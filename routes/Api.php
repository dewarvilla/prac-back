<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\CatalogoController;
use App\Http\Controllers\Api\V1\ProgramacionController;
use App\Http\Controllers\Api\V1\CreacionController;
use App\Http\Controllers\Api\V1\FechaController;
use App\Http\Controllers\Api\V1\SalarioController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\ApprovalInboxController;
use App\Http\Controllers\Api\V1\ApprovalRequestController;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    Route::apiResource('catalogos', CatalogoController::class)->parameters(['catalogos' => 'catalogo']);
    Route::post('catalogos/bulk', [CatalogoController::class, 'storeBulk']);
    Route::post('catalogos/bulk-delete', [CatalogoController::class, 'destroyBulk']);

    Route::apiResource('programaciones', ProgramacionController::class)->parameters(['programaciones' => 'programacion']);
    Route::post('programaciones/bulk-delete', [ProgramacionController::class, 'destroyBulk']);

    Route::apiResource('creaciones', CreacionController::class)->parameters(['creaciones' => 'creacion']);
    Route::post('creaciones/bulk-delete', [CreacionController::class, 'destroyBulk']);

    Route::apiResource('fechas', FechaController::class)->parameters(['fechas' => 'fecha']);
    Route::post('fechas/bulk-delete', [FechaController::class, 'destroyBulk']);

    Route::apiResource('salarios', SalarioController::class)->parameters(['salarios' => 'salario']);
    Route::post('salarios/bulk-delete', [SalarioController::class, 'destroyBulk']);

    // ===== aprobaciones =====
    Route::get('approvals/inbox', [ApprovalInboxController::class, 'index']);
    Route::get('approval-requests/{approvalRequest}', [ApprovalRequestController::class, 'show']);
    Route::post('approval-requests/{approvalRequest}/approve', [ApprovalRequestController::class, 'approve']);
    Route::post('approval-requests/{approvalRequest}/reject',  [ApprovalRequestController::class, 'reject']);
    Route::post('approval-requests/{approvalRequest}/cancel',  [ApprovalRequestController::class, 'cancel']);

    // ===== Notificaciones =====
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);
});
