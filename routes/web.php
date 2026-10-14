<?php

use App\Livewire\Auth\Register;
use App\Livewire\Dashboard\AlertsCenter;
use App\Livewire\Dashboard\CalculationEngineAdmin;
use App\Livewire\Dashboard\CpForm;
use App\Livewire\Dashboard\CreateProject;
use App\Livewire\Dashboard\FinancialOverview;
use App\Livewire\Dashboard\Overview;
use App\Livewire\Dashboard\PhysicalProgress;
use App\Livewire\Dashboard\ProjectFiche;
use App\Livewire\Dashboard\ProjectList;
use App\Livewire\Dashboard\DesignOfficesAdmin;
use App\Livewire\Dashboard\LotMilestoneTypesAdmin;
use App\Livewire\Dashboard\NetworkTypesAdmin;
use App\Livewire\Dashboard\ProjectOwnersAdmin;
use App\Livewire\Dashboard\ProjectShow;
use App\Livewire\Dashboard\QuickActionsPage;
use App\Livewire\Dashboard\ReportsCenter;
use App\Livewire\Dashboard\SpuOverview;
use App\Livewire\Dashboard\UserProfile;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashbored.overview')
        : redirect()->route('register');
});

Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::get('/register', Register::class)->name('register');

Route::get('/login', function () {
    return redirect()->route('register');
})->name('login');

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/register');
})->name('logout')->middleware('web');

Route::prefix('dashbored')->name('dashbored.')->middleware('auth')->group(function () {
    Route::get('/', Overview::class)->name('overview');
    Route::get('/financial', FinancialOverview::class)->name('financial');
    Route::get('/physical', PhysicalProgress::class)->name('physical');
    Route::get('/spu', SpuOverview::class)->name('spu');
    Route::get('/alerts', AlertsCenter::class)->name('alerts');
    Route::get('/projects', ProjectList::class)->name('projects');
    Route::get('/reports', ReportsCenter::class)->name('reports');

    // Official documents (TDR §11)
    Route::get('/reports/etat-global.xlsx', [ExportController::class, 'etatGlobal'])
        ->middleware('permission:reports.export')
        ->name('reports.etat-global');
    Route::get('/projects/{projectId}/fiche-suivi.pdf', [ExportController::class, 'ficheSuivi'])
        ->middleware('permission:reports.export')
        ->name('projects.fiche-suivi');
    Route::get('/pitch.pdf', [ExportController::class, 'pitch'])
        ->name('pitch');

    Route::get('/actions', QuickActionsPage::class)->name('actions');
    Route::get('/projects/create', CreateProject::class)->name('projects.create');
    Route::get('/projects/{fipId}/edit', CreateProject::class)->name('projects.edit');
    Route::get('/projects/{projectId}/fiche', ProjectFiche::class)->name('projects.fiche');
    Route::get('/projects/{projectId}', ProjectShow::class)->name('projects.show');
    Route::get('/projects/{projectId}/cp', CpForm::class)->name('projects.cp');
    Route::get('/moteur-de-calcul', CalculationEngineAdmin::class)
        ->middleware('permission:calculations.view')
        ->name('calculations');
    Route::get('/company-holdings', ProjectOwnersAdmin::class)
        ->middleware('permission:reference.manage')
        ->name('holdings');
    Route::get('/procurement-steps', LotMilestoneTypesAdmin::class)
        ->middleware('permission:reference.manage')
        ->name('procurement-steps');
    Route::get('/design-offices', DesignOfficesAdmin::class)
        ->middleware('permission:reference.manage')
        ->name('design-offices');
    Route::get('/network-types', NetworkTypesAdmin::class)
        ->middleware('permission:reference.manage')
        ->name('network-types');
    Route::get('/profile', UserProfile::class)->name('profile');
});
