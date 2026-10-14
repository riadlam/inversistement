<?php

namespace App\Enums;

/**
 * Canonical permission catalog. The authoritative role/permission matrix lives
 * in the `role_permissions` table so an administrator can change it, but this
 * enum keeps the key list typo-proof for gates, policies and seeders.
 */
enum Permission: string
{
    // Projects
    case ProjectsView = 'projects.view';
    case ProjectsCreate = 'projects.create';
    case ProjectsUpdate = 'projects.update';
    case ProjectsDelete = 'projects.delete';
    case ProjectsSubmit = 'projects.submit';
    case ProjectsArbitrate = 'projects.arbitrate';
    case ProjectsVisa = 'projects.visa';
    case ProjectsClose = 'projects.close';

    // Lots and field situations
    case LotsView = 'lots.view';
    case LotsManage = 'lots.manage';
    case SituationsCreate = 'situations.create';
    case SituationsValidate = 'situations.validate';

    // Money
    case AeView = 'ae.view';
    case AeEngage = 'ae.engage';
    case CpView = 'cp.view';
    case CpRequest = 'cp.request';
    case CpValidate = 'cp.validate';
    case ReevaluationRequest = 'reevaluation.request';
    case ReevaluationApprove = 'reevaluation.approve';
    case MovementRequest = 'movement.request';
    case MovementApprove = 'movement.approve';

    // Dashboards, alerts, reporting
    case DashboardView = 'dashboard.view';
    case AlertsView = 'alerts.view';
    case AlertsResolve = 'alerts.resolve';
    case ReportsView = 'reports.view';
    case ReportsExport = 'reports.export';

    // Administration
    case UsersManage = 'users.manage';
    case ReferenceManage = 'reference.manage';
    case CalculationsView = 'calculations.view';
    case CalculationsManage = 'calculations.manage';

    // Scope
    case ScopeNational = 'scope.national';

    public function label(): string
    {
        return __('ui.permissions.'.$this->value);
    }

    public function category(): string
    {
        return match ($this) {
            self::ProjectsView, self::ProjectsCreate, self::ProjectsUpdate, self::ProjectsDelete,
            self::ProjectsSubmit, self::ProjectsArbitrate, self::ProjectsVisa, self::ProjectsClose => __('ui.permission_categories.projects'),

            self::LotsView, self::LotsManage, self::SituationsCreate, self::SituationsValidate => __('ui.permission_categories.lots'),

            self::AeView, self::AeEngage, self::CpView, self::CpRequest, self::CpValidate,
            self::ReevaluationRequest, self::ReevaluationApprove,
            self::MovementRequest, self::MovementApprove => __('ui.permission_categories.budget'),

            self::DashboardView, self::AlertsView, self::AlertsResolve,
            self::ReportsView, self::ReportsExport => __('ui.permission_categories.pilotage'),

            self::UsersManage, self::ReferenceManage,
            self::CalculationsView, self::CalculationsManage, self::ScopeNational => __('ui.permission_categories.admin'),
        };
    }

    /**
     * The TDR RACI matrix, as the seeded default.
     *
     * @return array<string, list<string>> role value => permission keys
     */
    public static function matrix(): array
    {
        $all = array_map(fn (self $p) => $p->value, self::cases());

        $observer = [
            self::ProjectsView, self::LotsView, self::AeView, self::CpView,
            self::DashboardView, self::AlertsView, self::ReportsView,
        ];

        return [
            // Support: everything.
            Role::Admin->value => $all,

            // Opérationnel: saisie des données de terrain et mise à jour des fiches.
            Role::Dass->value => self::keys([
                self::ProjectsView, self::ProjectsCreate, self::ProjectsUpdate, self::ProjectsDelete,
                self::ProjectsSubmit,
                self::LotsView, self::LotsManage, self::SituationsCreate,
                self::AeView, self::CpView, self::CpRequest,
                self::ReevaluationRequest, self::MovementRequest,
                self::DashboardView, self::AlertsView,
                self::ReportsView, self::ReportsExport,
            ]),

            // Décisionnel: arbitrage, validation des réévaluations et clôtures.
            Role::ProgrammeManager->value => self::keys([
                self::ProjectsView, self::ProjectsArbitrate, self::ProjectsClose,
                self::LotsView, self::SituationsValidate,
                self::AeView, self::CpView,
                self::ReevaluationApprove, self::MovementApprove,
                self::DashboardView, self::AlertsView, self::AlertsResolve,
                self::ReportsView, self::ReportsExport,
                self::CalculationsView, self::ScopeNational,
            ]),

            // Conformité: vérification des seuils AE/CP et visa des engagements.
            Role::BudgetController->value => self::keys([
                self::ProjectsView, self::ProjectsVisa,
                self::LotsView,
                self::AeView, self::CpView, self::CpValidate,
                self::DashboardView, self::AlertsView,
                self::ReportsView, self::ReportsExport,
                self::CalculationsView, self::ScopeNational,
            ]),

            // Engagement juridique de la dépense.
            Role::Ordonnateur->value => self::keys([
                self::ProjectsView,
                self::LotsView,
                self::AeView, self::AeEngage, self::CpView, self::CpValidate,
                self::DashboardView, self::AlertsView,
                self::ReportsView, self::ReportsExport,
                self::ScopeNational,
            ]),

            Role::Police->value => self::keys($observer),
            Role::Gendarmerie->value => self::keys($observer),
            Role::Sante->value => self::keys($observer),
            Role::Ads->value => self::keys($observer),
        ];
    }

    /**
     * @param  list<self>  $permissions
     * @return list<string>
     */
    private static function keys(array $permissions): array
    {
        return array_map(fn (self $p) => $p->value, $permissions);
    }
}
