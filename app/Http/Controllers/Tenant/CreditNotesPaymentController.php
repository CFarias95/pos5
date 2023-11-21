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
use Illuminate\Support\Facades\Log;

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
            'user_id' => 'Clientes',
            'user_id_2' => 'Proveedores',
            'created_at' => 'Fecha emisión',
        ];
        $suppliers = Person::where('type','suppliers')->get()->transform(function($row){
            return[
                'id' =>$row->id,
                'name' => $row->name,
                'type' => $row->type,
            ];
        });
        $customers = Person::where('type','customers')->get()->transform(function($row){
            return[
                'id' =>$row->id,
                'name' => $row->name,
                'type' => $row->type,
            ];
        });

        return compact("columns","suppliers","customers");
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

        $records = CreditNotesPayment::query();

        if(isset($request->column) && isset($request->value) && $request->column == 'user_id'){
            $records->where($request->column, $request->value);
        }

        if(isset($request->column) && isset($request->value) && $request->column == 'user_id_2'){
            $records->where('user_id', $request->value);
        }

        if(isset($request->column) && isset($request->value) && $request->column == 'created_at'){
            $records->whereDate('created_at','>=',$request->value);
        }


        if($request->included == 'false'){
            //Log::info('mostrar solo las que tengar un valor usado menor al total');
            $records->whereColumn('used','<','amount');
        }

        return new CreditNotePaymentCollection($records->paginate(config('tenant.items_per_page')));

    }
}
