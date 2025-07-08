<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolController;
use App\Http\Controllers\CarreraController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\AsistenciaFotoController;
use App\Http\Controllers\IncidenciaController;
use App\Http\Controllers\IncidenciaDocumentoController;
use App\Http\Controllers\ReporteGeneradoController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// Rutas de autenticación
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('user', [AuthController::class, 'user']);

//Rutas de recursos
// Protege todas las rutas relacionadas con incidencias
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('incidencias', IncidenciaController::class);
    Route::post('incidencias/{incidencia}/documento', [IncidenciaDocumentoController::class, 'store']);
    Route::post('incidencias/{incidencia}/estatus', [IncidenciaController::class, 'cambiarEstatus']);
    Route::apiResource('asistencias', AsistenciaController::class);
    Route::post('asistencias/{asistencia}/foto', [AsistenciaFotoController::class, 'store']);
    Route::apiResource('reportes-generados', ReporteGeneradoController::class)->except(['update']);
});

Route::apiResource('roles', RolController::class);
Route::apiResource('carreras', CarreraController::class);
Route::apiResource('usuarios', UsuarioController::class);
