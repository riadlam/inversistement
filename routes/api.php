<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommuneController;
use App\Http\Controllers\Api\CpPaymentController;
use App\Http\Controllers\Api\EtablissementController;
use App\Http\Controllers\Api\MaturationChecklistController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\WilayaController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register/check', [AuthController::class, 'check']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    // Reference data: readable by anyone who may see projects at all.
    Route::middleware('permission:projects.view')->group(function () {
        Route::get('/wilayas', [WilayaController::class, 'index']);
        Route::get('/communes', [CommuneController::class, 'index']);
        Route::get('/etablissements', [EtablissementController::class, 'index']);
        Route::get('/maturation-checklist', [MaturationChecklistController::class, 'index']);
    });

    Route::post('/projects', [ProjectController::class, 'store'])
        ->middleware('permission:projects.create');

    Route::match(['put', 'patch'], '/projects/{fipId}', [ProjectController::class, 'update'])
        ->middleware('permission:projects.update');

    Route::delete('/projects/{fipId}', [ProjectController::class, 'destroy'])
        ->middleware('permission:projects.delete');

    Route::delete('/risk-files/{id}', [ProjectController::class, 'destroyRiskFile'])
        ->middleware('permission:projects.update');

    Route::post('/projects/{projectId}/cp-payments', [CpPaymentController::class, 'store'])
        ->middleware('permission:cp.request');
});
