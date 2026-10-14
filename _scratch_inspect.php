<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$biskra = Project::where('fip_id', 'FIP-2010-BISKRA-FP')->value('fip_id');
$own = fn (User $u) => Project::where('wilaya_id', $u->wilaya_id)->value('fip_id') ?? $biskra;

$roles = ['programme_manager', 'budget_controller', 'police'];

foreach ($roles as $role) {
    $user = User::where('role', $role)->first();

    if (! $user) {
        echo "--- {$role}: no account seeded ---\n";

        continue;
    }

    $project = $user->isNational() ? $biskra : $own($user);

    $pages = [
        '/dashbored',
        '/dashbored/financial',
        '/dashbored/physical',
        '/dashbored/spu',
        '/dashbored/alerts',
        '/dashbored/projects',
        '/dashbored/reports',
        '/dashbored/actions',
        '/dashbored/moteur-de-calcul',
        '/dashbored/profile',
        '/dashbored/projects/create',
        "/dashbored/projects/{$project}",
        "/dashbored/projects/{$project}/fiche",
        "/dashbored/projects/{$project}/fiche-suivi.pdf",
        '/dashbored/reports/etat-global.xlsx',
    ];

    echo "--- {$role} ({$user->username}, ".($user->isNational() ? 'national' : 'wilaya '.$user->wilaya_id).") ---\n";

    foreach ($pages as $page) {
        auth()->login($user);
        $request = Request::create($page, 'GET');
        $request->setLaravelSession(app('session.store'));

        try {
            $response = $kernel->handle($request);
            $status = $response->getStatusCode();
            $note = '';

            if ($status >= 500) {
                $log = file_get_contents(__DIR__.'/storage/logs/laravel.log');
                preg_match_all('/local\.ERROR: (.*?) \{"userId/s', $log, $m);
                $note = '  '.trim(mb_substr(end($m[1]) ?: '', 0, 160));
            }

            printf("  %-52s %d%s\n", $page, $status, $note);
        } catch (Throwable $e) {
            printf("  %-52s EX %s (%s:%d)\n", $page, mb_substr($e->getMessage(), 0, 120), basename($e->getFile()), $e->getLine());
        }
    }
}
