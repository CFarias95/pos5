<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantModifySriFormasPagoDefault extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('tenant')->table('payment_method_types')->insert([
            ['id' => '11', 'description' => 'Crédito 30 días', 'has_card' => false, 'number_days' => 30, 'charge' => null, 'pago_sri' => '01', 'is_credit' => true],
            ['id' => '12', 'description' => 'Crédito 60 días', 'has_card' => false, 'number_days' => 60, 'charge' => null, 'pago_sri' => '01', 'is_credit' => true],
            ['id' => '13', 'description' => 'Cheque', 'has_card' => false, 'number_days' => null, 'charge' => null, 'pago_sri' => '20', 'is_credit' => false],

        ]);
        DB::connection('tenant')->table('payment_method_types')->where('description', 'Efectivo')->update(['pago_sri' => '01']);
        DB::connection('tenant')->table('payment_method_types')->where('description', 'Tarjeta de crédito')->update(['pago_sri' => '19']);
        DB::connection('tenant')->table('payment_method_types')->where('description', 'Tarjeta de débito')->update(['pago_sri' => '16']);
        DB::connection('tenant')->table('payment_method_types')->where('description', 'Transferencia')->update(['pago_sri' => '20']);
        DB::connection('tenant')->table('payment_method_types')->where('description', 'Cheque')->update(['is_cash' => 1]);
        /* DB::connection('tenant')->table('payment_method_types')->where('description', 'Factura a 30 días')->delete();
        DB::connection('tenant')->table('payment_method_types')->where('description', 'Tarjeta crédito visa')->delete();
        DB::connection('tenant')->table('payment_method_types')->where('description', 'Contado contraentrega')->delete();
        DB::connection('tenant')->table('payment_method_types')->where('description', 'A 30 días')->delete();
        DB::connection('tenant')->table('payment_method_types')->where('description', 'Crédito')->delete();
        DB::connection('tenant')->table('payment_method_types')->where('description', 'Contado')->delete(); */
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
