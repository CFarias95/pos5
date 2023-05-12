<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
	public function authorize()
	{
		return true;
	}

	public function rules()
	{
		return [
			'supplier_id' => [
				'required',
			],
			'series' => [
				'required',
			],
			'date_of_issue' => [
				'required',
			],
            'items' => [
                'required',
                'array',
            ],
            ''

		];
	}
}
