<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PosDatedRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'posdated' => [
                'required',
                'max:100',
            ],

            'f_posdated' => [
                'required',
            ]
        ];
    }

    public function messages()
    {
        return [
            //JOINSOFTWARE
            'posdated.required' => 'Campo  es obligatorio.',
            'f_posdated.required' => 'Campo  es obligatorio.',

        ];
    }
}
