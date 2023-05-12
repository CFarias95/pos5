<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PersonRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        //JOINSOFTWARE AJUSTES CAMPOS OBLOGATORIOS CLIENTES//
        $id = $this->input('id');
        $type = $this->input('type');
        return [
            'number' => [
                'required',
                Rule::unique('tenant.persons')->where(function ($query) use($id, $type) {
                    return $query->where('type', $type)
                                 ->where('id', '<>' ,$id);
                })
            ],
            'name' => [
                'required',
                Rule::unique('tenant.persons')->where(function ($query) use($id, $type) {
                    return $query->where('type', $type)
                                 ->where('id', '<>' ,$id);
                })
            ],
            'identity_document_type_id' => [
                'required',
            ],
            'country_id' => [
                'required',
            ],
            // 'person_type_id' => [
            //     'required_if:type,"customers"',
            // ],
            'department_id' => [
                'required',
            ],
            'province_id' => [
                'required',
            ],
            'district_id' => [
                'required',
            ],
            'address' => [
                'required',
            ],
            'email' => [
                'required',
            ],
            'telephone' => [
                'required',
            ],
            'parteRel' => [
                'required_if:type,"suppliers"',
            ],
            'pagoLocExt' => [
                'required_if:type,"suppliers"',
            ],
            'pagoLocExtDoc' => [
                'required_if:pagoLocExt,"Exterior"',
            ],
            'internal_code' => 'max:100'
        ];
    }
}
