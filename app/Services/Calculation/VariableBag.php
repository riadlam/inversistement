<?php

namespace App\Services\Calculation;

/**
 * The raw inputs for one evaluation, plus the memoised results of any rules
 * resolved along the way.
 *
 * Missing inputs read as 0 rather than throwing: a project half-way through
 * data entry should still produce a score instead of an error page.
 */
class VariableBag
{
    /** @var array<string, mixed> */
    private array $values;

    /** @var array<string, float|null> */
    private array $resolved = [];

    /** @param array<string, mixed> $values */
    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    /** @param array<string, mixed> $values */
    public static function make(array $values = []): self
    {
        return new self($values);
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->values);
    }

    public function get(string $key): mixed
    {
        return $this->values[$key] ?? 0;
    }

    public function set(string $key, mixed $value): self
    {
        $this->values[$key] = $value;

        return $this;
    }

    /** @param array<string, mixed> $values */
    public function merge(array $values): self
    {
        $this->values = array_merge($this->values, $values);

        return $this;
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return $this->values;
    }

    public function hasResolved(string $key): bool
    {
        return array_key_exists($key, $this->resolved);
    }

    public function resolved(string $key): ?float
    {
        return $this->resolved[$key] ?? null;
    }

    public function remember(string $key, ?float $value): void
    {
        $this->resolved[$key] = $value;
        $this->values[$key] = $value ?? 0;
    }

    /** @return array<string, float|null> */
    public function allResolved(): array
    {
        return $this->resolved;
    }

    /**
     * Drop a memoised result so it is recomputed. Used by the admin preview,
     * which evaluates a candidate expression against an already-populated bag.
     */
    public function forget(string $key): self
    {
        unset($this->resolved[$key], $this->values[$key]);

        return $this;
    }
}
