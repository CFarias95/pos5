<?php

namespace Modules\Inventory\Http\Controllers;

use App\Models\Tenant\AccountMovement;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Inventory\Http\Resources\InventoryTransactionsCollection as ResourcesInventoryTransactionsCollection;
use Modules\Inventory\Models\InventoryTransaction;
use Modules\Inventory\Resources\InventoryTransactionsCollection;

class InventoryTransactionsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('inventory::transactions.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('inventory::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        try{
            if($request->id){
                $data = InventoryTransaction::find($request->id);
                $data->fill($request);
                $data->save();
            }else{

                $data = InventoryTransaction::created($request);

            }
            return[

                'message' => ($request->id)?'Se actualizo el registro':'Se creo un nuevo registro',
            ];

        }catch(Exception $ex){

            return[
                'message' =>$ex->getMessage(),
            ];
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('inventory::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('inventory::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    //RETORNA LOS REGISTROS DE LAS TRASACCIONES CREADAS
    public function records(Request $request){

        if(isset($request->column)){
            $records = InventoryTransaction::where($request->column, 'like', "%{$request->value}%");
        }else{
            $records = InventoryTransaction::query();
        }

        return new ResourcesInventoryTransactionsCollection($records->paginate(config('tenant.items_per_page')));
    }

    //RECUPERAR LA DATA DE UN REGISTRO ESPECIFICO
    public function record($id){

        $record = InventoryTransaction::find($id);
        return $record;
    }

    //retornamos data para crear formulario
    public function tables(){
        $accounts = AccountMovement::all();

        return compact(['accounts']);
    }
}
