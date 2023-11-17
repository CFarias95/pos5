<?php

namespace App\Http\Controllers\Tenant;

use App\Models\Tenant\CreditNotesPayment;
use App\Models\Tenant\Purchase;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CreditNotesPaymentController extends Controller
{
    public function list($id){
        $credits = CreditNotesPayment::where('user_id',$id)->get();
        $credits->transform(function($row){
            $purchase = Purchase::find($row->purchase_id);
            $name = $purchase->series.'-'.$purchase->number;
            $valor = $row->amount - $row->used;

            return [
                'id' => $row->id,
                'name' => $name.'/'.$valor,
                'amount' => $valor
            ];
        });
        return compact("credits");
    }
}
