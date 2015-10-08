<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Extracts;
use App\Contracts\ToolsClient;

class ExtractTools extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warehouse:extract-tools {--client=} {--table=} {--pk=} {--file-prefix=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extract a tools client table for the warehouse.';

    /**
     * @var Extracts\Tools
     */
    private $extractor;

    /**
     * @var ToolsClient\Service
     */
    private $client_service;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Extracts\Tools $extractor, ToolsClient\Service $client_service)
    {
        parent::__construct();
        $this->extractor = $extractor;
        $this->client_service = $client_service;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $client = $this->option('client');
        if (empty($client)) {
            $this->error('No client specified!');
            exit(1);
        }

        $table = $this->option('table');
        if (empty($table)) {
            $this->error('No table specified!');
            exit(1);
        }

        $pk = $this->option('pk');
        if (empty($pk)) {
            $pk = $table . '_id';
        }

        $prefix = $this->option('file-prefix');
        if (empty($prefix)) {
            $prefix = $client . '-' . $table . '-' . date('YmdHis');
        }

        $this->extractor->configure($client, $table, $pk, $prefix);

        // get and add client ID as fixture data
        $client_id = $this->client_service->getClientId($client);
        $this->extractor->addFixtureData('client_id', $client_id);

        if (!$this->option('quiet')) {
            $progress_bar = $this->output->createProgressBar(100);
            $this->extractor->setProgressBar($progress_bar);
        }

        $this->info('made it this far');



        // extract


    }
}
