<?php

namespace App\Console\Commands;

use App\Http\Controllers\Tenant\DispatchesSriController;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Dispatch;
use Illuminate\Console\Command;

class DispatchSEE extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dispatch:see';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validar las retenciones recibidas por el SRI';

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

            $documents = null;
            if(Configuration::firstOrFail()->send_auto){
                $documents = Dispatch::query()
                ->whereIn('state_type_id', ['07','30'])
                ->get();
            }else{
                $documents = Dispatch::query()
                ->whereIn('state_type_id', ['07','30'])
                ->where('is_aproved',1)
                ->get();
            }

            foreach ($documents as $document) {

                try {

                    $response = new DispatchesSriController();
                    $response->validateDocumentSRI($document->id,$document->clave_SRI);

                    $this->info('Validado: '.$document->clave_SRI);
                }
                catch (\Exception $e) {

                    $this->info('Error al tratar de validar: '.$e->getMessage());

                }
            }
        }
        else {
            $this->info('The crontab is disabled');
        }

    }
}
