<?php

namespace App\Console\Commands;
use App\Http\Controllers\Fba\FbaNotificationController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Mail;
class sendnotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sendnotification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gửi notification tự động';

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
        // // Create a client with a base URI
       $arr= ['headers' => ['Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6Ijc2ZTE3MDQ0ZDc2NjdjNjRiYjhhNmUyNDA4NTg5NDdkMWE5MmJlM2RlNjEyYjNhOTc0NDM5YjVlZGYzYWNjYmJkOWUwNmEwODU3YzFkOTM1In0.eyJhdWQiOiIxIiwianRpIjoiNzZlMTcwNDRkNzY2N2M2NGJiOGE2ZTI0MDg1ODk0N2QxYTkyYmUzZGU2MTJiM2E5NzQ0MzliNWVkZjNhY2NiYmQ5ZTA2YTA4NTdjMWQ5MzUiLCJpYXQiOjE1NDE3NzUyMzMsIm5iZiI6MTU0MTc3NTIzMywiZXhwIjoxNTczMzExMjMzLCJzdWIiOiIxIiwic2NvcGVzIjpbIioiXX0.lEKMisFxlBtWtILMN541dMRmDgTjdlkUNhmeKR38aCNfrbGqjCkwpBN84TJjEXFzA0dP_jKmhwPs2f-tnKDf_ceux3M71hgyz1ITTjxbQ7aVxtH-rN6cXwkpNERyr8RPqCfFTeHs4hEzREYVC1qDUpqlW7X8uNcAiaprhy_DuuACSkmvSSIYS4Txka1n0bYcNR2xy4T9Rc7wAlrqpDDGWYIqwp12Sy0_s0PYcp_CxnvPkYcVzLnyensg7tPwE6QX64EPAo6Tp24H_YtAn2bRQ7lTRrGkBMdF7CBO9sz9HGHskxv-MfnXkxSTccj_4wgabcWyHT0tj6BY77kW4qoAhesDlLfPuKLN3rqDFmU4fke_8eNZ52tYkO4_3PbJ1UJcB9Ic4u89mTfeKUKb3hegsxWUwt7JlWqI-OrLPggnSoZJshkI6NBq-eVPKcAiknF33uawFUTX-lSMjwwqwVFUKIIFSVET35ZKTkEAZblSfWbbzModaCc2feoYT8D4RBVy2pFRO6eDBbLVT6vpYEseyLKTRbE-sObVapnz0kfl6ufICvWSlw9fxpKH7tkWnJC8-4lyOPpbsX5zWb3sFoOFB8j33SwU-o5mmqbqnC6n9CN3gGbQdD8liy7BuhdOi9-QoiA0H8OePkche078hzb8C9kCxDbyXqOFYzr9doRHIOg']];
        $client = new \GuzzleHttp\Client(['base_uri' => env('URL_EMAIL')]);
        // Send a request to https://foo.com/api/test
        $response = $client->request('GET', 'api/get_notifications',$arr);
        $status = json_decode($response->getBody()->getContents());

        if($status->message === 0) {
            $data = [];
            Mail::send('notification.error', $data, function($msg){
                $msg->from(env('EMail_AD'),'ACS Solution');
                $msg->to(env('EMail_AD'),'ACS Solution')->subject('Qúa trình gửi thông báo tự động gặp lỗi và thất bại');
            });
        }
    }
}
