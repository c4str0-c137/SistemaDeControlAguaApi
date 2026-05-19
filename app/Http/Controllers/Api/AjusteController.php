<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ajuste;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AjusteController extends Controller
{
    public function index()
    {
        return response()->json(Ajuste::orderBy('clave')->get());
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'clave'       => 'required|string|unique:ajustes,clave',
            'valor'       => 'required|string',
            'descripcion' => 'nullable|string',
        ]);

        $ajuste = Ajuste::create($fields);

        return response()->json($ajuste, 201);
    }

    public function update(Request $request, $clave)
    {
        $ajuste = Ajuste::where('clave', $clave)->first();

        if (!$ajuste) {
            return response()->json([
                'error' => "Ajuste '{$clave}' no existe. Use POST /ajustes para crearlo."
            ], 404);
        }

        $fields = $request->validate([
            'valor'       => 'required|string',
            'descripcion' => 'nullable|string',
        ]);

        $ajuste->update($fields);

        return response()->json($ajuste);
    }

    public function destroy($clave)
    {
        $ajuste = Ajuste::where('clave', $clave)->first();

        if (!$ajuste) {
            return response()->json(['error' => "Ajuste '{$clave}' no existe."], 404);
        }

        $clavesProtegidas = ['multa_mora', 'meses_mora_deudor', 'monto_fijo_anual', 'dia_vencimiento'];

        if (in_array($clave, $clavesProtegidas)) {
            return response()->json([
                'error' => "No se puede borrar el ajuste '{$clave}'. Es un ajuste del sistema."
            ], 422);
        }

        $ajuste->delete();

        return response()->json(['message' => "Ajuste '{$clave}' eliminado"]);
    }
}
