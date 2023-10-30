<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddRetentionsPaymentToPaymentMethodTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('tenant')->table('payment_method_types')->insert([
            ['id'=>'14','description'=>'Anticipo Cliente','has_card' => 0, 'is_credit'=> 0,'is_cash'=>0,'is_advance'=>1,'pago_sri'=>'01'],
            ['id'=>'15','description'=>'Anticipo Proveedor','has_card' => 0, 'is_credit'=> 0,'is_cash'=>0,'is_advance'=>1,'pago_sri'=>'01'],
            ['id'=>'99','description'=>'Canje retenciones','has_card'=>0,'is_credit'=> 0,'is_cash'=>1,'is_advance'=>1,'pago_sri'=>'01']]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_method_types', function (Blueprint $table) {
            //
        });
    }
}
