<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\InternalRequestCollection;
use App\Mail\Tenant\InternalRequestEmail;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\InternalRequest;
use App\Models\Tenant\User;
use Error;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Swift_Mailer;
use Swift_SmtpTransport;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use Illuminate\Support\Facades\Storage;
use Modules\Finance\Helpers\UploadFileHelper;


class InternalRequestController extends Controller
{

    use StorageDocument;

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
    public function create($id)
    {
        if (auth()->user()->type == 'integrator')
            return redirect('/internal-request');

        $configuration = Configuration::first();

        return view('tenant.imports.form', compact('configuration'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{
            $id = $request->input('id');
            $estado = $request->input('estado');
            $internalR = InternalRequest::firstOrNew(['id' => $id]);
            $data = $request->all();
            unset($data['id']);
            $data['user_id'] = auth()->user()->id;
            $internalR->fill($data);
            $internalR->save();


            if($internalR->confirmed){
                $this->email($internalR->id);
            }

            $msg = '';
            $msg = ($id) ? 'Solicitud de pedido interno editada con éxito' : 'Solicitud de pedido interno registrada con éxito';

            return [
                'success' => true,
                'message' => $msg,
                'id' => $internalR->id
            ];
        }catch(Exception $es){
            //throw new Exception($es->getMessage(), 1);
            throw new Error($es->getMessage());
        }
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

    public function updateStatus(Request $request){

        $id = $request->id;
        $status = $request->status;

        $internalR = InternalRequest::find($id);
        $message = "Pedido aceptado";

        if($status == "Rejected"){
            $message = "Pedido rechazado";
        }

        if($status == "Created"){
            $message = "Pedido creado";
        }

        if($internalR){

            $internalR->status = $status;
            $internalR->phase = $message;
            $internalR->save();
            $this->email($id);
            return [
                'success'=>true,
                'message' => 'Se actualizo el estado de pedido interno'
            ];
        }else{
            return [
                'success'=>false,
                'message' => 'Se producjo un error al tratar de actualizar el estado de pedido interno'
            ];
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Model\Tenant\InternalRequest  $internalRequest
     * @return \Illuminate\Http\Response
     */
    public function destroy( $id)
    {

        $internalR = InternalRequest::find($id);
        if($internalR){
            $internalR->delete();
            return [
                'success'=>true,
                'message' => 'Se elimino el pedido interno PI-'.$id
            ];
        }else{
            return [
                'success'=>false,
                'message' => 'No se elimino el pedido interno PI-'.$id
            ];
        }

    }

    //RECORDS
    public function records(Request $request)
    {

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
        $recordsN = InternalRequest::query();

        if ($manager) {

            $records->where('user_manage', $manager);
            $recordsN->where('user_manage', $manager);
        }
        if ($fechaInicio && $fechaFin) {

            $records->whereBetween('created_at', [$fechaInicio, $fechaFin . "23:59:59"]);
            $recordsN->whereBetween('created_at', [$fechaInicio, $fechaFin . "23:59:59"]);
        }

        if ($estado) {

            $records->where('status', 'like', '%' . $estado . '%');
            $recordsN->where('status', 'like', '%' . $estado . '%');
        }

        $tipo = auth()->user()->type;
        if ($tipo == 'admin') {
            return $records;
        } else {
            $id = auth()->user()->id;

            $records->where('user_id', $id);
            $recordsN->where('user_manage', $id)->where('confirmed',1);

            return  $recordsN->union($records);
        }
    }

    public function record($id)
    {
        $data = InternalRequest::findOrFail($id);
        return compact('data');
    }

    public function tables()
    {
        $users = User::all();
        return compact('users');
    }

    public function email($id)
    {

        try{
            $internalRequest = InternalRequest::find($id);
            $user_email = $internalRequest->user->email;
            $manage_email = $internalRequest->manage->email;

            $estado = $internalRequest->status;
            $email = $manage_email;
            $name = $internalRequest->manage->name;
            $content = $internalRequest->description;
            // $this->reloadPDF($quotation, "a4", $quotation->filename);
            if($estado != 'Created'){

                $email = $user_email;
                $name = $internalRequest->user->name;

            }

            $mailable = new InternalRequestEmail($estado,$id,$name,$content);

            Configuration::setConfigSmtpMail();
            $backup = Mail::getSwiftMailer();
            $transport =  new Swift_SmtpTransport(Config::get('mail.host'), Config::get('mail.port'), Config::get('mail.encryption'));
            $transport->setUsername(Config::get('mail.username'));
            $transport->setPassword(Config::get('mail.password'));
            $mailer = new Swift_Mailer($transport);
            Mail::setSwiftMailer($mailer);
            Mail::to($email)->send($mailable);

            return [
                'success' => true
            ];
        }catch(Exception $ex){

            return [
                'success' => true
            ];
            throw new Exception($ex->getMessage(), 1);
        }

    }

    public function uploadAttached(Request $request)
    {

        $validate_upload = UploadFileHelper::validateUploadFile($request, 'file', 'jpg,jpeg,png,gif,svg,pdf', false);

        if (!$validate_upload['success']) {
            return $validate_upload;
        }
        if ($request->hasFile('file')) {
            //if(TechnicalService::where('upload_filename','!=',null)){}
            $new_request = [
                'file' => $request->file('file'),
                'type' => $request->input('type'),
            ];

            return $this->upload_attached($new_request);
        }
        return [
            'success' => false,
            'message' =>  __('app.actions.upload.error'),
        ];
    }

    function upload_attached($request)
    {
        $file = $request['file'];
        $type = $request['type'];
        $temp = tempnam(sys_get_temp_dir(), $type);
        file_put_contents($temp, file_get_contents($file));
        $mime = mime_content_type($temp);
        $data = file_get_contents($temp);

        Storage::disk('tenant')->put('internal_request_attached/pdf/'.$file->getClientOriginalName(),$data);
        return [
            'success' => true,
            'data' => [
                'filename' => $file->getClientOriginalName(),
                'temp_path' => $temp,
                'temp_image' => 'data:' . $mime . ';base64,' . base64_encode($data)
            ]
        ];
    }

    function pdf($id){

        $document = InternalRequest::find($id);

        if (!$document) {
            throw new Exception("El código {$id} es inválido, no se encontro documento relacionado");
        }

        return $this->downloadStorage(str_replace('.pdf','',$document->upload_filename),'pdf', 'internal_request_attached');
    }
}
