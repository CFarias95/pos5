<?php

namespace App\Http\Controllers\Tenant;

use App\Models\Tenant\PurchaseDocumentTypes2;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\DocumentTypePurchaseRequest;
use App\Http\Resources\Tenant\PurchaseDocumentTypeCollection;
use App\Http\Resources\Tenant\PurchaseDocumentTypeResource;
use App\Models\Tenant\Catalogs\PurchaseDocumentType;
use App\Models\Tenant\Configuration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;
use SebastianBergmann\Environment\Console;

class DocumentPurchaseTypy2Controller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $configuration = Configuration::getPublicConfig();
        return view('tenant.purchases.document_types',
        compact('configuration'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DocumentTypePurchaseRequest $request)
    {
        $id = $request->input('idType');
        $tariff = PurchaseDocumentTypes2::where('idType', $id)->get();

        if($tariff && $tariff->count() > 0){

            DB::connection('tenant')->table('cat_purchase_document_types2')->where('idType', $id)->update([
                'short' => $request->input('short'),
                'DocumentTypeID' => $request->input('DocumentTypeID'),
                'active' => $request->input('active'),
                'description' => $request->input('description'),
                'accountant' => $request->input('accountant'),
                'stock' => $request->input('stock'),
                'sign' => $request->input('sign'),
                'cost' => $request->input('cost'),

            ]);

            $msg = 'Tipo de documento de compra actualizado con éxito';

        }else{

            $tariff = new PurchaseDocumentTypes2();
            $data = $request->all();
            $tariff->fill($data);
            $tariff->save();
            $msg = '';
            $msg = 'Tipo de documento de compra generado con éxito';
        }


        return [
            'success' => true,
            'message' => $msg,
            'id' => $id
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tenant\PurchaseDocumentTypes2  $purchaseDocumentTypes2
     * @return \Illuminate\Http\Response
     */
    public function show(PurchaseDocumentTypes2 $purchaseDocumentTypes2)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tenant\PurchaseDocumentTypes2  $purchaseDocumentTypes2
     * @return \Illuminate\Http\Response
     */
    public function edit(PurchaseDocumentTypes2 $purchaseDocumentTypes2)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tenant\PurchaseDocumentTypes2  $purchaseDocumentTypes2
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PurchaseDocumentTypes2 $purchaseDocumentTypes2)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tenant\PurchaseDocumentTypes2  $purchaseDocumentTypes2
     * @return \Illuminate\Http\Response
     */
    public function destroy(PurchaseDocumentTypes2 $purchaseDocumentTypes2)
    {
        //
    }

    public function record($id)
    {

        $record = new PurchaseDocumentTypeResource(PurchaseDocumentTypes2::where('idType',$id)->get()[0]);
        //$record = PurchaseDocumentTypes2::where('idType',$id)->get();
        return $record;
    }

    public function records(Request $request)
    {
        $records = $this->getRecords($request);
        return new PurchaseDocumentTypeCollection($records->paginate(config('tenant.items_per_page')));
    }

    private function getRecords($request)
    {

        $tariff = $request->tariff;
        $active= $request->active;
        $records = PurchaseDocumentTypes2::query();

        if ($tariff) {
            $records->where('tariff', 'like', '%' . $tariff . '%');
        }

        if ($active) {
            if($active > 1){
                $records->where('active', 'like', '%' . 0 . '%');
            }else{
                $records->where('active', 'like', '%' . 1 . '%');
            }

        }

        return $records;
    }

    public function tables()
    {
        $doc_types = PurchaseDocumentType::whereActive()->get();

        return compact(
            'doc_types',

        );
    }
}
