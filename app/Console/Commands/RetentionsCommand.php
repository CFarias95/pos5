<?php

namespace App\Console\Commands;

use App\Http\Controllers\Tenant\RetentionsControllers;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Purchase;
use Illuminate\Console\Command;

class RetentionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'retentions:xml';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera los XML de las retenciones y los firma';

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

            $documents = Purchase::query()
                ->where('is_aproved', 1)
                ->get();

            foreach ($documents as $document) {
                try {

                    $response = new RetentionsControllers();
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
