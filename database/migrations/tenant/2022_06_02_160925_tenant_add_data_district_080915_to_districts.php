<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\CoreFacturalo\Helpers\Functions\SetDataHelper;


class TenantAddDataDistrict080915ToDistricts extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //SetDataHelper::createDistrict('080915', 'Kumpirushiato');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SetDataHelper::deleteDistrict('080915');
    }

}
