<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TarifaController extends Controller
{
    public function index()
    {
        return response()->json(\App\Models\Tarifa::with('rangos')->orderBy('id')->get());
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'nombre'     => 'required|string|unique:tarifas,nombre',
            'monto_fijo' => 'required|numeric|min:0',
            'tipo'       => 'sometimes|string|in:mensual,anual',
        ]);

        $fields['tipo'] = $fields['tipo'] ?? 'mensual';
        $tarifa = \App\Models\Tarifa::create($fields);

        return response()->json($tarifa->load('rangos'), 201);
    }

    public function update(Request $request, $id)
    {
        $tarifa = \App\Models\Tarifa::findOrFail($id);

        $fields = $request->validate([
            'nombre'     => 'sometimes|string|unique:tarifas,nombre,' . $id,
            'monto_fijo' => 'sometimes|numeric|min:0',
            'tipo'       => 'sometimes|string|in:mensual,anual',
        ]);

        $tarifa->update($fields);

        return response()->json($tarifa->load('rangos'));
    }

    public function destroy($id)
    {
        $tarifa = \App\Models\Tarifa::findOrFail($id);

        if ($tarifa->viviendas()->exists()) {
            return response()->json(['error' => 'No se puede borrar una tarifa en uso por viviendas.'], 422);
        }

        $tarifa->rangos()->delete();
        $tarifa->delete();

        return response()->json(null, 204);
    }
}
