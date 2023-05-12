<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->input('id');
        return [
            'rate_name' => [
                'required',
                Rule::unique('tenant.rates')->ignore($id),
                'max:50',
            ]
        ];
    }
}
