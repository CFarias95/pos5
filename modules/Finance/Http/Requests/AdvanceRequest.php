<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdvanceRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {

        return [
            'idMethodType' => [
                'required',
            ],
            'id_payment' => [
                'required',
            ],
            'idCliente' => [
                'required',
            ],
            'valor' => [
                'required',
            ],
        ];
    }
    public function messages()
	{
		return [
			'idMethodType.required' => 'El tipo de Anticipo es obligatorio',
            'id_payment.required' => 'El metodo de pago es obligatorio',
            'idCliente.required' => 'DEbe seleccionar un cliente/Proveedor',
            'valor.required' => 'El valor del anticipo no puede ser 0 (cero) o null',
		];
	}
}
