<?php

use Doctrine\DBAL\Schema\Schema as SchemaSchema;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddPermisionUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users',function(Blueprint $table){
            $table->boolean('documents_change_item_warehouse')->nullable()->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users',function(Blueprint $table){
            $table->dropColumn('documents_change_item_warehouse');
        });
    }
}
