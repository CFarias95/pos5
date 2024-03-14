<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\Traits\OfflineTrait;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Traits\FinanceTrait;
use Barryvdh\DomPDF\Facade as PDF;
use Barryvdh\DomPDF\PDF as DomPDFPDF;
use Carbon\Carbon;
use DOMDocument;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Report\Exports\PlanCuentasExport;
use Modules\Report\Http\Resources\PlanCuentasCollection;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Xml;
use League\CommonMark\Util\Xml as UtilXml;
use SimpleXMLElement;

class ATSController extends Controller
{
    use FinanceTrait;
    use OfflineTrait;
    use StorageDocument;

    protected $company;


    public function index()
    {
        $company = Company::select('soap_type_id')->first();
        return view('report::ats.index', compact('company'));
    }


    public function datosSP()
    {
        $sp = DB::connection('tenant')->select("CALL SP_PlanCuentas();");
        $collection = collect($sp);
        $per_page = (config('tenant.items_per_page'));
        $page = request()->query('page') ?? 1;
        $paginatedItems = $collection->slice(($page - 1) * $per_page, $per_page)->all();
        $paginatedCollection = new LengthAwarePaginator($paginatedItems, count($collection), $per_page, $page);
        return new PlanCuentasCollection($paginatedCollection);
    }

    public function generateReport(Request $request){

        $date_star = $request->date_start;
        $date_end = $request->date_end;

        $cabecera = DB::connection('tenant')->select("CALL SP_INFORMANTE(?, ?)",[$date_star, $date_end]);
        $cabeceraC = DB::connection('tenant')->select("CALL SP_COMPRAS_CABECERACOMPRAS(?, ?)",[$date_star, $date_end]);
        $cabeceraCompra = collect($cabeceraC)->transform(function($row) use($date_star, $date_end){
            $cabeceraPE = DB::connection('tenant')->select("CALL SP_COMPRAS_CABECERACOMPRAS_pagoExterior(?, ?, ?)",[$date_star, $date_end, $row->codCompra]);
            $row;
            $row->pagoExterior = (array) $cabeceraPE[0];
            return $row;
        });

        //Log::info('cabecera: '.json_encode($cabeceraCompra));

        $formaPagoC = DB::connection('tenant')->select("CALL SP_COMPRAS_formasDePago(?, ?)",[$date_star, $date_end]);
        $retencionesCompras = DB::connection('tenant')->select("CALL SP_COMPRAS_retencionesCompras(?, ?)",[$date_star, $date_end]);
        $reembolso = DB::connection('tenant')->select("CALL SP_COMPRAS_reembolsos(?, ?)",[$date_star, $date_end]);


        $detalleVenta = DB::connection('tenant')->select("CALL SP_VENTAS_detalleVentas(?, ?)",[$date_star, $date_end]);
        $formaPagoV = DB::connection('tenant')->select("CALL SP_VENTAS_formasDePago(?, ?)",[$date_star, $date_end]);
        $ventaEst = DB::connection('tenant')->select("CALL SP_VENTAS_ventasEstablecimiento(?, ?)",[$date_star, $date_end]);

        $detalleAnulados = DB::connection('tenant')->select("CALL SP_ANULADOS(?, ?)",[$date_star, $date_end]);


        //$data = (array)$cabecera[0];
        //$data['compras']['cabecerasCompras']['cabeceraCompra']= ($cabeceraCompra);
        //$data['compras']['formasDePago']['formaPago']= $formaPagoC;
        //$data['compras']['retencionesCompras']['detalleAir']= $retencionesCompras;
        //$data['compras']['reembolsos']['reembolso']= $reembolso;

        //$data['ventas']['detalleVentas'] = $detalleVenta;
        //$data['ventas']['formasDePago']['formaPago'] = $formaPagoV;
        //$data['ventasEstablecimiento']['ventaEst'] = $ventaEst;

        $data['anulados']['detalleAnulados'] = $detalleAnulados;

        $xml = new SimpleXMLElement('<iva/>');
        //Log::info(json_encode($data));

        //agregamos los nosdos de iva principales
        foreach ($cabecera[0] as $key => $value) {
            if (is_array($value)) {
                $child = $xml->addChild($key);
                $this->addChildToArray($child, $value);
            }elseif (is_object($value)) {
                $child = $xml->addChild($key);
                $this->addChildToArray($child,(array)$value);
            } else {
                $xml->addChild($key, $value);
            }
        }

        $childCompra = $xml->addChild('compras');
        $childCabecerasCompras = $childCompra->addChild('cabecerasCompras');

        //agregamos los nosdos de cabeceraCompra
        foreach ($cabeceraCompra as $key => $value) {
            if (is_array($value) ) {
                $child = $childCabecerasCompras->addChild('cabeceraCompra');
                $this->addChildToArray($child, $value);
            }elseif (is_object($value)) {
                $child = $childCabecerasCompras->addChild('cabeceraCompra');
                $this->addChildToArray($child, (array) $value);
            } else {
                $childCabecerasCompras->addChild($key, $value);
            }
        }

        $chilsformasDePago = $childCompra->addChild('formasDePago');
        //agregamos los nodos de formaPago
        foreach ($formaPagoC as $key => $value) {
            if (is_array($value) ) {
                $child = $chilsformasDePago->addChild('formaPago');
                $this->addChildToArray($child, $value);
            }elseif (is_object($value)) {
                $child = $chilsformasDePago->addChild('formaPago');
                $this->addChildToArray($child, (array) $value);
            } else {
                $chilsformasDePago->addChild($key, $value);
            }
        }

        $chilsretencionesCompras = $childCompra->addChild('retencionesCompras');
        //agregamos los nodos de retenciones compra
        foreach ($retencionesCompras as $key => $value) {
            if (is_array($value) ) {
                $child = $chilsretencionesCompras->addChild('detalleAir');
                $this->addChildToArray($child, $value);
            }elseif (is_object($value)) {
                $child = $chilsretencionesCompras->addChild('detalleAir');
                $this->addChildToArray($child, (array) $value);
            } else {
                $chilsretencionesCompras->addChild($key, $value);
            }
        }

        $chilsreembolsos = $childCompra->addChild('reembolsos');
        //agregamos los nodos de reembolsos
        foreach ($reembolso as $key => $value) {
            if (is_array($value) ) {
                $child = $chilsreembolsos->addChild('reembolso');
                $this->addChildToArray($child, $value);
            }elseif (is_object($value)) {
                $child = $chilsreembolsos->addChild('reembolso');
                $this->addChildToArray($child, (array) $value);
            } else {
                $chilsreembolsos->addChild($key, $value);
            }
        }

        //VENTAS
        $childVenta = $xml->addChild('ventas');

        //agregamos nodo de detalleVenta
        foreach ($detalleVenta as $key => $value) {
            if (is_array($value) ) {
                $child = $childVenta->addChild('detalleVentas');
                $this->addChildToArray($child, $value);
            }elseif (is_object($value)) {
                $child = $childVenta->addChild('detalleVentas');
                $this->addChildToArray($child, (array) $value);
            } else {
                $childVenta->addChild($key, $value);
            }
        }

        $childformasDePago = $childVenta->addChild('formasDePago');
        //agregamos los nodos de formaPago
        foreach ($formaPagoV as $key => $value) {
            if (is_array($value) ) {
                $child = $childformasDePago->addChild('formaPago');
                $this->addChildToArray($child, $value);
            }elseif (is_object($value)) {
                $child = $childformasDePago->addChild('formaPago');
                $this->addChildToArray($child, (array) $value);
            } else {
                $childformasDePago->addChild($key, $value);
            }
        }

        //VENTAS ESTABLECIMIENTO
        $childVentaEst = $xml->addChild('ventasEstablecimiento');
        //agregamos nodo de ventaEst
        foreach ($ventaEst as $key => $value) {
            if (is_array($value) ) {
                $child = $childVentaEst->addChild('ventaEst');
                $this->addChildToArray($child, $value);
            }elseif (is_object($value)) {
                $child = $childVentaEst->addChild('ventaEst');
                $this->addChildToArray($child, (array) $value);
            } else {
                $childVentaEst->addChild($key, $value);
            }
        }

        //VENTAS EXPORTACIONES
        $childexportaciones = $xml->addChild('exportaciones');

        //VENTAS ESTABLECIMIENTO
        $childrecap = $xml->addChild('recap');

        //VENTAS ESTABLECIMIENTO
        $childfideicomisos = $xml->addChild('fideicomisos');

        //VENTAS ANULADOS
        $childanulados = $xml->addChild('anulados');
        //agregamos nodo de DETALLE ANULADO
        foreach ($detalleAnulados as $key => $value) {
            if (is_array($value) ) {
                $child = $childanulados->addChild('detalleAnulados');
                $this->addChildToArray($child, $value);
            }elseif (is_object($value)) {
                $child = $childanulados->addChild('detalleAnulados');
                $this->addChildToArray($child, (array) $value);
            } else {
                $childanulados->addChild($key, $value);
            }
        }

        //VENTAS rendFinancieros
        $childfrendFinancieros = $xml->addChild('rendFinancieros');

        $xmlString = $xml->asXML();

        $filename = "ReporteATS.xml";
        Storage::disk('tenant')->put('ats'.DIRECTORY_SEPARATOR.$filename, $xmlString);

        return url('')."/reports/ats/print";
    }

    public function getFile(){

        $filename = "ReporteATS.xml";
        return Storage::disk('tenant')->download('ats'.DIRECTORY_SEPARATOR.$filename);

    }

    private function addChildToArray(SimpleXMLElement $xml, array $data) {
        foreach ($data as $key => $value) {
            if (is_array($value) ) {
                $child = $xml->addChild($key);
                $this->addChildToArray($child, $value);
            }elseif (is_object($value)) {
                $child = $xml->addChild($key);
                $this->addChildToArray($child, (array) $value);
            } else {
                $xml->addChild($key, $value);
            }
        }
    }

    public function pdf(Request $request)
    {

        $company = Company::first();
        $records = DB::connection('tenant')->select("CALL SP_PlanCuentas();");
        $usuario_log = Auth::user();
        $fechaActual = date('d/m/Y');

        $pdf = PDF::loadView('report::plan_cuentas.plan_cuenta_pdf', compact("records", "company", "usuario_log", "request"));

        $filename = 'Reporte_Plan_Ventas_' . date('YmdHis');

        return $pdf->download($filename . '.pdf');
    }

    public function excel()
    {
        $company = Company::first();
        $records = DB::connection('tenant')->select("CALL SP_PlanCuentas();");
        $usuario_log = Auth::user();
        $fechaActual = date('d/m/Y');

        $documentExport = new PlanCuentasExport();
        $documentExport
            ->records($records)
            ->company($company)
            ->usuario_log($usuario_log)
            ->fechaActual($fechaActual);

        return $documentExport->download('Reporte_plan_de_cuenta' . Carbon::now() . '.xlsx');
    }
}
