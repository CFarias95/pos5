<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantAddFieltToCatIdentityDocumentTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cat_identity_document_types', function (Blueprint $table) {
            $table->char('codeSri', 4)->nullable();
        });

        DB::connection('tenant')->table('cat_identity_document_types')->where('id','=','1')->update(['codeSri'=>'05']);
        DB::connection('tenant')->table('cat_identity_document_types')->where('id','=','6')->update(['codeSri'=>'04']);
        DB::connection('tenant')->table('cat_identity_document_types')->where('id','=','7')->update(['codeSri'=>'06']);
        DB::connection('tenant')->table('cat_identity_document_types')->where('id','=','0')->update(['codeSri'=>'07']);
        DB::connection('tenant')->table('cat_identity_document_types')->where('id','=','4')->update(['codeSri'=>'08']);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cat_identity_document_types', function (Blueprint $table) {
            $table->dropColumn('codeSri');
        });
    }
}
