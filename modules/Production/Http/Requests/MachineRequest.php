<?php

    namespace Modules\Production\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class MachineRequest extends FormRequest
    {

        public function authorize()
        {
            return true;
        }

        public function rules()
        {

            return [
                'name' => [
                    'required',
                ],
                'model' => [
                    'required',
                ],
                'brand' => [
                    'required',
                ],
                'maximum_force' => [
                    'required',
                ],
                'minimum_force' => [
                    'required',
                ], 

                /*
                 *
            machine_type_id:null,
            */
            ];
        }
    }
