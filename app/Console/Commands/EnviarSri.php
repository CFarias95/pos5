<?php

namespace App\Console\Commands;

use App\Http\Controllers\Tenant\DocumentController as TenantDocumentController;
use Facades\App\Http\Controllers\Tenant\DocumentController;

use Illuminate\Console\Command;
use App\Traits\CommandTrait;
use App\Models\Tenant\{
    Configuration,
    Document
};

class EnviarSri extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sri:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ENVIA LOS DOCUMENTOS AL SRI ECUADOR';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (Configuration::firstOrFail()->cron) {
            if ($this->isOffline()) {
                $this->info('Offline service is enabled');
                return;
            }
            $documents = null;
            if(Configuration::firstOrFail()->send_auto){
                $documents = Document::query()
                ->whereIn('document_type_id',['01','07'])
                ->whereIn('state_type_id', ['01'])
                ->get();
            }else{
                $documents = Document::query()
                ->whereIn('document_type_id',['01','07'])
                ->whereIn('state_type_id', ['01'])
                ->where('aproved',1)
                ->get();
            }

            foreach ($documents as $document) {
                try {
                    //$this->info('CLAVE ACCESO: '.$document->clave_SRI);
                    $response = new TenantDocumentController();
                    $res = $response->send($document->id);
                    $document->sunat_shipping_status = json_encode($res);
                    $document->success_sunat_shipping_status = true;
                    $document->save();
                    $this->info('DOCUMENTO ENVIADO AL SRI: '.$document->clave_SRI);
                }
                catch (\Exception $e) {

                    $document->success_sunat_shipping_status = false;

                    $document->sunat_shipping_status = json_encode([
                        'sucess' => false,
                        'message' => $e->getMessage(),
                        'payload' => $e
                    ]);

                    $document->save();
                }
            }
        }
        else {
            $this->info('The crontab is disabled');
        }

        $this->info('The command is finished');
    }
}
