<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInternalRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('internal_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned(); // user id of requester
            $table->integer('user_manage')->unsigned();
            $table->string('title', 266);
            $table->text('description');
            $table->enum('status', ['Created', 'Acepted','Rejected'])->nullable()->default('Created');
            $table->string('phase', 266)->nullable();
            $table->boolean('confirmed')->nullable()->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_manage')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('internal_requests');
    }
}
