<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetallePago;
use App\Models\Lectura;
use App\Models\Pago;
use App\Models\Vivienda;
use App\Models\Periodo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\BillingService;
use App\Http\Requests\CalcularDeudaRequest;
use App\Http\Requests\StorePagoRequest;

class PagoController extends Controller
{
    protected $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    /**
     * Calcular la deuda pendiente de una vivienda en un período dado.
     */
    public function calcularDeuda(CalcularDeudaRequest $request)
    {
        $vivienda = Vivienda::with(['tarifa.rangos'])->findOrFail($request->vivienda_id);
        $periodo = Periodo::findOrFail($request->periodo_id);
        $lectura = Lectura::where('vivienda_id', $request->vivienda_id)
            ->where('periodo_id', $request->periodo_id)
            ->first();

        if (!$lectura) {
            return response()->json(['error' => 'No hay lectura para este período'], 404);
        }

        $resultado = $this->billingService->calculateDebt($vivienda, $periodo, $lectura);

        return response()->json($resultado);
    }

    /**
     * Registrar un pago.
     */
    public function store(StorePagoRequest $request)
    {
        $fields = $request->validated();

        DB::beginTransaction();
        try {
            $pago = Pago::create([
                'vivienda_id'       => $fields['vivienda_id'],
                'periodo_id'        => $fields['periodo_id'],
                'payment_method_id' => $fields['payment_method_id'] ?? 1, 
                'monto_total'       => $fields['monto_total'],
                'fecha_pago'        => now(),
                'referencia'        => $fields['referencia'] ?? null,
                'lectura_anterior'  => $fields['lectura_anterior'] ?? null,
                'lectura_actual'    => $fields['lectura_actual'] ?? null,
                'consumo'           => $fields['consumo'] ?? null,
                'desgloce_rangos'   => $fields['desgloce_rangos'] ?? null,
            ]);

            // Crear detalles del pago
            if (!empty($fields['monto_fijo']) && $fields['monto_fijo'] > 0) {
                DetallePago::create([
                    'pago_id'     => $pago->id,
                    'tipo'        => 'cargo_fijo',
                    'monto'       => $fields['monto_fijo'],
                    'descripcion' => 'Cargo fijo mensual',
                ]);
            }
            if (!empty($fields['costo_consumo']) && $fields['costo_consumo'] > 0) {
                DetallePago::create([
                    'pago_id'     => $pago->id,
                    'tipo'        => 'consumo',
                    'monto'       => $fields['costo_consumo'],
                    'descripcion' => 'Consumo de agua',
                ]);
            }
            if (!empty($fields['multa']) && $fields['multa'] > 0) {
                DetallePago::create([
                    'pago_id'     => $pago->id,
                    'tipo'        => 'multa',
                    'monto'       => $fields['multa'],
                    'descripcion' => 'Multa por mora',
                ]);
            }
            if (!empty($fields['monto_alcantarillado']) && $fields['monto_alcantarillado'] > 0) {
                DetallePago::create([
                    'pago_id'     => $pago->id,
                    'tipo'        => 'alcantarillado',
                    'monto'       => $fields['monto_alcantarillado'],
                    'descripcion' => 'Servicio de alcantarillado',
                ]);
            }

            // Otros detalles opcionales (Aportes, Multas adicionales, etc)
            if (!empty($fields['otros_detalles'])) {
                foreach ($fields['otros_detalles'] as $detalle) {
                    if ($detalle['monto'] > 0) {
                        DetallePago::create([
                            'pago_id'     => $pago->id,
                            'tipo'        => $detalle['tipo'],
                            'monto'       => $detalle['monto'],
                            'descripcion' => $detalle['descripcion'],
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json($pago->load(['vivienda.socio', 'vivienda.zona', 'periodo', 'detalles']), 201);
        } catch (\Exception $e) {
            DB::rollback();
            // Evitar exponer el error exacto en producción
            \Illuminate\Support\Facades\Log::error('Error al registrar pago: ' . $e->getMessage());
            return response()->json(['error' => 'Ocurrió un error al procesar el pago.'], 500);
        }
    }

    /**
     * Listar pagos (con filtros opcionales).
     */
    public function index(Request $request)
    {
        $query = Pago::with(['vivienda.socio', 'vivienda.zona', 'detalles', 'paymentMethod']);

        if ($request->filled('vivienda_id')) {
            $query->where('vivienda_id', $request->vivienda_id);
        }
        if ($request->filled('periodo_id')) {
            $query->where('periodo_id', $request->periodo_id);
        }
        if ($request->filled('zona_id')) {
            $query->whereHas('vivienda', function ($q) use ($request) {
                $q->where('zone_id', $request->zona_id);
            });
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_pago', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_pago', '<=', $request->fecha_hasta);
        }

        $perPage = $request->integer('per_page', 50);
        $perPage = min($perPage, 200);
        $pagos = $query->orderBy('fecha_pago', 'desc')->paginate($perPage);

        return response()->json($pagos);
    }

    /**
     * Ver un pago en detalle.
     */
    public function show($id)
    {
        return response()->json(
            Pago::with(['vivienda.socio', 'vivienda.zona', 'detalles', 'paymentMethod'])->findOrFail($id)
        );
    }

    /**
     * Retorna los IDs de viviendas que ya tienen pago en un período.
     */
    public function pagadosEnPeriodo(Request $request)
    {
        $request->validate(['periodo_id' => 'required|exists:periodos,id']);

        $ids = Pago::where('periodo_id', $request->periodo_id)
            ->pluck('vivienda_id')
            ->unique()
            ->values();

        return response()->json($ids);
    }

    /**
     * Eliminar un pago (solo en caso de error).
     */
    public function destroy($id)
    {
        $pago = Pago::with('periodo')->findOrFail($id);

        if ($pago->periodo && $pago->periodo->estado === 'cerrado') {
            return response()->json([
                'error' => 'No se puede eliminar un pago de un período cerrado.'
            ], 422);
        }

        $pago->detalles()->delete();
        $pago->delete();

        return response()->json(['message' => 'Pago eliminado']);
    }

    /**
     * Resumen para el dashboard: estadísticas del mes activo o período dado.
     */
    public function resumen(Request $request)
    {
        $query = Pago::query();

        if ($request->filled('periodo_id')) {
            $request->validate(['periodo_id' => 'exists:periodos,id']);
            $query->where('periodo_id', $request->periodo_id);
        } else {
            $query->whereMonth('fecha_pago', now()->month)
                  ->whereYear('fecha_pago', now()->year);
        }

        if ($request->filled('zona_id')) {
            $request->validate(['zona_id' => 'exists:zones,id']);
            $query->whereHas('vivienda', function ($q) use ($request) {
                $q->where('zone_id', $request->zona_id);
            });
        }

        $totalPagos    = $query->count();
        $montoDelMes   = $query->sum('monto_total');
        $totalViviendas = Vivienda::when($request->filled('zona_id'), function ($q) use ($request) {
            return $q->where('zone_id', $request->zona_id);
        })->count();

        $pagadas = Pago::when($request->filled('periodo_id'), function ($q) use ($request) {
                return $q->where('periodo_id', $request->periodo_id);
            }, function ($q) {
                return $q->whereMonth('fecha_pago', now()->month)
                         ->whereYear('fecha_pago', now()->year);
            })
            ->when($request->filled('zona_id'), function ($q) use ($request) {
                return $q->whereHas('vivienda', function ($sub) use ($request) {
                    $sub->where('zone_id', $request->zona_id);
                });
            })
            ->distinct('vivienda_id')
            ->count('vivienda_id');

        return response()->json([
            'total_pagos'       => $totalPagos,
            'total_monto'       => round($montoDelMes, 2),
            'total_viviendas'   => $totalViviendas,
            'viviendas_pagadas' => $pagadas,
            'viviendas_pendientes' => $totalViviendas - $pagadas,
        ]);
    }
}
