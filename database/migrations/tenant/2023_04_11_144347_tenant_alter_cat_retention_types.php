<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantAlterCatRetentionTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        schema::table('cat_retention_types',function(Blueprint $table){
            $table->integer('code2')->unsigned()->nullable();
        });

        DB::connection('tenant')->table('cat_retention_types')->insert(['id'=>'24','active'=>1,'percentage'=>50,'description'=>'Retencion en IVA 50%','code'=>'11','type_id'=>'02','code2'=>'11']);
        DB::connection('tenant')->table('cat_retention_types')->where('type_id','02')->where('percentage',10)->update(['code2'=>'9']);
        DB::connection('tenant')->table('cat_retention_types')->where('type_id','02')->where('percentage',20)->update(['code2'=>'10']);
        DB::connection('tenant')->table('cat_retention_types')->where('type_id','02')->where('percentage',30)->update(['code2'=>'1']);
        DB::connection('tenant')->table('cat_retention_types')->where('type_id','02')->where('percentage',70)->update(['code2'=>'2']);
        DB::connection('tenant')->table('cat_retention_types')->where('type_id','02')->where('percentage',100)->update(['code2'=>'3']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        schema::table('cat_retention_types',function(Blueprint $table){
            $table->dropColumn('code2');
        });
    }
}
