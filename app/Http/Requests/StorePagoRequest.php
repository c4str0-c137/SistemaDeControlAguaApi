<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePagoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'vivienda_id'       => 'required|exists:viviendas,id',
            'periodo_id'        => 'required|exists:periodos,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'monto_total'       => 'required|numeric|min:0',
            'monto_fijo'        => 'nullable|numeric|min:0',
            'costo_consumo'     => 'nullable|numeric',
            'monto_alcantarillado' => 'nullable|numeric',
            'multa'             => 'nullable|numeric',
            'referencia'        => 'nullable|string',
            'observaciones'     => 'nullable|string',
            'lectura_anterior'  => 'nullable|integer',
            'lectura_actual'    => 'nullable|integer',
            'consumo'           => 'nullable|integer',
            'desgloce_rangos'   => 'nullable|array',
            'otros_detalles'    => 'nullable|array',
            'otros_detalles.*.tipo'   => 'required|string|in:consumo,multa,aporte,cargo_fijo,alcantarillado,conexion',
            'otros_detalles.*.monto'  => 'required|numeric',
            'otros_detalles.*.descripcion' => 'required|string',
        ];
    }
}
