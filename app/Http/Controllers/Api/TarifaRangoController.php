<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TarifaRango;
use Illuminate\Http\Request;

class TarifaRangoController extends Controller
{
    public function index(Request $request)
    {
        $query = TarifaRango::with('tarifa');

        if ($request->filled('tarifa_id')) {
            $query->where('tarifa_id', $request->tarifa_id);
        }

        return response()->json($query->orderBy('desde')->get());
    }

    public function show($id)
    {
        return response()->json(TarifaRango::with('tarifa')->findOrFail($id));
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'tarifa_id'    => 'required|exists:tarifas,id',
            'desde'        => 'required|numeric|min:0',
            'hasta'        => 'nullable|numeric|gt:desde',
            'precio_metro' => 'required|numeric|min:0',
        ]);

        $rango = TarifaRango::create($fields);

        return response()->json($rango->load('tarifa'), 201);
    }

    public function update(Request $request, $id)
    {
        $rango = TarifaRango::findOrFail($id);

        $fields = $request->validate([
            'tarifa_id'    => 'sometimes|exists:tarifas,id',
            'desde'        => 'sometimes|numeric|min:0',
            'hasta'        => 'nullable|numeric|gt:desde',
            'precio_metro' => 'sometimes|numeric|min:0',
        ]);

        $rango->update($fields);

        return response()->json($rango->load('tarifa'));
    }

    public function destroy($id)
    {
        $rango = TarifaRango::findOrFail($id);
        $rango->delete();

        return response()->json(null, 204);
    }
}
