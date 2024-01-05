<?php

namespace Modules\Inventory\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;

class ReportInventoryExport implements  FromView, ShouldAutoSize
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

    public function filters($filters) {
        $this->filters = $filters;

        return $this;
    }

    public function usuario_log($usuario_log) {
        $this->usuario_log = $usuario_log;

        return $this;
    }

    public function fechaActual($fechaActual) {
        $this->fechaActual = $fechaActual;

        return $this;
    }

    public function view(): View {
        return view('inventory::inventory.inventario_excel', [
            'records'=> $this->records,
            'company' => $this->company,
            'fechaActual'=>$this->fechaActual,
            'usuario_log'=>$this->usuario_log,
        ]);
    }
}
