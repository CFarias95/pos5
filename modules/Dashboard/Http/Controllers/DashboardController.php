<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Exports\AccountsReceivable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Dashboard\Helpers\DashboardData;
use Modules\Dashboard\Helpers\DashboardUtility;
use Modules\Dashboard\Helpers\DashboardSalePurchase;
use Modules\Dashboard\Helpers\DashboardView;
use Modules\Dashboard\Helpers\DashboardStock;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant\Document;
use App\Models\Tenant\Company;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Arr;
use Modules\Dashboard\Helpers\DashboardInventory;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\ConfigurationCash;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Modules\Dashboard\Http\Resources\DashboardSaleNoteSPCollection;
use Modules\Dashboard\Http\Resources\DashboardVentasVendedorSPCollection;

/**
 * Class DashboardController
 *
 * @package Modules\Dashboard\Http\Controllers
 * @mixin Controller
 */
class DashboardController extends Controller
{
    public function index()
    {
        if(auth()->user()->type != 'admin' || !auth()->user()->searchModule('dashboard'))
            return redirect()->route('tenant.documents.index');

        $company = Company::select('soap_type_id')->first();
        $soap_company  = $company->soap_type_id;
        $configuration = Configuration::first();
        //$currency=$configuration->currency_type_id;
        $currency=$configuration->currency_type_id;

        

        
        $cash= ConfigurationCash::where('id','=',$currency);
           

        return view('dashboard::index', compact('soap_company','configuration','cash'));
    }

    public function filter()
    {
        return [
            'establishments' => DashboardView::getEstablishments()
        ];
    }

   




    public function globalData()
    {
        return response()->json((new DashboardData())->globalData(), 200);
    }

    public function data(Request $request)
    {
        return [
            'data' => (new DashboardData())->data($request->all()),
        ];
    }

    // public function unpaid(Request $request)
    // {
    //     return [
    //             'records' => (new DashboardView())->getUnpaid($request->all())
    //     ];
    // }

    // public function unpaidall()
    // {

    //     return Excel::download(new AccountsReceivable, 'Allclients.xlsx');

    // }

    public function data_aditional(Request $request)
    {
        return [
            'data' => (new DashboardSalePurchase())->data($request->all()),
        ];
    }

    public function stockByProduct(Request $request)
    {
        return  (new DashboardStock())->data($request);
    }


    public function utilities(Request $request)
    {
        return [
            'data' => (new DashboardUtility())->data($request->all()),
        ];
    }

    public function df()
    {
        $path = app_path();
        //df -m -h --output=used,avail,pcent /

        $used = new Process('df -m -h --output=used /');
        $used->run();
        if (!$used->isSuccessful()) {
            return ['error'];
            throw new ProcessFailedException($used);
        }
        $disc_used = $used->getOutput();
        $array[] = str_replace("\n","",$disc_used);

        $avail = new Process('df -m -h --output=avail /');
        $avail->run();
        if (!$avail->isSuccessful()) {
            return ['error'];
            throw new ProcessFailedException($avail);
        }
        $disc_avail = $avail->getOutput();
        $array[] = str_replace("\n","",$disc_avail);

        $pcent = new Process('df -m -h --output=pcent /');
        $pcent->run();
        if (!$pcent->isSuccessful()) {
            return ['error'];
            throw new ProcessFailedException($pcent);
        }
        $disc_pcent = $pcent->getOutput();
        $array[] = str_replace("\n","",$disc_pcent);

        return $array;


    }

    /**
     * Extensión de ventas por producto
     *
     */
    public function salesByProduct()
    {
        return view('dashboard::sales_by_product');
    }
    
    public function productOfDue(Request $request)
    {
        return  (new DashboardInventory())->data($request);
    }

    public function saleNoteSP(Request $request)
    {
        $sp = DB::connection('tenant')->select("CALL SP_NotasVentasDashboard(?,?);", [$request->date_start, $request->date_end]);

        $sp1 = array();
        $sp2 = [];
        foreach($sp as $row)
        {
            foreach($row as $key => $data)
            {
                array_push($sp1, $data);
                array_push($sp2, $key);
            }
            break;
        }

        //Log::info('sp - '.json_encode($sp));
        //Log::info('sp1 - '.json_encode($sp1));
        //Log::info('sp2 - '.json_encode($sp2));

        $collection = collect($sp);
        $per_page = (config('tenant.items_per_page'));
        $page = request()->query('page') ?? 1;
        $paginatedItems = $collection->slice(($page - 1) * $per_page, $per_page)->all();
        $paginatedCollection = new LengthAwarePaginator($paginatedItems, count($collection), $per_page, $page);
        $paginatedCollection['datos'] = $sp2;

        //Log::info('sp - '.$collection);

        return new DashboardSaleNoteSPCollection($paginatedCollection);
    }

    public function comprobantesSP(Request $request)
    {
        $sp = DB::connection('tenant')->select("CALL SP_VentasVendedor(?,?);", [$request->date_start, $request->date_end]);

        $sp1 = array();
        $sp2 = [];
        foreach($sp as $row)
        {
            foreach($row as $key => $data)
            {
                array_push($sp1, $data);
                array_push($sp2, $key);
            }
            break;
        }

        //Log::info('sp - '.json_encode($sp));
        //Log::info('sp1 - '.json_encode($sp1));
        //Log::info('sp2 - '.json_encode($sp2));

        $collection = collect($sp);
        $per_page = (config('tenant.items_per_page'));
        $page = request()->query('page') ?? 1;
        $paginatedItems = $collection->slice(($page - 1) * $per_page, $per_page)->all();
        $paginatedCollection = new LengthAwarePaginator($paginatedItems, count($collection), $per_page, $page);
        $paginatedCollection['datos'] = $sp2;

        //Log::info('sp - '.$collection);

        return new DashboardVentasVendedorSPCollection($paginatedCollection);
    }

    public function graph_sale_noteSP(Request $request)
    {
        $sp = DB::connection('tenant')->select("CALL SP_NotasVentasDashboard(?,?);", [$request->date_start, $request->date_end]);

        $sp1 = array();
        $sp2 = [];
        foreach($sp as $row)
        {
            foreach($row as $key => $data)
            {
                array_push($sp1, $data);
                array_push($sp2, $key);
            }
            break;
        }
        $sp3 = [];
        $sp4 = [];
        foreach($sp as $row)
        {

            foreach($row as $key => $data)
            {
                array_push($sp3, $data);   
            }
            $sp3 = array_slice($sp3, 1);
            //Log::info('slice - '.json_encode($sp3));
            array_push($sp4, $sp3);
            $sp3 = [];
        }
        //Log::info('sp4 - '.json_encode($sp4));

        return [
            'graph' => [
                'labels' => ["cantidad","total"],
                'datasets' => [
                    [
                        'label' => 'Ventas totales por linea',
                        'data' => $sp4,
                        'backgroundColor' => [
                            'rgb(54, 162, 235)',
                            'rgb(255, 99, 132)',
                        ]
                    ]
                ],
            ],
        ];
    }

}
