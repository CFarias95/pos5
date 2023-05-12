<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\RateRequest;
use Exception;
use Illuminate\Http\Request;
use App\Http\Resources\Tenant\RatesCollection;
use App\Models\Tenant\Rate;

class RatesController extends Controller
{
    public function index()
    {

        return view('tenant.rates.index');

    }
    public function columns()
    {
        return [
            'rate_name' => 'Nombre Tarifa',
        ];
    }

    public function records(Request $request)
    {
        $records = Rate::where($request->column, 'like', "%{$request->value}%")
                            ->orderby('id');
        return new RatesCollection ($records->paginate(config('tenant.items_per_page')));
    }


    public function record($id)
    {
        $record = Rate::findOrFail($id);

        return $record;
    }

    public function store(RateRequest $request)
    {
        $id = $request->input('id');
        $person_type = Rate::firstOrNew(['id' => $id]);
        $person_type->fill($request->all());
        $person_type->save();


        return [
            'success' => true,
            'message' => ($id)?'Tarifa editada con éxito':'Tarifa registrada con éxito',
        ];
    }

    public function destroy($id)
    {
        try {

            $rate = Rate::findOrFail($id);
            $rate_type = 'Tarifa';
            $rate->delete();

            return [
                'success' => true,
                'message' => $rate_type.' eliminada con éxito'
            ];

        } catch (Exception $e) {

            return ($e->getCode() == '23000') ? ['success' => false,'message' => "El {$rate_type} esta siendo usado por otros registros, no puede eliminar"] : ['success' => false,'message' => "Error inesperado, no se pudo eliminar la {$rate_type}"];

        }

    }


}
