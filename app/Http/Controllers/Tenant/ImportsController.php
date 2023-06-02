<?php

namespace App\Http\Controllers\Tenant;

use App\Exports\ImportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\ImportRequest as TenantImportRequest;
use App\Http\Resources\Tenant\ImportResource;
use App\Http\Resources\Tenant\ImportsCollection;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Imports;
use App\Models\Tenant\Item;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\PurchaseItem;
use App\Models\Tenant\Tariff;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

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

        $records = Purchase::where('import_id',$id)->where('tipo_doc_id',1)->get();
        $flete = Purchase::where('tipo_doc_id',2)
                    ->join('purchase_items',function($join) use($id){
                        $join->on('purchases.id','=','purchase_items.purchase_id')
                        ->where('purchase_items.import',$id)->where('concepto',4);
                    })
                    ->get();
        $totalFlete = $flete->sum('total_value');
        //Log::info(json_encode($flete));
        $seguro = Purchase::where('tipo_doc_id',2)
                    ->join('purchase_items',function($join) use($id){
                        $join->on('purchases.id','=','purchase_items.purchase_id')
                        ->where('purchase_items.import',$id)->where('concepto',5);
                    })
                    ->get();

        $totalSeguro = $seguro->sum('total_value');
        $gasto = Purchase::where('tipo_doc_id',2)
                    ->join('purchase_items',function($join) use($id){
                        $join->on('purchases.id','=','purchase_items.purchase_id')
                        ->where('purchase_items.import',$id)->where('concepto',1);
                    })
                    ->get();
        $totalgasto = $gasto->sum('total_value');


        $source = $this->transformReportImports($records, $totalFlete, $totalSeguro, $totalgasto);

        return (new ImportExport)
            ->records($source)
            ->download('Reporte_Importacion_' . Carbon::now() . '.xlsx');

    }

    private function updateItemCost($id){

        $records = Purchase::where('import_id',$id)->where('tipo_doc_id',1)->get();

        $fleteT = Purchase::where('tipo_doc_id',2)
                    ->join('purchase_items',function($join) use($id){
                        $join->on('purchases.id','=','purchase_items.purchase_id')
                        ->where('purchase_items.import',$id)->where('concepto',4);
                    })
                    ->get();
        $totalFlete = $fleteT->sum('total_value');
        $seguroT = Purchase::where('tipo_doc_id',2)
                    ->join('purchase_items',function($join) use($id){
                        $join->on('purchases.id','=','purchase_items.purchase_id')
                        ->where('purchase_items.import',$id)->where('concepto',5);
                    })
                    ->get();

        $totalSeguro = $seguroT->sum('total_value');
        $gastoT = Purchase::where('tipo_doc_id',2)
                    ->join('purchase_items',function($join) use($id){
                        $join->on('purchases.id','=','purchase_items.purchase_id')
                        ->where('purchase_items.import',$id)->where('concepto',1);
                    })
                    ->get();
        $totalgasto = $gastoT->sum('total_value');

        $totalFOD = $records->sum('total_value');

        foreach($records as $row){

            foreach($row->items as $key => $item){

                $arancel = Tariff::findOrFail($item->item->tariff_id);

                $flete = $item->total_value * $totalFlete /  $totalFOD;
                $seguro = $item->total_value * $totalSeguro /  $totalFOD;
                $gasto = $item->total_value * $totalgasto /  $totalFOD;

                $cif = 0;
                $advaloren = 0;
                $fodinfa = 0;
                $costo = 0;


                if($arancel->count() > 0){

                    $cif = $item->unit_value + ($flete/ $item->quantity) + ($seguro / $item->quantity);
                    $advaloren = $cif * ($arancel->advaloren/100);
                    $costo = $item->unit_value + $advaloren + ($gasto / $item->quantity);

                }else{

                    $cif = $item->unit_value + ($flete/ $item->quantity) + ($seguro / $item->quantity);
                    $costo = $item->unit_value + $advaloren + ($gasto / $item->quantity);

                }

                $itemUp = Item::find($item->item->id);
                $itemUp->update([
                    'purchase_unit_price' => round($costo,3),
                ]);

            }
        }

    }

    private function transformReportImports($resource, $fleteTotal, $totalSeguro, $totalgasto )
    {
        $totalFOD = $resource->sum('total_value');
        $records = null;

        foreach($resource as $row){

            $import = Imports::find($row->import_id);
            foreach($row->items as $key => $item){

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

        return (object) $records;
    }

    public function record($id)
    {
        $record = new ImportResource(Imports::findOrFail($id));
        return $record;
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
        $d_llegada = $request->d_llegada;
        $d_embarque = $request->d_embarque;

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
        if ($number) {
            $records->where('numeroImportacion', 'like', '%' . $number. '%');
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
