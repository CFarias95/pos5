<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\InternalRequestCollection;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\InternalRequest;
use Illuminate\Http\Request;

class InternalRequestController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        $configuration = Configuration::getPublicConfig();

        return view('tenant.internal_requests.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Model\Tenant\InternalRequest  $internalRequest
     * @return \Illuminate\Http\Response
     */
    public function show(InternalRequest $internalRequest)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Model\Tenant\InternalRequest  $internalRequest
     * @return \Illuminate\Http\Response
     */
    public function edit(InternalRequest $internalRequest)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Model\Tenant\InternalRequest  $internalRequest
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, InternalRequest $internalRequest)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Model\Tenant\InternalRequest  $internalRequest
     * @return \Illuminate\Http\Response
     */
    public function destroy(InternalRequest $internalRequest)
    {
        //
    }

    //RECORDS
    public function records(Request $request){

        $records = $this->getRecords($request);

        return new InternalRequestCollection($records->paginate(config('tenant.items_per_page')));

    }

    public function getRecords($request)
    {

        $manager = $request->manager;

        $estado = $request->estado;

        $fechaInicio = $request->fechaInicio;

        $fechaFin = $request->fechaFin;

        $records = InternalRequest::query();

        if ($manager) {

            $records->where('user_manage', $manager);

        }
        if ($fechaInicio && $fechaFin) {

            $records->whereBetween('created_at', [$fechaInicio,$fechaFin."23:59:59"]);
        }

        if ($estado) {

            $records->where('status', 'like', '%' . $estado . '%');
        }

        return $records;
    }

}
