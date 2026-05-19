<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalcularDeudaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'vivienda_id' => 'required|exists:viviendas,id',
            'periodo_id'  => 'required|exists:periodos,id',
        ];
    }
}
