<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'document_type_id' => [
                'required',
            ],
            /* JOINSOFTWARE
            'series' => [
                'required',
            ],
            'number' => [
                'required',
            ],
            */
            'date_of_issue' => [
                'required',
            ],
            'customer_number' => [
                'required',
            ],
            /* JOINSOFTWARE
            'total' => [
                'required',
            ],
            */
            'clave_SRI' => [
                'required',
            ],
        ];
    }
}