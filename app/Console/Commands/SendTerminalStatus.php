<?php

namespace App\Console\Commands;
use App\Http\Controllers\Fba\FbaNotificationController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class SendTerminalStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SendTerminalStatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gửi trạng thái thiết bị tự động';

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
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $current_date = date('Y-m-d');
        Log::useFiles(base_path() . '/dailyLog/[SERVICE]SendTerminalStatus.log', 'info');
        try {
            $arr= ['headers' => ['Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6Ijc2ZTE3MDQ0ZDc2NjdjNjRiYjhhNmUyNDA4NTg5NDdkMWE5MmJlM2RlNjEyYjNhOTc0NDM5YjVlZGYzYWNjYmJkOWUwNmEwODU3YzFkOTM1In0.eyJhdWQiOiIxIiwianRpIjoiNzZlMTcwNDRkNzY2N2M2NGJiOGE2ZTI0MDg1ODk0N2QxYTkyYmUzZGU2MTJiM2E5NzQ0MzliNWVkZjNhY2NiYmQ5ZTA2YTA4NTdjMWQ5MzUiLCJpYXQiOjE1NDE3NzUyMzMsIm5iZiI6MTU0MTc3NTIzMywiZXhwIjoxNTczMzExMjMzLCJzdWIiOiIxIiwic2NvcGVzIjpbIioiXX0.lEKMisFxlBtWtILMN541dMRmDgTjdlkUNhmeKR38aCNfrbGqjCkwpBN84TJjEXFzA0dP_jKmhwPs2f-tnKDf_ceux3M71hgyz1ITTjxbQ7aVxtH-rN6cXwkpNERyr8RPqCfFTeHs4hEzREYVC1qDUpqlW7X8uNcAiaprhy_DuuACSkmvSSIYS4Txka1n0bYcNR2xy4T9Rc7wAlrqpDDGWYIqwp12Sy0_s0PYcp_CxnvPkYcVzLnyensg7tPwE6QX64EPAo6Tp24H_YtAn2bRQ7lTRrGkBMdF7CBO9sz9HGHskxv-MfnXkxSTccj_4wgabcWyHT0tj6BY77kW4qoAhesDlLfPuKLN3rqDFmU4fke_8eNZ52tYkO4_3PbJ1UJcB9Ic4u89mTfeKUKb3hegsxWUwt7JlWqI-OrLPggnSoZJshkI6NBq-eVPKcAiknF33uawFUTX-lSMjwwqwVFUKIIFSVET35ZKTkEAZblSfWbbzModaCc2feoYT8D4RBVy2pFRO6eDBbLVT6vpYEseyLKTRbE-sObVapnz0kfl6ufICvWSlw9fxpKH7tkWnJC8-4lyOPpbsX5zWb3sFoOFB8j33SwU-o5mmqbqnC6n9CN3gGbQdD8liy7BuhdOi9-QoiA0H8OePkche078hzb8C9kCxDbyXqOFYzr9doRHIOg']];
            $client = new \GuzzleHttp\Client(['base_uri' => env('URL_EMAIL')]);
            // Send a request to https://foo.com/api/test
            $response = $client->request('POST', 'api/send_terminal_status',$arr);
            $responseObject = json_decode($response->getBody()->getContents());
            if($responseObject->status) {
                Log::info('Send mail success');
            } else {
                Log::info('Send mail error');
                Log::info($responseObject->errMsg);
            }
            return true;
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return false;
        }
        
    }
}
