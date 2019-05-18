<?php

namespace FK3\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;

class sync_old_db extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:old-db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make synchronization from old fk2 db to current DB. This will truncate current all tables and insert all old data into it.';

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
    public function handle(Request $request)
    {
        $syncDb = new \FK3\Controllers\SyncController();
        $syncDb->doSync($request);
        echo 'Sync Complete!' . "\n";
    }
}
