<?php

use App\Models\System\ModuleLevel;
use App\Models\Tenant\Module;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\LevelAccess\Models\ModuleLevel as ModelsModuleLevel;

class TenantAddperimiosnInternalRequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('tenant')->table('module_levels')->insert([
            'value'       => 'internal_request',
            'description' => 'Pedidos internos',
            'module_id' => 3,
        ]);
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
