<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCountableAcountToPaymentMethodTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_method_types', function (Blueprint $table) {
            $table->string('countable_acount', 100)->nullable();
            $table->boolean('is_advance')->nullable()->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_method_types', function (Blueprint $table) {
            $table->dropColumn('countable_acount');
            $table->dropColumn('is_advance');
        });
    }
}
