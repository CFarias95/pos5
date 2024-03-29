<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->decimal('percentage', 8, 4)->nullable();
            $table->boolean('unaffected')->nullable()->default(false);
            $table->string('code', 4)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cat_affectation_igv_types', function (Blueprint $table) {
            $table->dropColumn('percentage');
            $table->dropColumn('unaffected');
            $table->dropColumn('code');
        });
    }
}
