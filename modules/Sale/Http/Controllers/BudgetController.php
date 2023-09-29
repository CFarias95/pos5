<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Sale\Http\Resources\UserCommissionCollection;
use Modules\Sale\Http\Resources\UserCommissionResource;
use Illuminate\Support\Facades\DB;
use Modules\Sale\Http\Requests\UserCommissionRequest;
use Exception;
use Modules\Sale\Models\UserCommission;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\Auth;
use Modules\Sale\Http\Resources\BudgetCollection;
use Modules\Sale\Models\Budget;

class BudgetController extends Controller
{

    public function index()
    {
        //return view('sale::user-commissions.index');
    }


    public function columns()
    {
        return [
            'id' => 'Número',
        ];
    }


    public function records($user_id)
    {
        $records = Budget::where('user_id', $user_id);
        return new BudgetCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function tables()
    {

        $users = User::get(['id', 'name']);

        return compact('users');
    }

    public function record($id)
    {
        $record = new UserCommissionResource(UserCommission::findOrFail($id));

        return $record;
    }

    public function store(Request $request)
    {

        $id = $request->input('id');
        $budget = Budget::firstOrNew(['id' => $id]);
        $budget->fill($request->all());
        $budget->user = Auth::user()->id;
        $budget->save();

        return [
            'success' => true,
            'message' => ($id) ? 'Presupuesto editado con éxito' : 'Presupuesto registrado con éxito'
        ];
    }



    public function destroy($id)
    {

        $record = Budget::findOrFail($id);
        $record->delete();

        return [
            'success' => true,
            'message' => 'Presupuesto eliminado con éxito'
        ];
    }
}
