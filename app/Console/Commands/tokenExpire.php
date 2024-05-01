<?php

namespace App\Console\Commands;

use App\Models\Oauth_access_token;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class tokenExpire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'token:expire';
    // protected $signature = 'schedule:work';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This wil expire token';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $currentTime = Carbon::now('Asia/Karachi');
        // $yesterdayTime = $currentTime->subDay();

        // // Oauth_access_token::where('created_at', '=', $yesterdayTime)->update(['das' => '1']);

        // $token = Oauth_access_token::where('created_at', '=', $yesterdayTime)->first();


        // if ($token) {
        //     $token->delete();
        //     Log::info($token);
        // }


        DB::table('oauth_access_tokens')->delete();
    }
}
