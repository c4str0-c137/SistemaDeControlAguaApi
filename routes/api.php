<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AjusteController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LecturaController;
use App\Http\Controllers\Api\MultaController;
use App\Http\Controllers\Api\AporteController;
use App\Http\Controllers\Api\PagoController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\PeriodoController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SocioController;
use App\Http\Controllers\Api\TarifaController;
use App\Http\Controllers\Api\TarifaRangoController;
use App\Http\Controllers\Api\ViviendaController;
use App\Http\Controllers\Api\ZoneController;

// ─── Auth (sin middleware) con rate limiting ─────────────────────────────────
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// ─── Rutas protegidas ─────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', fn(Request $r) => $r->user());

    // Catálogos de solo lectura (todos los roles autenticados)
    Route::get('/roles', [RoleController::class, 'index']);
    Route::get('/ajustes', [AjusteController::class, 'index']);

    // Rutas compartidas por Admin y Lector
    Route::middleware('role:Admin,Lector')->group(function () {
        Route::get('/viviendas', [ViviendaController::class, 'index']);
        Route::get('/viviendas/{vivienda}', [ViviendaController::class, 'show']);
        Route::get('/periodos/activo', [PeriodoController::class, 'activo']);
        Route::get('/periodos', [PeriodoController::class, 'index']);
        Route::get('/lecturas', [LecturaController::class, 'index']);
        Route::post('/lecturas', [LecturaController::class, 'store']);
        Route::get('/lecturas/vivienda/{vivienda}', [LecturaController::class, 'byVivienda']);
        Route::get('/pagos/resumen', [PagoController::class, 'resumen']);
    });

    // Solo Admin
    Route::middleware('role:Admin')->group(function () {
        // Socios / Usuarios
        Route::apiResource('socios', SocioController::class);

        // Viviendas (Escritura y borrado)
        Route::post('/viviendas', [ViviendaController::class, 'store']);
        Route::put('/viviendas/{vivienda}', [ViviendaController::class, 'update']);
        Route::delete('/viviendas/{vivienda}', [ViviendaController::class, 'destroy']);

        // Tarifas y Rangos
        Route::apiResource('tarifas', TarifaController::class);
        Route::apiResource('tarifa-rangos', TarifaRangoController::class);

        // Períodos (Escritura y borrado)
        Route::apiResource('periodos', PeriodoController::class)->except(['activo']);

        // Lecturas (Edición y borrado)
        Route::put('/lecturas/{lectura}', [LecturaController::class, 'update']);
        Route::delete('/lecturas/{lectura}', [LecturaController::class, 'destroy']);

        // Pagos (Admin completo)
        Route::post('/pagos/calcular', [PagoController::class, 'calcularDeuda']);
        Route::get('/pagos/pagados-en-periodo', [PagoController::class, 'pagadosEnPeriodo']);
        Route::apiResource('pagos', PagoController::class)->only(['index', 'store', 'show', 'destroy']);

        // Ajustes (Admin puede crear/actualizar/borrar)
        Route::post('/ajustes', [AjusteController::class, 'store']);
        Route::put('/ajustes/{clave}', [AjusteController::class, 'update']);
        Route::delete('/ajustes/{clave}', [AjusteController::class, 'destroy']);

        // Zonas CRUD completo
        Route::apiResource('zones', ZoneController::class);

        // Payment Methods CRUD completo
        Route::apiResource('payment-methods', PaymentMethodController::class);

        // Multas y Aportes
        Route::apiResource('multas', MultaController::class);
        Route::apiResource('aportes', AporteController::class);
    });
});
