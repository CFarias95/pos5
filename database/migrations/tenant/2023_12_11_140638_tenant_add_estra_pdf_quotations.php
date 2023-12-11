<?php

use Doctrine\DBAL\Schema\Schema as SchemaSchema;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddEstraPdfQuotations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('tenant')->table('quotations', function ($table) {
            $table->string('upload_filename')->nullable();
            $table->boolean('send_upload_pdf')->nullable()->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tenant')->table('quotations', function ($table) {
            $table->dropColumn('upload_filename');
            $table->dropColumn('send_upload_pdf');
        });

    }
}
