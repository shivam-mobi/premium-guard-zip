<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StatisticsCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */

    public function __construct()
    {
        parent::__construct();
    }
    
    public function handle()
    {
        return 0;
    }
}
