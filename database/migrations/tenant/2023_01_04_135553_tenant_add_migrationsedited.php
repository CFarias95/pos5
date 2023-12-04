<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantAddMigrationsedited extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('tenant')->table('migrations')->insert([
            ['migration'=>'2023_10_04_112423_tenant_add_category_id_array_to_items','batch'=>112],
            ['migration'=>'2023_11_21_094640_tenant_add_validity_to_items','batch'=>112],
            ['migration'=>'2023_11_23_155154_tenant_add_flete_fob_to_import','batch'=>112],
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
