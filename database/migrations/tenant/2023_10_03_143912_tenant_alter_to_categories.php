<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAlterToCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->integer('parent_2_id')->unsigned()->nullable();
            $table->integer('parent_3_id')->unsigned()->nullable();

            $table->foreign('parent_2_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('parent_3_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('parent_2_id');
            $table->dropColumn('parent_3_id');
        });
    }
}
