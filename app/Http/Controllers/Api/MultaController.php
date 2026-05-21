<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Multa;
use Illuminate\Http\Request;

class MultaController extends Controller
{
    public function index(Request $request)
    {
        $query = Multa::with('vivienda.socio');

        if ($request->filled('vivienda_id')) {
            $query->where('vivienda_id', $request->vivienda_id);
        }
        if ($request->filled('pagado')) {
            $query->where('pagado', $request->boolean('pagado'));
        }

        $perPage = $request->integer('per_page', 50);
        $perPage = min($perPage, 1000);

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

        $multa = Multa::create($fields);

        return response()->json($multa->load('vivienda.socio'), 201);
    }

    public function show($id)
    {
        return response()->json(Multa::with(['vivienda.socio', 'vivienda.zona'])->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $multa = Multa::findOrFail($id);

        $fields = $request->validate([
            'monto'  => 'sometimes|numeric|min:0',
            'motivo' => 'sometimes|string',
            'pagado' => 'sometimes|boolean',
        ]);

        $multa->update($fields);

        return response()->json($multa->load('vivienda.socio'));
    }

    public function destroy($id)
    {
        $multa = Multa::findOrFail($id);
        $multa->delete();

        return response()->json(null, 204);
    }
}
