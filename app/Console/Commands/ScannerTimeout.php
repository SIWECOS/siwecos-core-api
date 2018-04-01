<?php

namespace App\Console\Commands;

use App\Scan;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ScannerTimeout extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siwecos:timeout';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for timeout';

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
        $notFinishedScans = Scan::whereCreatedAt('<', Carbon::now()->addMinutes(-5))->all();
        /** @var Scan $pendingScan */
        foreach ($notFinishedScans as $pendingScan){
            $pendingScan->status = 3;
            $pendingScan->save();
        }
    }
}
