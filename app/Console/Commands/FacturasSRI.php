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


class FacturasSRI extends Command
{
    //use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sri:see';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'CONSULTA LAS FACTURAS YA ENVIADAS AL SRI ECUADOR';

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
            /*if ($this->isOffline()) {
                $this->info('Offline service is enabled');
                return;
            }*/

            $documents = Document::query()
                ->whereIn('document_type_id', ['01','07'])
                ->whereIn('state_type_id', ['07'])
                //->where('success_sunat_shipping_status', true)
                ->get();

            foreach ($documents as $document) {
                try {
                    //$this->info('CONSULTANDO: '.$document->clave_SRI);
                    $response = new TenantDocumentController();
                    $res = $response->validarSRI($document->id);
                    $document->sunat_shipping_status = json_encode($res);
                    $document->success_sunat_shipping_status = true;
                    $document->save();
                    $this->info('DOCUMENTO VALIDADO: '.$document->clave_SRI);
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

        $this->info('SRI:SEE A TERMINADO');

    }
}
