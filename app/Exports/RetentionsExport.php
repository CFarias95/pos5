<?php

namespace App\Exports;

use App\Http\Resources\Tenant\RetentionCollection;
use App\Models\Tenant\Retention;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;

class RetentionsExport implements FromView
{
    /**
     * @return \Illuminate\Support\Collection
     */

    use Exportable;

    public function records($records)
    {
        $this->records = $records;
        return $this;
    }

    public function company($company)
    {
        $this->company = $company;

        return $this;
    }

    public function view(): View
    {
        return view('tenant.reports.retentions.report_excel', [
            'records' => $this->records,
            'company' => $this->company,
        ]);
    }
}
