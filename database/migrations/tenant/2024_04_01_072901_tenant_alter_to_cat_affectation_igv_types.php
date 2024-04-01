<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantAlterToCatAffectationIgvTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cat_affectation_igv_types', function (Blueprint $table) {
            //
        });
        DB::connection('tenant')->table('cat_affectation_igv_types')->where("description",'Gravado - IVA 12')->update(["description" => 'Gravado - IVA 15']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cat_affectation_igv_types', function (Blueprint $table) {
            //
        });
    }
}
