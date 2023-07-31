<?php

namespace App\Console\Commands;

use App\Http\Controllers\Tenant\DispatchesSriController;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Dispatch;
use Illuminate\Console\Command;

class DispatchXML extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dispatch:xml';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera y forma los XML de guias de remision';

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
        $configurations = Configuration::firstOrFail();
        if ($configurations->cron) {

            $documents = Dispatch::query()
                        ->where('state_type_id','01')
                        ->get();

            foreach ($documents as $document) {
                try {

                    $response = new DispatchesSriController();
                    $result = $response->createXML($document->id);
                    $this->info($result);
                }
                catch (\Exception $e) {

                    $this->info('error : '.$e->getMessage());

                }
            }
        }
        else {
            $this->info('The crontab is disabled');
        }
    }
}
