<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCreditNotesPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_notes_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('document_id')->unsigned()->nullable();
            $table->integer('purchase_id')->unsigned()->nullable();
            $table->double('amount', 30, 8);
            $table->integer('user_id')->unsigned();
            $table->boolean('in_use')->default(false);
            $table->double('used', 30, 8)->nullable()->default(0);

            $table->foreign('user_id')->references('id')->on('persons')->onDelete('cascade');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credit_notes_payments');
    }
}
