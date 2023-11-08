<?php

namespace Modules\Report\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;

class PurchaseStatementExport implements  FromView, ShouldAutoSize
{
    use Exportable;

    public function records($records) {
        $this->records = $records;

        return $this;
    }

    public function company($company) {
        $this->company = $company;

        return $this;
    }

    public function establishment($establishment) {
        $this->establishment = $establishment;

        return $this;
    }

    public function filters($filters) {
        $this->filters = $filters;

        return $this;
    }

    public function title($title) {
        $this->title = $title;

        return $this;
    }

    public function view(): View {
        return view('report::purchase_statement.report_excel', [
            'records'=> $this->records,
            'company' => $this->company,
            'title' => $this->title,
            'establishment'=>$this->establishment,
            'filters'=>$this->filters
        ]);
    }
}
