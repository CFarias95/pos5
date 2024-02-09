<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantCreateCatPurchaseDocumentTypes2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cat_purchase_document_types2', function (Blueprint $table) {
            $table->string('idType')->index();
            $table->boolean('active')->default(true);
            $table->boolean('accountant')->default(false);
            $table->boolean('stock')->default(false);
            $table->boolean('cost')->default(false);
            $table->integer('sign')->default(1);
            $table->string('short')->nullable();
            $table->string('description')->nullable();
            $table->string('DocumentTypeID');
            $table->timestamps();
            $table->foreign('DocumentTypeID')->references('id')->on('cat_purchase_document_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cat_purchase_document_types2');
    }
}
