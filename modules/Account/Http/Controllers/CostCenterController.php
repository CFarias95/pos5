<?php

namespace Modules\Account\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Account\Models\CostCenter;

class CostCenterController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('account::cost_centers.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        //return view('account::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        try {
            $id = $request['id'];
            $data = CostCenter::findOrNew($id);
            $data->fill($request->all());
            $data->save();

            return [
                'success' => true,
                'message' => ($id) ? 'Centro de costo actualizado correctamente' : 'Centro de costo creado correctamente'
            ];
        } catch (Exception $ex) {
            return [
                'error' => true,
                'success' => false,
                'message' => $ex->getMessage(),
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
        //return view('account::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        //return view('account::edit');
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

    public function records()
    {
        $cost_centers = CostCenter::where('level_1', null)->paginate(config('tenant.items_per_page'));
        $cost_centers->transform(function ($row) {
            $level1 = CostCenter::where('level_1', $row->id)->get();
            return [
                'id' => $row->id,
                'name' => $row->name,
                'date' => $row->created_at->format("Y-m-d"),
                'isActive' => true,
                'children' => $level1->transform(function ($level) {
                    $level2 = CostCenter::where('level_2', $level->id)->get();
                    return [
                        'id' => $level->id,
                        'name' => $level->name,
                        'date' => $level->created_at->format("Y-m-d"),
                        'isActive' => true,
                        'children' => $level2->transform(function ($data) {
                            return [
                                'id' => $data->id,
                                'name' => $data->name,
                                'date' => $data->created_at->format("Y-m-d"),
                            ];
                        })

                    ];
                })
            ];
        });

        //$cost_centers = CostCenter::select('id','name','created_at','level_1','level_2')->groupBy('id','level_1','level_2')->paginate(config('tenant.items_per_page'));
        return $cost_centers;
    }

    public function columns()
    {

        return;
    }

    public function levelRecords($tipo, $id)
    {
        $levels = null;

        if ($tipo == "1") {
            $levels = CostCenter::where('level_1', null)->where('level_2', null)->get()->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'name' => $row->name
                ];
            });
        }
        if ($tipo == "2") {
            $levels = CostCenter::where('level_1', $id)->get()->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'name' => $row->name
                ];
            });
        }
        return compact("levels");
    }
}
