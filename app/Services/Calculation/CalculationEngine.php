<?php

namespace App\Services\Calculation;

use DivisionByZeroError;
use RuntimeException;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\Lexer;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\ExpressionLanguage\Token;
use Throwable;

/**
 * Evaluates the configurable metric expressions.
 *
 * Rules may reference other rules ("taux_financier" uses "ap_actuelle"); those
 * are resolved on demand and memoised in the {@see VariableBag}, with a
 * resolution stack guarding against a cycle an administrator might introduce.
 */
class CalculationEngine
{
    /** Maximum rule-to-rule nesting before we assume something is wrong. */
    private const MAX_DEPTH = 20;

    private ExpressionLanguage $language;

    /** @var list<string> */
    private array $stack = [];

    public function __construct(private readonly MetricRegistry $registry)
    {
        $this->language = new ExpressionLanguage;

        foreach ($this->functions() as $function) {
            $this->language->addFunction($function);
        }
    }

    /**
     * Resolve one metric. Returns null when the rule does not exist or the
     * expression cannot be evaluated.
     */
    public function evaluate(string $key, VariableBag $bag): ?float
    {
        if ($bag->hasResolved($key)) {
            return $bag->resolved($key);
        }

        $rule = $this->registry->rule($key);

        if ($rule === null) {
            return null;
        }

        if (in_array($key, $this->stack, true)) {
            throw new RuntimeException(
                'Dépendance circulaire dans les règles de calcul : '.implode(' -> ', [...$this->stack, $key])
            );
        }

        if (count($this->stack) >= self::MAX_DEPTH) {
            throw new RuntimeException('Les règles de calcul sont imbriquées trop profondément.');
        }

        $this->stack[] = $key;

        try {
            $value = $this->evaluateExpression($rule['expression'], $bag);
            $value = $this->applyBounds($value, $rule);
        } finally {
            array_pop($this->stack);
        }

        $bag->remember($key, $value);

        return $value;
    }

    /**
     * Resolve several metrics against one bag, reusing intermediate results.
     *
     * @param  list<string>  $keys
     * @return array<string, float|null>
     */
    public function evaluateMany(array $keys, VariableBag $bag): array
    {
        $out = [];
        foreach ($keys as $key) {
            $out[$key] = $this->evaluate($key, $bag);
        }

        return $out;
    }

    /** Resolve every active rule. @return array<string, float|null> */
    public function evaluateAll(VariableBag $bag): array
    {
        return $this->evaluateMany(array_keys($this->registry->rules()), $bag);
    }

    /**
     * Seed a bag with the current parameter values, then the caller's inputs.
     *
     * @param  array<string, mixed>  $inputs
     */
    public function bag(array $inputs = []): VariableBag
    {
        return VariableBag::make($this->registry->parameters())->merge($inputs);
    }

    /**
     * Evaluate a raw expression, resolving any rule references it contains.
     */
    private function evaluateExpression(string $expression, VariableBag $bag): ?float
    {
        foreach ($this->identifiers($expression) as $identifier) {
            if ($bag->has($identifier)) {
                continue;
            }

            // An unmet identifier that names another rule is resolved first.
            if ($this->registry->hasRule($identifier)) {
                $this->evaluate($identifier, $bag);

                continue;
            }

            // Otherwise treat it as an absent input.
            $bag->set($identifier, 0);
        }

        try {
            $result = $this->language->evaluate($expression, $bag->all());
        } catch (DivisionByZeroError) {
            // A zero denominator means "nothing to measure yet", not an error:
            // a project with no AP has a 0% consumption rate.
            return 0.0;
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Expression invalide « '.$expression.' » : '.$e->getMessage(),
                previous: $e,
            );
        }

        if ($result === null || is_bool($result)) {
            return $result === true ? 1.0 : ($result === false ? 0.0 : null);
        }

        if (! is_numeric($result)) {
            return null;
        }

        return (float) $result;
    }

    /** @param array<string, mixed> $rule */
    private function applyBounds(?float $value, array $rule): ?float
    {
        if ($value === null) {
            return null;
        }

        if (is_nan($value) || is_infinite($value)) {
            return null;
        }

        if (($rule['clamp_min'] ?? null) !== null) {
            $value = max((float) $rule['clamp_min'], $value);
        }

        if (($rule['clamp_max'] ?? null) !== null) {
            $value = min((float) $rule['clamp_max'], $value);
        }

        return round($value, (int) ($rule['precision'] ?? 2));
    }

    /**
     * Identifiers an expression reads, excluding function names and literals.
     *
     * @return list<string>
     */
    public function identifiers(string $expression): array
    {
        $functionNames = array_map(fn (ExpressionFunction $f) => $f->getName(), $this->functions());
        $reserved = ['true', 'false', 'null', 'and', 'or', 'not', 'in', 'matches', 'constant'];

        try {
            $stream = (new Lexer)->tokenize($expression);
        } catch (SyntaxError) {
            return [];
        }

        $names = [];
        while (! $stream->isEOF()) {
            $token = $stream->current;

            if ($token->test(Token::NAME_TYPE)) {
                $name = (string) $token->value;

                // A name followed by "(" is a function call, not a variable.
                $stream->next();
                $isCall = $stream->current->test(Token::PUNCTUATION_TYPE, '(');

                if (! $isCall && ! in_array($name, $functionNames, true) && ! in_array($name, $reserved, true)) {
                    $names[$name] = true;
                }

                continue;
            }

            $stream->next();
        }

        return array_keys($names);
    }

    /**
     * Check an expression an administrator has typed, without evaluating it
     * against real data.
     *
     * @return list<string> human-readable problems; empty means valid
     */
    public function validate(string $expression, ?string $forKey = null): array
    {
        $errors = [];

        if (trim($expression) === '') {
            return ['L\'expression ne peut pas être vide.'];
        }

        $identifiers = [];

        try {
            $this->language->parse($expression, $this->registry->knownIdentifiers());
            $identifiers = $this->identifiers($expression);
        } catch (SyntaxError $e) {
            return ['Erreur de syntaxe : '.$e->getMessage()];
        } catch (Throwable $e) {
            return ['Expression invalide : '.$e->getMessage()];
        }

        $known = $this->registry->knownIdentifiers();
        foreach ($identifiers as $identifier) {
            if (! in_array($identifier, $known, true)) {
                $errors[] = 'Variable inconnue : « '.$identifier.' ».';
            }
        }

        if ($forKey !== null) {
            if (in_array($forKey, $identifiers, true)) {
                $errors[] = 'Une règle ne peut pas se référencer elle-même.';
            } elseif ($this->reachesRule($identifiers, $forKey, [])) {
                $errors[] = 'Cette expression crée une dépendance circulaire.';
            }
        }

        // A dry run on neutral inputs catches anything the parser accepts but
        // PHP rejects at runtime.
        try {
            $bag = $this->bag();
            foreach ($identifiers as $identifier) {
                if (! $bag->has($identifier)) {
                    $bag->set($identifier, 1);
                }
            }
            $this->language->evaluate($expression, $bag->all());
        } catch (DivisionByZeroError) {
            // Tolerated at runtime.
        } catch (Throwable $e) {
            $errors[] = 'Évaluation impossible : '.$e->getMessage();
        }

        return array_values(array_unique($errors));
    }

    /**
     * Whether any of $identifiers depends, transitively, on $target.
     *
     * @param  list<string>  $identifiers
     * @param  list<string>  $seen
     */
    private function reachesRule(array $identifiers, string $target, array $seen): bool
    {
        foreach ($identifiers as $identifier) {
            if ($identifier === $target) {
                return true;
            }

            if (in_array($identifier, $seen, true)) {
                continue;
            }

            $rule = $this->registry->rule($identifier);

            if ($rule === null) {
                continue;
            }

            if ($this->reachesRule($this->identifiers($rule['expression']), $target, [...$seen, $identifier])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Expression Language ships with no maths functions, so the vocabulary an
     * administrator can use is defined here.
     *
     * @return list<ExpressionFunction>
     */
    private function functions(): array
    {
        return [
            ExpressionFunction::fromPhp('max'),
            ExpressionFunction::fromPhp('min'),
            ExpressionFunction::fromPhp('abs'),
            ExpressionFunction::fromPhp('round'),
            ExpressionFunction::fromPhp('floor'),
            ExpressionFunction::fromPhp('ceil'),
            ExpressionFunction::fromPhp('sqrt'),
            ExpressionFunction::fromPhp('pow'),
            ExpressionFunction::fromPhp('intdiv'),

            // Percentage with a safe denominator: pct(paid, total) -> 0 when total is 0.
            new ExpressionFunction(
                'pct',
                fn ($part, $whole) => sprintf('(((float) %s) == 0.0 ? 0.0 : ((float) %s) / ((float) %s) * 100)', $whole, $part, $whole),
                fn (array $ctx, $part, $whole) => (float) $whole === 0.0 ? 0.0 : (float) $part / (float) $whole * 100,
            ),

            // Division with an explicit fallback: div(a, b, 0).
            new ExpressionFunction(
                'div',
                fn ($a, $b, $fallback = '0') => sprintf('(((float) %s) == 0.0 ? %s : ((float) %s) / ((float) %s))', $b, $fallback, $a, $b),
                fn (array $ctx, $a, $b, $fallback = 0) => (float) $b === 0.0 ? (float) $fallback : (float) $a / (float) $b,
            ),

            // Keep a value inside a band: clamp(value, 0, 100).
            new ExpressionFunction(
                'clamp',
                fn ($value, $lo, $hi) => sprintf('min(max((float) %s, (float) %s), (float) %s)', $value, $lo, $hi),
                fn (array $ctx, $value, $lo, $hi) => min(max((float) $value, (float) $lo), (float) $hi),
            ),

            // Whole days between two dates, either as Y-m-d strings or timestamps.
            new ExpressionFunction(
                'jours',
                fn ($from, $to) => sprintf('\App\Services\Calculation\CalculationEngine::daysBetween(%s, %s)', $from, $to),
                fn (array $ctx, $from, $to) => self::daysBetween($from, $to),
            ),
        ];
    }

    /** Helper backing the `jours()` expression function. */
    public static function daysBetween(mixed $from, mixed $to): float
    {
        if (blank($from) || blank($to)) {
            return 0.0;
        }

        $a = is_numeric($from) ? (int) $from : strtotime((string) $from);
        $b = is_numeric($to) ? (int) $to : strtotime((string) $to);

        if ($a === false || $b === false) {
            return 0.0;
        }

        return floor(($b - $a) / 86400);
    }
}
