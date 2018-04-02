<?php

namespace App\Console\Commands;

use App\Token;
use Illuminate\Console\Command;
use Log;

class RestockCredits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siwecos:restock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restock credits to 50';

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
        /** @var Token[] $allCreditsBelow */
        $allCreditsBelow = Token::whereCredits('<', 50);

        foreach ($allCreditsBelow as $token){
            Log::info('Restock credits for token: ' . $token->token);
            $token->credits = 50;
            $token->save();
        }
    }
}
