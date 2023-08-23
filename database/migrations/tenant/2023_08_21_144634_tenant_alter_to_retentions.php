<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAlterToRetentions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('retentions', function (Blueprint $table) {
            $table->json('supplier')->nullable()->change();
            $table->json('establishment')->nullable()->change();
            $table->string('document_type_id')->nullable()->change();
            $table->boolean('in_use')->default(false);
            $table->decimal('total_used', 10, 2)->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('retentions', function (Blueprint $table) {
            $table->dropColumn('in_use');
            $table->dropColumn('total_used');
        });
    }
}
