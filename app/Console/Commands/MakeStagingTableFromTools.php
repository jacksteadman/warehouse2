<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Contracts\ToolsClient;
use App\Services\SqlService;
use PDO;

class MakeStagingTableFromTools extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warehouse:make-staging-from-tools {--client=} {--table=} {--pk=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate redshift staging table DDL based on a tools table.';

    /**
     * @var SqlService
     */
    private $sql_service;

    /**
     * @var ToolsClient\Service
     */
    private $client_service;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SqlService $sql_service, ToolsClient\Service $client_service)
    {
        parent::__construct();
        $this->sql_service = $sql_service;
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

        $conn = $this->client_service->getPdoConnection($client);
        $desc_sth = $conn->query('DESC ' . $table);
        $desc = $desc_sth->fetchAll(PDO::FETCH_ASSOC);

        $ddl = $this->sql_service->stagingTableFromToolsTable($table, $pk, $desc);

        $this->info($ddl);

    }
}
