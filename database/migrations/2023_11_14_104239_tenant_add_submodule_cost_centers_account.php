<?php

use App\Models\System\ModuleLevel;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddSubmoduleCostCentersAccount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $q2 = new ModuleLevel([
            'value'       => 'cost_centers',
            'description' => 'Centros de Costo',
        ]);
        $q2->setModuleId(9)->push();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
