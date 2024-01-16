<?php

namespace App\Http\Controllers\HQHD\Administration;

use App\User;
use App\Location;
use App\Organization;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Mail;
use DateTime;
use Config;
use PDF;
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
				->where([['users.hqhd_report', 1], ['fba_report_users.hqhd_report_dayly', 1]])
				->select('users.*', 'organizations.organization_name')
				->get();
			// lặp những users được phép nhận báo cáo
			foreach ($users as  $value) {
				$user_id           = $value->id;
				$email             = $value->email;
				$name              = $value->name;
				$organization_id   = (int) $value->organization_id;
				$organization_name = $value->organization_name;
				// thông số body request
				$form_params = 	[
					'user_id' => $user_id, 'end_time' => "'23:59'", 'organization_id' => $organization_id, 'site_id' => 0, 'start_date' => '\'' . $yesterday . '\'', 'end_date' => '\'' . $yesterday . '\'', 'start_time' => "'00:00'", 'level' => "Hour", 'operation' => 'sum', 'dimension' => 'Site', 'view_by' => "Hour", 'export'  => 'sosanh'
				];
				$this->request_send_mail($client, $header, $form_params, $organization_id, $organization_name, $email, $name);
				DB::table('poc_data_in_email_logs')->insert([
					'user_id' => $user_id, 'status'  => 1, 'calendar' => 1, 'site_id' => $organization_id, 'place' => 4
				]);
			}
			$action_result = 1;
			return response()->json(['message' => $action_result]);
		} catch (\Exception $e) {
			$action_result = 0;

			DB::table('poc_data_in_email_logs')->insert([
				'user_id' => $user_id, 'status'  => 0, 'calendar' => 1, 'site_id' => $organization_id, 'place' => 4
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
				->where([['users.hqhd_report', 1], ['fba_report_users.hqhd_report_weekly', 1]])
				->select('users.*', 'organizations.organization_name')
				->get();
			// lặp những users được phép nhận báo cáo
			foreach ($users as  $value) {
				$user_id         = $value->id;
				$email           = $value->email;
				$name            = $value->name;
				$organization_id = (int) $value->organization_id;
				$organization_name = $value->organization_name;
				// thông số body request
				$form_params = 	[
					'user_id' => $user_id, 'end_time' => "'23:59'", 'organization_id' => $organization_id, 'site_id' => 0, 'start_date' => '\'' . $monday . '\'', 'end_date' => '\'' . $sunday . '\'', 'start_time' => "'00:00'", 'level' => "Day", 'operation' => 'sum', 'dimension' => 'Site', 'view_by' => "Day", 'export'  => 'sosanh'
				];

				$this->request_send_mail($client, $header, $form_params, $organization_id, $organization_name, $email, $name);
				DB::table('poc_data_in_email_logs')->insert([
					'user_id' => $user_id, 'status'  => 1, 'calendar' => 2, 'site_id' => $organization_id, 'place' => 4
				]);
			}
			$action_result = 1;
			return response()->json(['message' => $action_result]);
		} catch (\Exception $e) {
			$action_result = 0;
			DB::table('poc_data_in_email_logs')->insert([
				'user_id' => $user_id, 'status'  => 0, 'calendar' => 2, 'site_id' => $organization_id, 'place' => 4
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
				->where([['users.hqhd_report', 1], ['fba_report_users.hqhd_report_monthly', 1]])
				->select('users.*', 'organizations.organization_name')
				->get();
			// lặp những users được phép nhận báo cáo
			foreach ($users as  $value) {
				$user_id         = $value->id;
				$email           = $value->email;
				$name            = $value->name;
				$organization_id = (int) $value->organization_id;
				$organization_name = $value->organization_name;
				// thông số body request
				$form_params = 	[
					'user_id' => $user_id, 'end_time' => "'23:59'", 'organization_id' => $organization_id, 'site_id' => 0, 'start_date' => '\'' . $start_month . '\'', 'end_date' => '\'' . $end_month . '\'', 'start_time' => "'00:00'", 'level' => "Day", 'operation' => 'sum', 'dimension' => 'Site', 'view_by' => "Day", 'export'  => 'sosanh'
				];

				$this->request_send_mail($client, $header, $form_params, $organization_id, $organization_name, $email, $name);
				DB::table('poc_data_in_email_logs')->insert([
					'user_id' => $user_id, 'status'  => 1, 'calendar' => 3, 'site_id' => $organization_id, 'place' => 4
				]);
			}
			$action_result = 1;
			return response()->json(['message' => $action_result]);
		} catch (\Exception $e) {
			$action_result = 0;

			DB::table('poc_data_in_email_logs')->insert([
				'user_id' => $user_id, 'status'  => 1, 'calendar' => 3, 'site_id' => $organization_id, 'place' => 4
			]);
			return response()->json(['message' => $action_result]);
		}
	}
	public function request_send_mail(&$client, &$header, &$form_params, &$organization_id, &$organization_name, &$email, &$name)
	{
		// $response = $client->request('POST', 'api/sp_footfall_get_traffic_export_excel',[
		$response = $client->request('POST', 'api/sp_footfall_performance_data_by_site_export_excel', [
			'headers' => [
				'Content-Type' => 'application/x-www-form-urlencoded',
				'Authorization' => $header
			], 'form_params' => $form_params
		]);
		$poc = $response->getBody()->getContents();
		$poc_name = str_replace('"', '', $poc);

		$data = ['name' => $name, 'site' => $organization_name, 'title' => 'POC', 'body' => 'hiệu quả hoạt động'];
		$path_name = public_path() . '\exports\\' . $poc_name;

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
		Mail::send('sendmail', $data, function ($message) use ($path_name, $sender_mail, $sender_name, $email, $name) {    //sale.pyxis@gmail.com  				abfzlwidalmnluas
			$message->from($sender_mail, $sender_name);
			$message->attach($path_name);
			$message->to($email, $name);
			$message->subject('ACS POC - Báo cáo kết quả hiệu quả hoạt động');
		});
		// thực hiện xóa file đã xuất
		if (file_exists($path_name)) {
			unlink($path_name);
		}
	}

	public function sendmail(Request $request)
	{

		$data["subject"] = "";
		$user_id = 1;
		$organization_id = 6;
		$site_id = 0;
		$start_time = "'08:00'";
		$end_time = "'23:59'";
		$start_date = "'2019-12-20'";
		$end_date = "'2019-12-20'";
		$view_by = "'Hour'";
		$operation = 'SUM';
		$items = DB::select("exec sp_poc_data_in_out_sum_by_site $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date, $end_date, $view_by,  $operation");
		$func = function ($value) {
			return $value->id;
		};
		$new = array_map($func, $items);
		$pdf = PDF::loadView('layouts.PDF.footfall_metrics', ['new' => json_encode($new), 'items' => $items]);
		$path = public_path() . '\exports\\' . "example.pdf";
		$pdf->save($path);

		try {
			$sender_mail = env('MAIL_USERNAME');
			$sender_name = 'ACS ANALYTICS';
			Mail::send('layouts.PDF.whitespace', $data, function ($message) use ($sender_mail, $sender_name, $pdf) {    //sale.pyxis@gmail.com  				abfzlwidalmnluas
				$message->from($sender_mail, $sender_name);
				$message->attachData($pdf->output(), "PDF.pdf");
				// $message->Attach($pdf);
				$message->to("luuduchuy.it@gmail.com", "Lưu Đức Huy");
				$message->subject('ACS POC - Báo cáo kết quả đo lường lưu lượng ra vào');
			});
		} catch (Exception $exception) {
			$this->serverstatuscode = "0";
			$this->serverstatusdes = $exception->getMessage();
		}
		return response()->json(compact('this'));
	}

	public function view(Request $request)
	{
		$user_id = 1;
		$organization_id = 6;
		$site_id = 0;
		$start_time = "'08:00'";
		$end_time = "'23:59'";
		$start_date = "'2019-12-20'";
		$end_date = "'2019-12-20'";
		$view_by = "'Hour'";
		$operation = 'SUM';
		$items = DB::select("exec sp_poc_data_in_out_sum_by_site $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date, $end_date, $view_by,  $operation");
		$func = function ($value) {
			return $value->id;
		};
		$new = array_map($func, $items);
		return view("layouts.PDF.footfall_metrics", ['new' => json_encode($new), 'items' => $items]);
	}
}
