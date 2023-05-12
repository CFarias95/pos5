<?php

namespace App\Console\Commands;

use App\Http\Controllers\Tenant\RetentionsControllers;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\RetentionsEC;
use Illuminate\Console\Command;

class RetenctionsAuthCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'retentions:see';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ESTE COMANDO VALIDA LAS RETENCIONES ENVIADAS AL sri';

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
                ->whereIn('status_id',['03','04'])
                ->get();

            $this->info('VALIDANDO RETENCIONES EN EL SRI');
            foreach ($documents as $document) {
                try {

                    $response = new RetentionsControllers();
                    $result = $response->validateDocumentSRI($document->id,$document->claveAcceso);

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
