<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
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
}
