<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aporte;
use Illuminate\Http\Request;

class AporteController extends Controller
{
    public function index(Request $request)
    {
        $query = Aporte::with('vivienda.socio');

        if ($request->filled('vivienda_id')) {
            $query->where('vivienda_id', $request->vivienda_id);
        }
        if ($request->filled('pagado')) {
            $query->where('pagado', $request->boolean('pagado'));
        }

        $perPage = $request->integer('per_page', 50);
        $perPage = min($perPage, 200);

        return response()->json($query->orderByDesc('created_at')->paginate($perPage));
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'vivienda_id' => 'required|exists:viviendas,id',
            'monto'       => 'required|numeric|min:0',
            'motivo'      => 'required|string',
            'pagado'      => 'sometimes|boolean',
        ]);

        $aporte = Aporte::create($fields);

        return response()->json($aporte->load('vivienda.socio'), 201);
    }

    public function show($id)
    {
        return response()->json(Aporte::with(['vivienda.socio', 'vivienda.zona'])->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $aporte = Aporte::findOrFail($id);

        $fields = $request->validate([
            'monto'  => 'sometimes|numeric|min:0',
            'motivo' => 'sometimes|string',
            'pagado' => 'sometimes|boolean',
        ]);

        $aporte->update($fields);

        return response()->json($aporte->load('vivienda.socio'));
    }

    public function destroy($id)
    {
        $aporte = Aporte::findOrFail($id);
        $aporte->delete();

        return response()->json(null, 204);
    }
}
