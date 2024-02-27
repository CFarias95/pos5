<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddColumnasPdfProduction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('production', function (Blueprint $table) {
            $table->string('num_personas')->nullable();
            $table->string('presentacion')->nullable();
            $table->string('muestra1')->nullable();
            $table->string('muestra2')->nullable();
            $table->string('muestra3')->nullable();
            $table->string('muestra4')->nullable();
            $table->string('muestra5')->nullable();
            $table->string('ph')->nullable();
            $table->string('color')->nullable();
            $table->string('olor')->nullable();
            $table->string('sabor')->nullable();
            $table->string('solubilidad')->nullable();
            $table->boolean('revision')->default(0);
            $table->string('enviado')->nullable();
            $table->boolean('verificacion_nombre')->default(0);
            $table->boolean('verificacion_date_issue')->default(0);
            $table->boolean('verificacion_date_end')->default(0);
            $table->text('observaciones2')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('production', function (Blueprint $table) {
            $table->dropColumn([
                'num_personas', 'presentacion', 'muestra1', 'muestra2', 'muestra3',
                'muestra4', 'muestra5', 'ph', 'color', 'olor', 'sabor', 'solubilidad',
                'revision', 'verificacion_nombre', 'verificacion_date_issue',
                'verificacion_date_end', 'observaciones2', 'enviado'
            ]);
        });
    }
}
