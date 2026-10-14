<?php

/*
|--------------------------------------------------------------------------
| Dashboard navigation
|--------------------------------------------------------------------------
|
| Each entry carries the permission key required to see it. The sidebar in
| resources/views/components/dashboard/shell.blade.php filters on it, so a
| role never sees a page it cannot open.
|
*/

return [
    'nav' => [
        ['route' => 'dashbored.overview', 'label_key' => 'ui.nav.overview', 'icon' => 'grid', 'permission' => 'dashboard.view'],
        ['route' => 'dashbored.financial', 'label_key' => 'ui.nav.financial', 'icon' => 'currency', 'permission' => 'cp.view'],
        ['route' => 'dashbored.physical', 'label_key' => 'ui.nav.physical', 'icon' => 'chart', 'permission' => 'dashboard.view'],
        ['route' => 'dashbored.spu', 'label_key' => 'ui.nav.spu', 'icon' => 'signal', 'permission' => 'dashboard.view'],
        ['route' => 'dashbored.alerts', 'label_key' => 'ui.nav.alerts', 'icon' => 'bell', 'permission' => 'alerts.view'],
        ['route' => 'dashbored.projects', 'label_key' => 'ui.nav.projects', 'icon' => 'folder', 'permission' => 'projects.view'],
        ['route' => 'dashbored.reports', 'label_key' => 'ui.nav.reports', 'icon' => 'document', 'permission' => 'reports.view'],
        ['route' => 'dashbored.actions', 'label_key' => 'ui.nav.actions', 'icon' => 'bolt', 'permission' => ['reevaluation.request', 'movement.request', 'movement.approve', 'reevaluation.approve', 'projects.close']],
        ['route' => 'dashbored.calculations', 'label_key' => 'ui.nav.calculations', 'icon' => 'calculator', 'permission' => 'calculations.view'],
        ['route' => 'dashbored.holdings', 'label_key' => 'ui.nav.holdings', 'icon' => 'building', 'permission' => 'reference.manage'],
        ['route' => 'dashbored.procurement-steps', 'label_key' => 'ui.nav.procurement_steps', 'icon' => 'list', 'permission' => 'reference.manage'],
        ['route' => 'dashbored.design-offices', 'label_key' => 'ui.nav.design_offices', 'icon' => 'briefcase', 'permission' => 'reference.manage'],
        ['route' => 'dashbored.network-types', 'label_key' => 'ui.nav.network_types', 'icon' => 'signal', 'permission' => 'reference.manage'],
        ['route' => 'dashbored.profile', 'label_key' => 'ui.nav.profile', 'icon' => 'user', 'permission' => null],
    ],
];
