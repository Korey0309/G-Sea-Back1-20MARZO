<?php

use App\Http\Controllers\Api\AgenteController;
use App\Http\Controllers\Api\AgentePromotoriaController;
use App\Http\Controllers\Api\AseguradoraController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContratanteController;
use App\Http\Controllers\Api\InvitationController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\PolizaController;
use App\Http\Controllers\Api\PolizaDocumentoController;
use App\Http\Controllers\Api\PolizaVehiculoController;
use App\Http\Controllers\Api\RamoController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SubramoController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WorkspaceController;
use App\Http\Controllers\Api\WorkspaceUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('users', UserController::class);
    Route::apiResource('workspaces', WorkspaceController::class);
    Route::apiResource('invitations', InvitationController::class);
    Route::apiResource('workspace_users', WorkspaceUserController::class)
        ->parameters(['workspace_users' => 'workspace_user']);

    Route::apiResource('aseguradoras', AseguradoraController::class);
    Route::apiResource('planes', PlanController::class);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('ramos', RamoController::class);
    Route::apiResource('subramos', SubramoController::class);

    Route::apiResource('contratantes', ContratanteController::class);

    Route::apiResource('agentes', AgenteController::class);
    Route::apiResource('agentes_promotoria', AgentePromotoriaController::class)
        ->parameters(['agentes_promotoria' => 'agente_promotoria']);

    Route::apiResource('polizas', PolizaController::class);
    Route::apiResource('poliza_vehiculos', PolizaVehiculoController::class)
        ->parameters(['poliza_vehiculos' => 'poliza_vehiculo']);
    Route::apiResource('poliza_documentos', PolizaDocumentoController::class);
});
