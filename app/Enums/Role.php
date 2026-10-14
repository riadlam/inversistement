<?php

namespace App\Enums;

/**
 * TDR RACI profiles (Terme Technique de Référence §4) plus the sectoral
 * read profiles already present in the legacy `dass` table.
 *
 * Values match the strings already stored in `users.role`, so existing
 * accounts keep working without a data migration.
 */
enum Role: string
{
    /** Support: accounts and technical maintenance. */
    case Admin = 'admin';

    /** Responsable de l'action — Gestionnaire de projet (DASS et établissements). */
    case Dass = 'dass';

    /** Responsable de programme (centrale ministère) — decisional. */
    case ProgrammeManager = 'programme_manager';

    /** Contrôleur budgétaire (RFF et DEPSI) — compliance. */
    case BudgetController = 'budget_controller';

    /** Ordonnateur — commitment and ordering authority (TDR §3). */
    case Ordonnateur = 'ordonnateur';

    case Police = 'police';
    case Gendarmerie = 'gendarmerie';
    case Sante = 'sante';
    case Ads = 'ads';

    public function label(): string
    {
        return __('ui.roles.'.$this->value);
    }

    /** TDR "Fonction" column. */
    public function function(): string
    {
        return __('ui.role_functions.'.$this->value);
    }

    /**
     * Whether the role sees the whole country. Wilaya-scoped roles only ever
     * read their own wilaya, per the TDR access rules.
     */
    public function isNational(): bool
    {
        return in_array($this, [
            self::Admin,
            self::ProgrammeManager,
            self::BudgetController,
            self::Ordonnateur,
        ], true);
    }

    /** Read-only sectoral observers. */
    public function isObserver(): bool
    {
        return in_array($this, [self::Police, self::Gendarmerie, self::Sante, self::Ads], true);
    }

    /** @return array<string, string> value => label, for select inputs. */
    public static function options(): array
    {
        $out = [];
        foreach (self::cases() as $case) {
            $out[$case->value] = $case->label();
        }

        return $out;
    }
}
