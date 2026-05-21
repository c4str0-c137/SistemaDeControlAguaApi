<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lectura;
use App\Models\Vivienda;
use Illuminate\Http\Request;

class LecturaController extends Controller
{
    public function index(Request $request)
    {
        $query = Lectura::with(['vivienda.socio', 'vivienda.zona', 'periodo']);

        if ($request->filled('periodo_id')) {
            $query->where('lecturas.periodo_id', $request->periodo_id);
        }

        if ($request->filled('vivienda_id')) {
            $query->where('lecturas.vivienda_id', $request->vivienda_id);
        }

        if ($request->filled('zona_id')) {
            $query->whereHas('vivienda', function ($q) use ($request) {
                $q->where('zone_id', $request->zona_id);
            });
        }

        $perPage = $request->integer('per_page', 50);
        $perPage = min($perPage, 1000);

        $lecturas = $query->join('periodos', 'lecturas.periodo_id', '=', 'periodos.id')
            ->orderBy('periodos.fecha_inicio', 'desc')
            ->select('lecturas.*')
            ->paginate($perPage);

        return response()->json($lecturas);
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'vivienda_id'     => 'required|exists:viviendas,id',
            'periodo_id'      => 'required|exists:periodos,id',
            'lectura_anterior'=> 'required|numeric|min:0',
            'lectura_actual'  => 'required|numeric|min:0',
            'observaciones'   => 'nullable|string',
        ]);

        // FIX BUG #2: Validar anomalías en lecturas
        $consumo = max(0, $fields['lectura_actual'] - $fields['lectura_anterior']);

        // Advertencia: consumo negativo (medidor reiniciado o error)
        $warning = null;
        if ($fields['lectura_actual'] < $fields['lectura_anterior']) {
            $warning = 'Lectura actual es menor que la anterior. Verifique el medidor.';
        }

        // Advertencia: salto masivo de consumo (>100m3 en un período)
        $saltoMaximo = 100;
        if ($consumo > $saltoMaximo) {
            $warning = ($warning ? $warning . ' ' : '')
                . "Consumo inusualmente alto ({$consumo}m3). Verifique que no haya error de lectura.";
        }

        // Advertencia: consumo cero (posible falta de lectura)
        if ($consumo === 0 && $fields['lectura_actual'] == $fields['lectura_anterior']) {
            $warning = ($warning ? $warning . ' ' : '')
                . 'Consumo es 0. Confirme que la vivienda no tuvo consumo este período.';
        }

        $periodo = \App\Models\Periodo::findOrFail($fields['periodo_id']);
        $existeLectura = Lectura::where('vivienda_id', $fields['vivienda_id'])
            ->where('periodo_id', $fields['periodo_id'])
            ->exists();

        // Se permite registrar lecturas incluso en periodos cerrados para facilitar carga histórica
        // $if ($periodo->estado === 'cerrado' && !$existeLectura) { ... }

        $fields['consumo'] = $consumo;

        $lectura = Lectura::updateOrCreate(
            ['vivienda_id' => $fields['vivienda_id'], 'periodo_id' => $fields['periodo_id']],
            $fields
        );

        $response = $lectura->load(['vivienda.socio', 'periodo']);
        if ($warning) {
            return response()->json([
                'lectura' => $response,
                'warning' => $warning,
            ], 201);
        }

        return response()->json($response, 201);
    }

    /**
     * Actualizar una lectura existente (corregir errores).
     */
    public function update(Request $request, $id)
    {
        $lectura = Lectura::findOrFail($id);

        $fields = $request->validate([
            'lectura_anterior'=> 'sometimes|numeric|min:0',
            'lectura_actual'  => 'sometimes|numeric|min:0',
            'observaciones'   => 'nullable|string',
        ]);

        // Recalcular consumo si cambió alguna lectura
        $lecturaAnterior = $fields['lectura_anterior'] ?? $lectura->lectura_anterior;
        $lecturaActual   = $fields['lectura_actual'] ?? $lectura->lectura_actual;
        $fields['consumo'] = max(0, (float)$lecturaActual - (float)$lecturaAnterior);

        $warning = null;
        if (isset($fields['lectura_actual']) && isset($fields['lectura_anterior'])) {
            if ($fields['lectura_actual'] < $fields['lectura_anterior']) {
                $warning = 'Lectura actual es menor que la anterior.';
            }
            if ($fields['consumo'] > 100) {
                $warning = ($warning ? $warning . ' ' : '') . 'Consumo inusualmente alto.';
            }
        }

        $lectura->update($fields);

        $response = $lectura->load(['vivienda.socio', 'periodo']);
        if ($warning) {
            return response()->json(['lectura' => $response, 'warning' => $warning]);
        }
        return response()->json($response);
    }

    public function show($id)
    {
        return response()->json(
            Lectura::with(['vivienda.socio', 'vivienda.zona', 'periodo'])->findOrFail($id)
        );
    }

    public function byVivienda($viviendaId)
    {
        $lecturas = Lectura::with(['periodo'])
            ->join('periodos', 'lecturas.periodo_id', '=', 'periodos.id')
            ->where('vivienda_id', $viviendaId)
            ->orderBy('periodos.fecha_inicio', 'desc')
            ->select('lecturas.*')
            ->get();
        return response()->json($lecturas);
    }
}
