<?php

namespace App\Console\Commands;

use App\Token;
use Illuminate\Console\Command;

class CreateMasterToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:mastertoken';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        //
        $this->info("create Mastertoken");
        $masterToken = new Token(['credits'=>0]);
        $masterToken->setAclLevel(9999);
        $masterToken->save();
        $this->info('Please save the following token, it is required for any token operation');
        $this->info('mastertoken: ' . $masterToken->token);
    }
}
