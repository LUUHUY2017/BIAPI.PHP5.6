<?php

namespace App\Http\Controllers\GenderAge\Administration;

use App\User;
use App\Location;
use App\Organization;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Mail;
use DateTime;
use Config;
use Maatwebsite\Excel\Facades\Excel;
use GuzzleHttp\Client;
use Carbon\Carbon;

class EmailController extends Controller
{
	public function send_email_dayly(Request $request)
	{
		$action_result = 0;
		try {
			$header = $request->header('Authorization');
			$client = new Client(['base_uri' => env('URL_EMAIL')]);
			$now = Carbon::now()->format('Y-m-d');
			$yesterday = Carbon::yesterday()->format('Y-m-d');
			$t = date('Y-m-d H:i:s');
			/*--------- gửi theo ngày của từng users-------------*/
			$users = DB::table('users')
				->join('fba_report_users', 'fba_report_users.user_id', '=', 'users.id')
				->join('organizations', 'users.organization_id', '=', 'organizations.id')
				->where([['users.age_report', 1], ['fba_report_users.age_report_dayly', 1]])
				->select('users.*', 'organizations.organization_name')
				->get();
			// lặp những users được phép nhận báo cáo
			foreach ($users as  $value) {
				$user_id           = $value->id;
				$email             = $value->email;
				$name              = $value->name;
				$organization_id   = (int)$value->organization_id;
				$organization_name = $value->organization_name;
				// thông số body request
				$form_params = 	[
					'user_id' => $user_id, 'end_hour' => "'23:59'", 'organization_id' => $organization_id, 'site_id' => 0, 'start_date' => '\'' . $yesterday . '\'', 'end_date' => '\'' . $yesterday . '\'', 'start_hour' => "'00:00'", 'view_by' => "Hour"
				];
				$this->request_send_mail($client, $header, $form_params, $organization_id, $organization_name, $email, $name);
				DB::table('poc_data_in_email_logs')->insert([
					'user_id' => $user_id, 'status'  => 1, 'calendar' => 1, 'site_id' => $organization_id, 'place' => 2
				]);
			}
			$action_result = 1;
			return response()->json(['message' => $action_result]);
		} catch (\Exception $e) {
			$action_result = 0;

			DB::table('poc_data_in_email_logs')->insert([
				'user_id' => $user_id, 'status'  => 0, 'calendar' => 1, 'site_id' => $organization_id, 'place' => 2
			]);
			return response()->json(['message' => $action_result]);
		}
	}

	public function send_email_weekly(Request $request)
	{
		$action_result = 0;
		try {
			$header = $request->header('Authorization');
			$client = new Client(['base_uri' => env('URL_EMAIL')]);
			$date = Carbon::now();
			$t = date('Y-m-d H:i:s');
			$monday = $date->startOfWeek()->format('Y-m-d');
			$sunday = $date->endOfWeek()->format('Y-m-d');
			/*--------- gửi theo tuần của từng users-------------*/
			$users = DB::table('users')
				->join('fba_report_users', 'fba_report_users.user_id', '=', 'users.id')
				->join('organizations', 'users.organization_id', '=', 'organizations.id')
				->where([['users.age_report', 1], ['fba_report_users.age_report_weekly', 1]])
				->select('users.*', 'organizations.organization_name')
				->get();
			// lặp những users được phép nhận báo cáo
			foreach ($users as  $value) {
				$user_id         = $value->id;
				$email           = $value->email;
				$name            = $value->name;
				$organization_id = (int)$value->organization_id;
				$organization_name = $value->organization_name;
				// thông số body request
				$form_params = 	[
					'user_id' => $user_id, 'end_hour' => "'23:59'", 'organization_id' => $organization_id, 'site_id' => 0, 'start_date' => '\'' . $monday . '\'', 'end_date' => '\'' . $sunday . '\'', 'start_hour' => "'00:00'", 'view_by' => "Day"
				];

				$this->request_send_mail($client, $header, $form_params, $organization_id, $organization_name, $email, $name);
				DB::table('poc_data_in_email_logs')->insert([
					'user_id' => $user_id, 'status'  => 1, 'calendar' => 2, 'site_id' => $organization_id, 'place' => 2
				]);
			}
			$action_result = 1;
			return response()->json(['message' => $action_result]);
		} catch (\Exception $e) {
			$action_result = 0;
			DB::table('poc_data_in_email_logs')->insert([
				'user_id' => $user_id, 'status'  => 0, 'calendar' => 2, 'site_id' => $organization_id, 'place' => 2
			]);
			return response()->json(['message' => $action_result]);
		}
	}

	public function send_email_monthly(Request $request)
	{
		$action_result = 0;
		try {
			$header = $request->header('Authorization');
			$client = new Client(['base_uri' => env('URL_EMAIL')]);
			$t = date('Y-m-d H:i:s');
			$start = new Carbon('first day of this month');
			$start_month =  Carbon::parse($start)->format('Y-m-d');
			$end = new Carbon('last day of this month');
			$end_month =  Carbon::parse($end)->format('Y-m-d');
			/*--------- gửi theo tháng của từng users-------------*/
			$users = DB::table('users')
				->join('fba_report_users', 'fba_report_users.user_id', '=', 'users.id')
				->join('organizations', 'users.organization_id', '=', 'organizations.id')
				->where([['users.age_report', 1], ['fba_report_users.age_report_monthly', 1]])
				->select('users.*', 'organizations.organization_name')
				->get();
			// lặp những users được phép nhận báo cáo
			foreach ($users as  $value) {
				$user_id         = $value->id;
				$email           = $value->email;
				$name            = $value->name;
				$organization_id = (int)$value->organization_id;
				$organization_name = $value->organization_name;
				// thông số body request
				$form_params = 	[
					'user_id' => $user_id, 'end_hour' => "'23:59'", 'organization_id' => $organization_id, 'site_id' => 0, 'start_date' => '\'' . $start_month . '\'', 'end_date' => '\'' . $end_month . '\'', 'start_hour' => "'00:00'", 'view_by' => "Day"
				];

				$this->request_send_mail($client, $header, $form_params, $organization_id, $organization_name, $email, $name);
				DB::table('poc_data_in_email_logs')->insert([
					'user_id' => $user_id, 'status'  => 1, 'calendar' => 3, 'site_id' => $organization_id, 'place' => 2
				]);
			}
			$action_result = 1;
			return response()->json(['message' => $action_result]);
		} catch (\Exception $e) {
			$action_result = 0;

			DB::table('poc_data_in_email_logs')->insert([
				'user_id' => $user_id, 'status'  => 1, 'calendar' => 3, 'site_id' => $organization_id, 'place' => 2
			]);
			return response()->json(['message' => $action_result]);
		}
	}
	public function request_send_mail(&$client, &$header, &$form_params, &$organization_id, &$organization_name, &$email, &$name)
	{
		$response = $client->request('POST', 'api/sp_poc_gender_metric_analytic_export_excel', [
			'headers' => [
				'Content-Type' => 'application/x-www-form-urlencoded',
				'Authorization' => $header
			], 'form_params' => $form_params
		]);
		$response2 = $client->request('POST', 'api/sp_poc_gender_metrics_comparison_export_excel', [
			'headers' => [
				'Content-Type' => 'application/x-www-form-urlencoded',
				'Authorization' => $header
			], 'form_params' => $form_params
		]);
		$age = $response->getBody()->getContents();
		$age_name = str_replace('"', '', $age);

		$gender = $response2->getBody()->getContents();
		$gender_name = str_replace('"', '', $gender);

		$data = ['name' => $name, 'site' => $organization_name, 'title' => 'Gender Age', 'body' => 'thống kê giới tính, độ tuổi khách ra vào'];
		$path_name = public_path() . '\exports\\' . $age_name;
		$path_name2 = public_path() . '\exports\\' . $gender_name;
		$confi =  DB::table('mail_configuration')->where(['organization_id', $organization_id])->first();
		if ($confi) {
			$port = $confi->port;
			$sender_name = $confi->user_name;
			$sender_mail = $confi->email;
		    $sender_password = $confi->pass_word;
            Config::set('mail.host',  trim($confi->server));
            Config::set('mail.port', $port);
            Config::set('mail.username', $sender_mail);
            Config::set('mail.password', $sender_password);
		} else {
			$sender_mail = env('MAIL_USERNAME');
			$sender_name = 'ACS ANALYTICS';
		}
		Mail::send('sendmail', $data, function ($message) use ($path_name, $path_name2, $sender_mail, $sender_name, $email, $name) {    //sale.pyxis@gmail.com  				abfzlwidalmnluas
			$message->from($sender_mail, $sender_name);
			$message->attach($path_name);
			$message->attach($path_name2);
			$message->to($email, $name);
			$message->subject('ACS Gender Age - Báo cáo kết quả giới tính độ tuổi');
		});
		// thực hiện xóa file đã xuất
		if (file_exists($path_name)) {
			unlink($path_name);
		}
		if (file_exists($path_name2)) {
			unlink($path_name2);
		}
	}
}
