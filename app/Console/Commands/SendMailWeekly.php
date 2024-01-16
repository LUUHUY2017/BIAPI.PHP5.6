<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Mail;
use App\User;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use GuzzleHttp\Client;
use function GuzzleHttp\json_decode;


class SendMailWeekly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SendMailWeekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send mail of report weekly';

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
     * @return mixed5
     */
    public function handle()
    {
        // $form_params = ['grant_type' => "client_credentials", 'client_id' => "1", 'client_secret' => 'pMcVtltzSopwTl24es094rhZFoqoeJcbtRl6bYqs'];
        // $header = [
        //     'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
        //     'form_params' => $form_params
        // ];
        // $client = new Client(['base_uri' => env('URL_EMAIFL')]);
        // $token = $client->request('POST', 'oauth/token', $header);
        // $poc = json_decode($token->getBody()->getContents());
        // $token_acc = $poc->access_token;
        // $arr = ['headers' => ['Authorization' => 'Bearer '.$token_acc]];
        $arr = ['headers' => ['Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImMyNDc2MGYyZjIwNTA2MjRiMWVkYTNmNTRlNmJmMWYwNTJlYTNmY2MzYTJhNWQyMzZmODUzOTBiNzVkMDc1N2YyZTcyNjk4MDVlMTllYzEzIn0.eyJhdWQiOiIxIiwianRpIjoiYzI0NzYwZjJmMjA1MDYyNGIxZWRhM2Y1NGU2YmYxZjA1MmVhM2ZjYzNhMmE1ZDIzNmY4NTM5MGI3NWQwNzU3ZjJlNzI2OTgwNWUxOWVjMTMiLCJpYXQiOjE1NjE2OTYyNjQsIm5iZiI6MTU2MTY5NjI2NCwiZXhwIjoxNTkzMzE4NjY0LCJzdWIiOiIxIiwic2NvcGVzIjpbIioiXX0.GQEbG0tJQvqELiT0LO2gQbfGY5tPO3irFATuk4Jmme3WyZcdbUP2PyOqVlEtPicNiSfwScXYA79-SwGE7lQK5F8zfDsXwaQV3jp58I2In79URoBnFnoQrmmbDohunDZqju1dcVY2kH9xaiIlIRb7pQKHqzJ3z1AxcB2Eobg3BnxzzYdjMOO3X2xxlWT8C3nEiRn1NcRWMXXFqgXumOrjhyDyko28fR9UsB1VqBdrPA9VdH81AIRP50mlFPTgd1pQYYYTLpHjmvgGqS6HvlIkJeEIFuvB9CrHSyZedhtg5ryh_TpQqNTizaRI--bSPDQV3_g7y0bD89iy1h4k_C6m38dO1uP6JumBthWeVBzwohBTwggqh_sma68iRUNCC5bTzNPJ9sGuHqashGsoXAmbkf9hiLyVL4-4Uh-36eWYuigEHkU9bdxNywdwtsbmh6p983xS0NSvigeFRaYJ8sMNqAxiwZkzQD6jGDkCd-9OMOhuk1Do19SxWfX585ep32T44zsIFGB8jm8mo4OUJkSU5JhzmqoyqpFwTYMBPO2AnZxiDWRpFxHEGx7qwFb_g6Xoei-09qgMNyOcTEaPawcFhu9KaVPPIfsXrcqZY-KBCKL8z0pHOhqsJTeJUJuyjKRQiqYNtD7oILkwL82GlRmq2hfJ6J_hhxmp5l1FdMUpe_g']];
        $client = new Client(['base_uri' => env('URL_EMAIL')]);
        $response = $client->request('POST', 'api/fba_send_email_weekly', $arr);
        $response1 = $client->request('POST', 'api/poc_send_email_weekly', $arr);
        $response2 = $client->request('POST', 'api/age_send_email_weekly', $arr);
        $response3 = $client->request('POST', 'api/hqhd_send_email_weekly', $arr);
    }
}
