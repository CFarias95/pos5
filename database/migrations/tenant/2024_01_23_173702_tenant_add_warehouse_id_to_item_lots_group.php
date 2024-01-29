<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddWarehouseIdToItemLotsGroup extends Migration

{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_lots_group', function (Blueprint $table) {
            $table->unsignedInteger('warehouse_id')->nullable()->after('item_id');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            //
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_lots_group', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
            //
        });
    }
}
