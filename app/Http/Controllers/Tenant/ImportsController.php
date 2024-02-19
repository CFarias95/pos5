<?php

namespace App\Http\Controllers\Tenant;

use App\Exports\ImportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\ImportRequest as TenantImportRequest;
use App\Http\Resources\Tenant\ImportResource;
use App\Http\Resources\Tenant\ImportsCollection;
use App\Models\Tenant\AccountingEntries;
use App\Models\Tenant\AccountingEntryItems;
use App\Models\Tenant\AccountMovement;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Imports;
use App\Models\Tenant\Incoterm;
use App\Models\Tenant\Item;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\PurchaseItem;
use App\Models\Tenant\Tariff;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImportsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $configuration = Configuration::getPublicConfig();

        return view('tenant.imports.index',
        compact('configuration'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (auth()->user()->type == 'integrator')
            return redirect('/imports');

        $configuration = Configuration::first();

        return view('tenant.imports.form', compact( 'configuration'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TenantImportRequest $request)
    {

        $id = $request->input('id');
        $estado = $request->input('estado');
        $import = Imports::firstOrNew(['id' => $id]);
        $data = $request->all();
        unset($data['id']);

        $import->fill($data);
        $import->save();

        if($id && $estado == 'Liquidada'){
            $this->updateItemCost($id);
        }
        $msg = '';
        $msg = ($id) ? 'Importacion editada con éxito' : 'Importacion registrada con éxito';

        return [
            'success' => true,
            'message' => $msg,
            'id' => $import->id
        ];
    }

    public function liquidationsReport($id){

        $source = DB::connection("tenant")->select("CALL SP_Reporteimportacion(?)",[$id]);

        return (new ImportExport)
            ->records($source)->view();
            //->preview('Reporte_Importacion_' . Carbon::now() . '.xlsx')->toHtml();

    }
    public function liquidationsReportExcel($id){
        $source = DB::connection("tenant")->select("CALL SP_Reporteimportacion(?)",[$id]);

        return (new ImportExport)
            ->records($source)
            ->download('Reporte_Importacion_' . Carbon::now() . '.xlsx');
    }
    //genera el asiento contable del ISD registrado en la importacion
    public function isdAccountant($id){

        $document = Imports::firstOrNew(['id' => $id]);

        if(!isset($document->isd) || !isset($document->cta_isd)){
            return[
                'success'=>false,
                "message"=>"No se puede generar el asiento, verifica la cuenta asociada y el valor asignado a ISD de la importación"
            ];
        }
        $valor = DB::connection("tenant")->select("CALL SP_BaseparaIsd('$id')");

        $valor = round($valor[0]->Importe * ($document->isd / 100),2);
        Log::info("Valor recuperado : ".$valor);
        if($valor && $valor > 0){

            $existe = AccountingEntries::where('document_id','ISD'.$id)->get();

            if($existe && $existe->count() > 0 ){
                $existe[0]->total_debe = $valor;
                $existe[0]->total_haber = $valor;

                $entries = AccountingEntryItems::where('accounting_entrie_id',$existe[0]->id)->get();

                foreach($entries as $entry){
                    if($entry->seat_line == 1){
                        $entry->haber = $valor;
                    }
                    if($entry->seat_line == 2){
                        $entry->debe = $valor;
                    }
                    $entry->save();
                }

                $existe[0]->save();

                return[
                    'success'=>true,
                    'message' => 'Asiento contable ISD actualizado'
                ];

            }else{

                try{
                    $idauth = auth()->user()->id;
                    $lista = AccountingEntries::where('user_id', '=', $idauth)->latest('id')->first();
                    $ultimo = AccountingEntries::latest('id')->first();
                    $configuration = Configuration::first();
                    if (empty($lista)) {
                        $seat = 1;
                    } else {

                        $seat = $lista->seat + 1;
                    }

                    if (empty($ultimo)) {
                        $seat_general = 1;
                    } else {
                        $seat_general = $ultimo->seat_general + 1;
                    }

                    $comment = ' ISD Importacion '. $document->numeroImportacion ;

                    $cabeceraC = new AccountingEntries();
                    $cabeceraC->user_id = auth()->user()->id;
                    $cabeceraC->seat = $seat;
                    $cabeceraC->seat_general = $seat_general;
                    $cabeceraC->seat_date = $document->updated_at;
                    $cabeceraC->types_accounting_entrie_id = 3;
                    $cabeceraC->comment = $comment;
                    $cabeceraC->serie = 'IMPORTACION';
                    $cabeceraC->number = $seat;
                    $cabeceraC->total_debe = $valor;
                    $cabeceraC->total_haber = $valor;
                    $cabeceraC->revised1 = 0;
                    $cabeceraC->user_revised1 = 0;
                    $cabeceraC->revised2 = 0;
                    $cabeceraC->user_revised2 = 0;
                    $cabeceraC->currency_type_id = $configuration->currency_type_id;
                    $cabeceraC->doctype = 99;
                    $cabeceraC->is_client = false;
                    $cabeceraC->establishment_id = null;
                    $cabeceraC->establishment = '';
                    $cabeceraC->prefix = 'ASC';
                    $cabeceraC->person_id = null;
                    $cabeceraC->external_id = Str::uuid()->toString();
                    $cabeceraC->document_id = 'ISD'.$id;

                    $cabeceraC->save();
                    $cabeceraC->filename = 'ASC-'.$cabeceraC->id.'-'. date('Ymd');
                    $cabeceraC->save();

                    $detalle = new AccountingEntryItems();

                    $detalle->accounting_entrie_id = $cabeceraC->id;
                    $detalle->account_movement_id = $document->cta_isd;
                    $detalle->seat_line = 1;
                    $detalle->debe = 0;
                    $detalle->haber = $valor;
                    $detalle->save();

                    $detalle2 = new AccountingEntryItems();

                    $detalle2->accounting_entrie_id = $cabeceraC->id;
                    $detalle2->account_movement_id = $document->cuenta_contable;
                    $detalle2->seat_line = 2;
                    $detalle2->debe = $valor;
                    $detalle2->haber = 0;
                    $detalle2->save();

                    return[
                        'success'=>true,
                        'message' => 'Se generar el asiento contable ISD correctamente'
                    ];

                }catch(Exception $ex){

                    Log::error('Error al intentar generar el asiento contable de ISD ');
                    Log::error($ex->getMessage());
                    return[
                        'success'=>false,
                        'message' => 'No se pudo generar el asiento contable ISD'
                    ];
                }
            }
        }else{
            return[
                'success'=>false,
                'message'=>'No se encontraron valores validos para el calculo del ISD',
            ];
        }
    }

    //genera el asiento contable de Comunicaciones registrado en la importacion
    public function comunicationsAccountant($id){

        $document = Imports::firstOrNew(['id' => $id]);

        if(!isset($document->comunications) || !isset($document->cta_comunications)){
            return[
                'success'=>false,
                "message"=>"No se puede generar el asiento, verifica la cuenta asociada y el valor asignado a Comunicaciones en la importación"
            ];
        }
        //$valor = DB::connection("tenant")->select("CALL SP_Reporteimportacion(?)",[$id]);
        $valor = $document->comunications;

        if($valor && $valor > 0){

            $existe = AccountingEntries::where('document_id','COM'.$id)->get();

            if($existe && $existe->count() > 0 ){
                $existe[0]->total_debe = $valor;
                $existe[0]->total_haber = $valor;

                $entries = AccountingEntryItems::where('accounting_entrie_id',$existe[0]->id)->get();

                foreach($entries as $entry){
                    if($entry->seat_line == 1){
                        $entry->haber = $valor;
                    }
                    if($entry->seat_line == 2){
                        $entry->debe = $valor;
                    }
                    $entry->save();
                }

                $existe[0]->save();

                return[
                    'success'=>true,
                    'message' => 'Asiento contable Comunicaciones actualizado'
                ];

            }else{

                try{
                    $idauth = auth()->user()->id;
                    $lista = AccountingEntries::where('user_id', '=', $idauth)->latest('id')->first();
                    $ultimo = AccountingEntries::latest('id')->first();
                    $configuration = Configuration::first();
                    if (empty($lista)) {
                        $seat = 1;
                    } else {

                        $seat = $lista->seat + 1;
                    }

                    if (empty($ultimo)) {
                        $seat_general = 1;
                    } else {
                        $seat_general = $ultimo->seat_general + 1;
                    }

                    $comment = 'Comunicaciones Importacion '. $document->numeroImportacion ;

                    $cabeceraC = new AccountingEntries();
                    $cabeceraC->user_id = auth()->user()->id;
                    $cabeceraC->seat = $seat;
                    $cabeceraC->seat_general = $seat_general;
                    $cabeceraC->seat_date = $document->updated_at;
                    $cabeceraC->types_accounting_entrie_id = 3;
                    $cabeceraC->comment = $comment;
                    $cabeceraC->serie = 'IMPORTACION';
                    $cabeceraC->number = $seat;
                    $cabeceraC->total_debe = $valor;
                    $cabeceraC->total_haber = $valor;
                    $cabeceraC->revised1 = 0;
                    $cabeceraC->user_revised1 = 0;
                    $cabeceraC->revised2 = 0;
                    $cabeceraC->user_revised2 = 0;
                    $cabeceraC->currency_type_id = $configuration->currency_type_id;
                    $cabeceraC->doctype = 99;
                    $cabeceraC->is_client = false;
                    $cabeceraC->establishment_id = null;
                    $cabeceraC->establishment = '';
                    $cabeceraC->prefix = 'ASC';
                    $cabeceraC->person_id = null;
                    $cabeceraC->external_id = Str::uuid()->toString();
                    $cabeceraC->document_id = 'COM'.$id;

                    $cabeceraC->save();
                    $cabeceraC->filename = 'ASC-'.$cabeceraC->id.'-'. date('Ymd');
                    $cabeceraC->save();

                    $detalle = new AccountingEntryItems();

                    $detalle->accounting_entrie_id = $cabeceraC->id;
                    $detalle->account_movement_id = $document->cta_comunications;
                    $detalle->seat_line = 1;
                    $detalle->debe = 0;
                    $detalle->haber = $valor;
                    $detalle->save();

                    $detalle2 = new AccountingEntryItems();

                    $detalle2->accounting_entrie_id = $cabeceraC->id;
                    $detalle2->account_movement_id = $document->cuenta_contable;
                    $detalle2->seat_line = 2;
                    $detalle2->debe = $valor;
                    $detalle2->haber = 0;
                    $detalle2->save();

                    return[
                        'success'=>true,
                        'message' => 'Se generar el asiento contable Comunicaciones correctamente'
                    ];

                }catch(Exception $ex){

                    Log::error('Error al intentar generar el asiento contable de Comunicaciones');
                    Log::error($ex->getMessage());
                    return[
                        'success'=>false,
                        'message' => 'No se pudo generar el asiento contable de Comunicaciones'
                    ];
                }
            }
        }
    }

    private function updateItemCost($id){
        $result = DB::connection("tenant")->select("CALL SP_Liquidarimportacion(?)",[$id]);
        Log::info("valor liquidacion: ".$result[0]->totalimportacion);
        $this->createAccountingEntry($id,$result[0]->totalimportacion);
    }

    /* CREARE ACCOUNTING ENTRIES IMPORT*/
    private function createAccountingEntry($document_id,$valor){

        $document = Imports::firstOrNew(['id' => $document_id]);
        //Log::info('documento created: ' . json_encode($document));
        $entry = (AccountingEntries::get())->last();
        //ASIENTO CONTABLE DE FACTURAS
        try{
            $idauth = auth()->user()->id;
            $lista = AccountingEntries::where('user_id', '=', $idauth)->latest('id')->first();
            $ultimo = AccountingEntries::latest('id')->first();
            $configuration = Configuration::first();
            if (empty($lista)) {
                $seat = 1;
            } else {

                $seat = $lista->seat + 1;
            }

            if (empty($ultimo)) {
                $seat_general = 1;
            } else {
                $seat_general = $ultimo->seat_general + 1;
            }

            $comment = 'Importacion '. $document->numeroImportacion ;

            $total_debe = $valor;
            $total_haber = $valor;

            $cabeceraC = new AccountingEntries();
            $cabeceraC->user_id = auth()->user()->id;
            $cabeceraC->seat = $seat;
            $cabeceraC->seat_general = $seat_general;
            $cabeceraC->seat_date = $document->updated_at;
            $cabeceraC->types_accounting_entrie_id = 3;
            $cabeceraC->comment = $comment;
            $cabeceraC->serie = 'IMPORTACION';
            $cabeceraC->number = $seat;
            $cabeceraC->total_debe = $total_debe;
            $cabeceraC->total_haber = $total_haber;
            $cabeceraC->revised1 = 0;
            $cabeceraC->user_revised1 = 0;
            $cabeceraC->revised2 = 0;
            $cabeceraC->user_revised2 = 0;
            $cabeceraC->currency_type_id = $configuration->currency_type_id;
            $cabeceraC->doctype = 99;
            $cabeceraC->is_client = false;
            $cabeceraC->establishment_id = null;
            $cabeceraC->establishment = '';
            $cabeceraC->prefix = 'ASC';
            $cabeceraC->person_id = null;
            $cabeceraC->external_id = Str::uuid()->toString();
            $cabeceraC->document_id = 'I'.$document_id;

            $cabeceraC->save();
            $cabeceraC->filename = 'ASC-'.$cabeceraC->id.'-'. date('Ymd');
            $cabeceraC->save();


            $detalle = new AccountingEntryItems();

            $detalle->accounting_entrie_id = $cabeceraC->id;
            $detalle->account_movement_id = $configuration->cta_purchases;
            $detalle->seat_line = 1;
            $detalle->debe = $valor;
            $detalle->haber = 0;

            if($detalle->save() == false){
                $cabeceraC->delete();
                return;
            }

            $detalle2 = new AccountingEntryItems();
            $detalle2->accounting_entrie_id = $cabeceraC->id;
            $detalle2->account_movement_id = (isset($document->cuenta_contable))?$document->cuenta_contable:$configuration->cta_transit_imports;
            $detalle2->seat_line = 2;
            $detalle2->debe = 0;
            $detalle2->haber = $valor;

            if($detalle2->save() == false){
                $cabeceraC->delete();
                return;
            }

        }catch(Exception $ex){

            Log::error('Error al intentar generar el asiento contable');
            Log::error($ex->getMessage());
        }


    }
    private function transformReportImports($resource, $fleteTotal, $totalSeguro, $totalgasto )
    {
        $totalFOD = $resource->sum('total_value');
        $records = null;



        foreach($resource as $row){

            $import = Imports::find($row->import_id);

            foreach($row->items as $key => $item){

                if($item->item->unit_type_id != 'ZZ'){

                    $arancel = Tariff::find($item->item->tariff_id);

                    $flete = $item->total_value * $fleteTotal /  $totalFOD;
                    $seguro = $item->total_value * $totalSeguro /  $totalFOD;
                    $gasto = $item->total_value * $totalgasto /  $totalFOD;

                    $cif = 0;
                    $advaloren = 0;
                    $fodinfa = 0;
                    $iva = 0;
                    $costo = 0;
                    $factor = 0;

                    if($arancel && $arancel->count() > 0){

                        $cif = $item->unit_value + ($flete/ $item->quantity) + ($seguro / $item->quantity);
                        $advaloren = ($item->unit_value + ($seguro / $item->quantity)) * ($arancel->advaloren/100);
                        $fodinfa = $cif * $arancel->fodinfa;
                        $iva = ($cif + $advaloren + $fodinfa) * 0.12;
                        $costo = $item->unit_value + $advaloren + ($gasto / $item->quantity);
                        $factor = (($gasto / $item->quantity) + $cif) / $item->unit_value;

                    }else{

                        $cif = $item->unit_value + ($flete / $item->quantity) + ($seguro / $item->quantity);
                        $iva = ($cif + $advaloren + $fodinfa) * 0.12;
                        $costo = $item->unit_value + $advaloren + ($gasto / $item->quantity);
                        $factor = (($gasto / $item->quantity) + $cif) / $item->unit_value;

                    }

                    $records[] = [
                        'serie' => $row->series,
                        'numero' => $row->number,
                        'importacion' => $import->numeroImportacion,
                        'numLinea' => $key + 1,
                        'codArticulo' => $item->item->id,
                        'referencia' => $item->item->id,
                        'descripcion' => $item->item->name,
                        'partidaArancelaria' => ($arancel && $arancel->count > 0 ) ? $arancel->tariff : '',
                        'porcentajeAdvaloren' => ($arancel && $arancel->count > 0 ) ?$arancel->advaloren : 0,
                        'unidadestotal' => $item->quantity,
                        'fob' => round($item->unit_value,3),
                        'fobTotal' => round($item->total_value,3),
                        'flete' => round($flete / $item->quantity,3),
                        'fleteTotal' => round($flete,3),
                        'seguro' => round($seguro / $item->quantity,3),
                        'seguroTotal' => round($seguro,3),
                        'cif' => round($cif,3),
                        'advaloren' => round($advaloren,3),
                        'fodinfa' => round($fodinfa,3),
                        'iva' => round($iva,3),
                        'gastos' => round($gasto / $item->quantity,3),
                        'gastosTotal' => round($gasto,3),
                        'costo' => round($costo,3),
                        'totalLinea' => $item->total_value,
                        'factor' => round($factor,3),

                    ];
                }

            }
        }

        return (object) $records;
    }

    public function record($id)
    {
        $record = new ImportResource(Imports::findOrFail($id));
        return $record;
    }

    public function table()
    {
        $cta_accountants = AccountMovement::get();
        $incoterms = Incoterm::all();

        return compact("cta_accountants","incoterms");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tenant\Imports  $imports
     * @return \Illuminate\Http\Response
     */
    public function show(Imports $imports)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tenant\Imports  $imports
     * @return \Illuminate\Http\Response
     */
    public function edit(Imports $imports)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tenant\Imports  $imports
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Imports $imports)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tenant\Imports  $imports
     * @return \Illuminate\Http\Response
     */
    public function destroy(Imports $imports)
    {
        //
    }

    public function getRecords($request)
    {
        //Log::info('Request - '.$request->document_type_id);
        $d_llegada = $request->d_llegada;
        $d_embarque = $request->d_embarque;

        $num_import = $request->numeroImportacion;

        $date_of_issue = $request->date_of_issue;

        $number= $request->document_type_id;
        $tipoTransporte = $request->tipoTransporte;

        $estado = $request->estado;

        $fechaEmbarque = $request->fechaEmbarque;

        $fechaLlegada = $request->fechaLlegada;

        $records = Imports::query();

        if ($date_of_issue) {
            $records->where('created_at', 'like', '%' . $date_of_issue . '%');
        }
        if ($fechaEmbarque) {
            $records->where('fechaEmbarque', 'like', '%' . $fechaEmbarque . '%');
        }
        if ($fechaLlegada) {
            $records->where('fechaLlegada', 'like', '%' . $fechaLlegada . '%');
        }
        if ($num_import) {
            $records->where('numeroImportacion', 'like', '%' . $num_import. '%');
        }
        if ($estado) {
            $records->where('estado', 'like', '%' . $estado . '%');
        }
        if ($tipoTransporte) {
            $records->where('tipoTransporte', 'like', '%' . $tipoTransporte . '%');
        }

        return $records;
    }

    public function records(Request $request)
    {

        $records = $this->getRecords($request);

        return new ImportsCollection($records->paginate(config('tenant.items_per_page')));
    }
}
