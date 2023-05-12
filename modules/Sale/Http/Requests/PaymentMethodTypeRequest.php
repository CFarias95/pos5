<?php

namespace Modules\Sale\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentMethodTypeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        //JOINSOFTWARE ANADIR COD DEL SRI EN METODOS DE PAGO//
        $id = $this->input('id');
        return [
            'id' => [
                'required',
                Rule::unique('tenant.payment_method_types')->ignore($id),
            ],
            'description' => [
                'required',
            ], 
            'pago_sri' => [
                'required',
            ],
        ];
    }
}
