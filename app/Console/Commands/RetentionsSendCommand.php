<?php

namespace App\Console\Commands;

use App\Http\Controllers\Tenant\RetentionsControllers;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\RetentionsEC;
use Illuminate\Console\Command;

class RetentionsSendCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'retentions:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Este comando envia al SRI las retenciones de las cuales ya se genero el XML y se firmo';

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

            $documents = RetentionsEC::whereNotNull('claveAcceso')
                ->whereIn('status_id',['02'])
                ->get();

            $this->info('ENVIANDO DOCUMENTOS AL SRI');
            foreach ($documents as $document) {
                try {

                    $response = new RetentionsControllers();
                    $result = $response->sendXmlSigned($document->id,$document->claveAcceso.'.xml');

                }
                catch (\Exception $e) {

                    $this->info('error : '.$e->getMessage());

                }
            }
        }
        else {
            $this->info('ESTE COMANDO ESTA DESHABILITADO');
        }
    }
}
