<?php

use App\Models\System\ModuleLevel;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddModulesToAccountant extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $q2 = new ModuleLevel([
            'value'       => 'accounting_audit',
            'description' => 'Auditoria de contabilidad',
        ]);
        $q2->setModuleId(9)->push();

        $q3 = new ModuleLevel([
            'value'       => 'accounting_reconciliation',
            'description' => 'Punteo contable',
        ]);
        $q3->setModuleId(9)->push();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
