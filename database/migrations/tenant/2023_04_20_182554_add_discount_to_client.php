<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiscountToClient extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('person_types', function (Blueprint $table) {
            //
            $table->decimal('discount', 12, 2)
                ->default(0)
                ->after('description')
                ->comment('establece el porcentaje del descuento');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('person_types', function (Blueprint $table) {
            //
            $table->dropColumn('discount');
        });
    }
}
