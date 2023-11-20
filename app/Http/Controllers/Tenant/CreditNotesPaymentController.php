<?php

namespace App\Http\Controllers\Tenant;

use App\Models\Tenant\CreditNotesPayment;
use App\Models\Tenant\Purchase;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\CreditNotePaymentCollection;
use App\Http\Resources\Tenant\RetentionCollection;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Document;
use App\Models\Tenant\Person;

class CreditNotesPaymentController extends Controller
{
    private $config;
    private $company;

    public function __construct()
    {
        $this->config = Configuration::first();
        $this->company = Company::first();
    }

    public function index()
    {
        return view('tenant.credit_note_payments.index');
    }

    public function columns()
    {
        $columns =  [
            'user_id' => 'Rezón social',
        ];
        $persons = Person::all()->transform(function($row){
            return[
                'id' =>$row->id,
                'name' => $row->name,
                'type' => $row->type,
            ];
        });

        return compact("columns","persons");
    }

    public function list($id){
        $credits = CreditNotesPayment::where('user_id',$id)->get();
        $credits->transform(function($row){
            $document = null;

            if($row->purchase_id){
                $document = Purchase::find($row->purchase_id);
            }

            if($row->document_id){
                $document = Document::find($row->document_id);
            }

            $name = $document->series.'-'.$document->number;
            $valor = $row->amount - $row->used;

            return [
                'id' => $row->id,
                'name' => $name.'/'.$valor,
                'amount' => $valor
            ];
        });
        return compact("credits");
    }

    public function records(Request $request){

        $records = $this->getRecords($request);
        return $records;
    }

    public function getRecords($request){

        if(isset($request->column) && isset($request->value)){
            $records = CreditNotesPayment::where($request->column, $request->value);
        }else{
            $records = CreditNotesPayment::query();
        }

        return new CreditNotePaymentCollection($records->paginate(config('tenant.items_per_page')));

    }
}
