<?php

namespace Modules\Account\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;

class ReconciliationExport implements FromView
{
    /**
     * @return \Illuminate\Support\Collection
     */

    use Exportable;
    protected $records;
    protected $company;

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
        return view('tenant.reports.reconciliation.report_excel', [
            'records' => $this->records,
            'company' => $this->company,
        ]);
    }
}
