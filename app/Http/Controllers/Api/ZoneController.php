<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function index()
    {
        return response()->json(Zone::withCount('viviendas')->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'name'        => 'required|string|unique:zones,name',
            'description' => 'nullable|string',
        ]);

        $zone = Zone::create($fields);

        return response()->json($zone, 201);
    }

    public function show($id)
    {
        return response()->json(Zone::withCount('viviendas')->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $zone = Zone::findOrFail($id);

        $fields = $request->validate([
            'name'        => 'sometimes|string|unique:zones,name,' . $id,
            'description' => 'nullable|string',
        ]);

        $zone->update($fields);

        return response()->json($zone);
    }

    public function destroy($id)
    {
        $zone = Zone::findOrFail($id);

        if ($zone->viviendas()->exists()) {
            return response()->json([
                'error' => 'No se puede borrar una zona con viviendas asignadas.'
            ], 422);
        }

        $zone->delete();

        return response()->json(null, 204);
    }
}
