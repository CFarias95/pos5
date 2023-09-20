<?php

namespace Modules\Report\Http\Controllers;

use App\Exports\RetentionsExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\RetentionCollection;
use Barryvdh\DomPDF\Facade as PDF;
use Modules\Report\Exports\NoPaidExport;
use Illuminate\Http\Request;
use App\Models\Tenant\Company;
use App\Models\Tenant\Retention;
use Carbon\Carbon;
use Modules\Report\Http\Resources\QuotationCollection;
use Modules\Dashboard\Helpers\DashboardView;
use Modules\Finance\Traits\UnpaidTrait;

class ReportRetentionController extends Controller
{

    use UnpaidTrait;

    public function excel(Request $request)
    {

        $retention = Retention::where($request->column, 'like', "%{$request->value}%")
        ->latest();

        $records = new RetentionCollection($retention);

        $company = Company::get();

        $retentionExport = new RetentionsExport();

        $retentionExport
            ->company($company)
            ->records($records);

        return $retentionExport->download('Retenciones_' . Carbon::now()->timestamp . '.xlsx');
    }
}
