<?php

namespace Modules\Inventory\Http\Controllers;

use App\Models\Tenant\AccountMovement;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        DB::connection('tenant')->transaction(function () use ($request) {

            try{
                if($request->id){
                    Log::info("se edita: ");
                    $data = InventoryTransaction::where('id',$request->id);
                    $data->update([
                        'type'=>$request->type,
                        'name' =>$request->name,
                        'cta_account' =>$request->cta_account,
                    ]);

                }else{
                    Log::info("se crea uno nuevo: ");
                    $data = new InventoryTransaction();
                    $data->id = $this->validarID();
                    $data->type = $request->type;
                    $data->name = $request->name;
                    $data->cta_account = $request->cta_account;
                    $data->save();

                }

            }catch(Exception $ex){
                return [
                    'success' => false,
                    'message' => $ex->getMessage(),
                ];
            }
        });

        return [
            'success' => true,
            'message' => ($request->id)?'Se actualizo el registro':'Se creo un nuevo registro',
        ];
    }

    //esta funcion crea un nuevo ID en base al ultimo registrado
    public function validarID(){
        try{
            $lastID = DB::connection('tenant')->select('SELECT CAST(id AS SIGNED) AS id FROM inventory_transactions ORDER BY CAST(id AS SIGNED) DESC LIMIT 1');
            Log::info('ID nuevo trasanction: '.$lastID[0]->id);
            return ($lastID[0]->id + 1);
        }
        catch(\Throwable $th){
            throw $th;
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
            $records = InventoryTransaction::where($request->column, 'like', "%{$request->value}%")->where('visible', true);
        }else{
            $records = InventoryTransaction::where('visible', true);
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
