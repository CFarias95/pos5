<?php

          namespace App\Models;
        namespace App\Models\Tenant;


    use App\Models\Tenant\Catalogs\CurrencyType;
    use Auth;
    use Carbon\Carbon;
    use Illuminate\Config\Repository;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Foundation\Application;
    use Illuminate\Support\Facades\Config;
    use Modules\Inventory\Models\Warehouse;
    use Modules\LevelAccess\Models\ModuleLevel;
    use App\Models\Tenant\Skin;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;

class ConfigurationCash extends Model
{

    protected $table = 'cat_currency_types';
}