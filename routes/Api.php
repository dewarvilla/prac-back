<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\CatalogoController;
use App\Http\Controllers\Api\V1\ProgramacionController;
use App\Http\Controllers\Api\V1\CreacionController;
use App\Http\Controllers\Api\V1\FechaController;
use App\Http\Controllers\Api\V1\SalarioController;
use App\Http\Controllers\Api\V1\CreacionApprovalController;
use App\Http\Controllers\Api\V1\ProgramacionApprovalController;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    Route::apiResource('catalogos', CatalogoController::class)->parameters(['catalogos' => 'catalogo']);
    Route::post('catalogos/bulk', [CatalogoController::class, 'storeBulk'])->name('catalogos.bulk.store');
    Route::post('catalogos/bulk-delete', [CatalogoController::class, 'destroyBulk'])->name('catalogos.bulk.delete');

    Route::apiResource('programaciones', ProgramacionController::class)->parameters(['programaciones' => 'programacion']);
    Route::post('programaciones/bulk-delete', [ProgramacionController::class, 'destroyBulk'])->name('programaciones.bulk.delete');

    Route::apiResource('creaciones', CreacionController::class)->parameters(['creaciones' => 'creacion']);
    Route::post('creaciones/bulk-delete', [CreacionController::class, 'destroyBulk'])->name('creaciones.bulk.delete');

    Route::apiResource('fechas', FechaController::class)->parameters(['fechas' => 'fecha']);
    Route::post('fechas/bulk-delete', [FechaController::class, 'destroyBulk'])->name('fechas.bulk.delete');

    Route::apiResource('salarios', SalarioController::class)->parameters(['salarios' => 'salario']);
    Route::post('salarios/bulk-delete', [SalarioController::class, 'destroyBulk'])->name('salarios.bulk.delete');

    Route::post('creaciones/{creacion}/approve', [CreacionApprovalController::class, 'approve']);
    Route::post('creaciones/{creacion}/reject',  [CreacionApprovalController::class, 'reject']);

    Route::post('programaciones/{programacion}/approve', [ProgramacionApprovalController::class, 'approve']);
    Route::post('programaciones/{programacion}/reject',  [ProgramacionApprovalController::class, 'reject']);
});