<?php

    namespace Modules\Production\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class ProductionRequest extends FormRequest
    {

        public function authorize()
        {
            return true;
        }

        public function rules()
        {
            $rules = [
                'records_id' => 'required|in:01,02,03,04',
                'item_id' => [
                    'required'
                ],
                'warehouse_id' => [
                    'required'
                ],
                'quantity' => [
                    'required'
                ],
                'name' => [ 'required'],
                'machine_id' => [ 'required'],
            ];
        
            $state = $this->get('records_id');
        
            if ($state === '02') {
                $rules['date_start'] = 'required';
                $rules['time_start'] = 'required';
            }

        
            if ($state === '03') {
                $rules['date_end'] = 'required';
                $rules['time_end'] = 'required';
            }
        
            return $rules;
        }
    }
