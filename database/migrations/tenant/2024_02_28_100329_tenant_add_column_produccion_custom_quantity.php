<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddColumnProduccionCustomQuantity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('production', function (Blueprint $table) {
            $table->decimal('custom_quantity', 10, 6)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('production', function (Blueprint $table) {
            $table->dropColumn('custom_quantity');
        });
    }
}
