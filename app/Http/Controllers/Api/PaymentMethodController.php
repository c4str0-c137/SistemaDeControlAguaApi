<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentMethodController extends Controller
{
    public function index()
    {
        return response()->json(PaymentMethod::withCount('pagos')->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'name'        => 'required|string|unique:payment_methods,name',
            'description' => 'nullable|string',
        ]);

        $method = PaymentMethod::create($fields);

        return response()->json($method, 201);
    }

    public function show($id)
    {
        return response()->json(PaymentMethod::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $method = PaymentMethod::findOrFail($id);

        $fields = $request->validate([
            'name'        => 'sometimes|string|unique:payment_methods,name,' . $id,
            'description' => 'nullable|string',
        ]);

        $method->update($fields);

        return response()->json($method);
    }

    public function destroy($id)
    {
        $method = PaymentMethod::findOrFail($id);

        if ($method->pagos()->exists()) {
            return response()->json([
                'error' => 'No se puede borrar un método de pago con registros de pagos.'
            ], 422);
        }

        $method->delete();

        return response()->json(null, 204);
    }

    /**
     * Handle the incoming request (legacy compatibility).
     */
    public function __invoke(Request $request)
    {
        return $this->index();
    }
}
