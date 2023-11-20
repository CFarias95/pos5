<?php

use App\Models\System\ModuleLevel;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubmoduleToAdvanceModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $q2 = new ModuleLevel([
            'value'       => 'credit_note_payments',
            'description' => 'Notas de crédito como pago',
        ]);
        $q2->setModuleId(3)->push();

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
