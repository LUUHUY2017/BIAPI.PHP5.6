<?php

namespace App\Http\Controllers\Fba\Administration;

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
				->where([['users.recerviced_report', 1], ['fba_report_users.report_dayly', 1]])
				->select('users.*')
				->get();
			// lặp những users được phép nhận báo cáo
			foreach ($users as  $value) {
				$user_id         = $value->id;
				$email           = $value->email;
				$name            = $value->name;
				$organization_id = (int) $value->organization_id;
				// thực hiện lấy câu hỏi đang diễn ra
				$questions       = DB::select("exec sp_fba_get_question_now $user_id, $organization_id,2");
				// nếu ko có câu hỏi đang diễn ra thì lấy câu hỏi đã kết thúc
				// if(count($questions) == 0){
				// 	$questions       = DB::select("exec sp_fba_get_question_now $user_id, $organization_id,3");
				// }
				// kiểm tra xem hiện tại user có câu hỏi đang hoạt động hay không

				if (count($questions) > 0) {
					// lấy câu hỏi đầu tiên &  id câu hỏi
					$question           = $questions[0];
					$question_id        = $question->id;
					$organization_name  = $question->organization_name;

					// thông số body request
					$form_params = 	[
						'user_id' => $user_id, 'category_id' => 0, 'end_date' => '\'' . $yesterday . '\'', 'end_hour' => "'23:59'", 'organization_id' => $organization_id, 'question_id' => $question_id, 'site_id' => 0, 'start_date' => '\'' . $yesterday . '\'', 'start_hour' => "'00:00'", 'view_by' => "Hour"
					];
					$this->request_send_mail($client, $header, $form_params, $organization_id, $organization_name, $email, $name);

					DB::table('fba_report_user_email_logs')->insert([
						'user_id' => $user_id, 'question_id' => $question_id, 'status'  => 1, 'calendar' => 1, 'site_id' => $organization_id
					]);
				}
			}
			$action_result = 1;
		} catch (\Exception $e) {
			$action_result = 0;
			DB::table('fba_report_user_email_logs')->insert([
				'user_id' => $user_id, 'question_id' => $question_id, 'status'  => 0, 'calendar' => 1, 'site_id' => $organization_id
			]);
			return response()->json(['message' => $action_result]);
		}
		return response()->json(['message' => $action_result]);
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
				->where([['users.recerviced_report', 1], ['fba_report_users.report_weekly', 1]])
				->select('users.*')
				->get();
			// lặp những users được phép nhận báo cáo
			foreach ($users as  $value) {
				$user_id         = $value->id;
				$email           = $value->email;
				$name            = $value->name;
				$organization_id = (int) $value->organization_id;
				// thực hiện lấy câu hỏi đang diễn ra
				$questions       = DB::select("exec sp_fba_get_question_now $user_id, $organization_id,2");
				if (count($questions) > 0) {
					// lấy câu hỏi đầu tiên &  id câu hỏi
					$question        = $questions[0];
					$question_id     = $question->id;
					$organization_name  = $question->organization_name;
					// thông số body request
					$form_params = 	[
						'user_id' => $user_id, 'category_id' => 0, 'end_date' => '\'' . $sunday . '\'', 'end_hour' => "'23:59'", 'organization_id' => $organization_id, 'question_id' => $question_id, 'site_id' => 0, 'start_date' => '\'' . $monday . '\'', 'start_hour' => "'00:00'", 'view_by' => "Day"
					];
					$this->request_send_mail($client, $header, $form_params, $organization_id, $organization_name, $email, $name);

					DB::table('fba_report_user_email_logs')->insert([
						'user_id' => $user_id, 'question_id' => $question_id, 'status'  => 1, 'calendar' => 2, 'site_id' => $organization_id
					]);
				}
			}
			$action_result = 1;
			return response()->json(['message' => $action_result]);
		} catch (\Exception $e) {
			$action_result = 0;
			DB::table('fba_report_user_email_logs')->insert([
				'user_id' => $user_id, 'question_id' => $question_id, 'status'  => 0, 'calendar' => 2, 'site_id' => $organization_id
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
				->where([['users.recerviced_report', 1], ['fba_report_users.report_monthly', 1]])
				->select('users.*')
				->get();
			// lặp những users được phép nhận báo cáo
			foreach ($users as  $value) {
				$user_id         = $value->id;
				$email           = $value->email;
				$name            = $value->name;
				$organization_id = (int) $value->organization_id;
				// thực hiện lấy câu hỏi đang diễn ra
				$questions       = DB::select("exec sp_fba_get_question_now $user_id, $organization_id,2");
				if (count($questions) > 0) {
					// lấy câu hỏi đầu tiên &  id câu hỏi
					$question        = $questions[0];
					$question_id     = $question->id;
					$organization_name  = $question->organization_name;
					// thông số body request
					$form_params = 	[
						'user_id' => $user_id, 'category_id' => 0, 'end_date' => '\'' . $end_month . '\'', 'end_hour' => "'23:59'", 'organization_id' => $organization_id, 'question_id' => $question_id, 'site_id' => 0, 'start_date' => '\'' . $start_month . '\'', 'start_hour' => "'00:00'", 'view_by' => "Day"
					];
					$this->request_send_mail($client, $header, $form_params, $organization_id, $organization_name, $email, $name);

					DB::table('fba_report_user_email_logs')->insert([
						'user_id' => $user_id, 'question_id' => $question_id, 'status'  => 1, 'calendar' => 3, 'site_id' => $organization_id
					]);
				}
			}
			$action_result = 1;
			return response()->json(['message' => $action_result]);
		} catch (\Exception $e) {
			$action_result = 0;
			DB::table('fba_report_user_email_logs')->insert([
				'user_id' => $user_id, 'question_id' => $question_id, 'status'  => 0, 'calendar' => 2, 'site_id' => $organization_id
			]);
			return response()->json(['message' => $action_result]);
		}
	}

	public function request_send_mail(&$client, &$header, &$form_params, &$organization_id, &$organization_name, &$email, &$name)
	{
		$response = $client->request('POST', 'api/export_metrics_comparison', [
			'headers' => [
				'Content-Type' => 'application/x-www-form-urlencoded',
				'Authorization' => $header
			], 'form_params' => $form_params
		]);
		$comparison = $response->getBody()->getContents();
		$comparison_name = str_replace('"', '', $comparison);

		$response = $client->request('POST', 'api/export_metrics_analytic', [
			'headers' =>
			[
				'Content-Type' => 'application/x-www-form-urlencoded',
				'Authorization' => $header
			], 'form_params' => $form_params
		]);
		$nalytic = $response->getBody()->getContents();
		$nalytic_name = str_replace('"', '', $nalytic);

		$pathToFile_comparison = public_path() . '\exports\\' . $comparison_name;
		$pathToFile_nalytic = public_path() . '\exports\\' . $nalytic_name;
		$data = ['name' => $name, 'site' => $organization_name, 'title' => 'Smileys', 'body' => 'trải nghiệm khách hàng'];
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
		Mail::send('sendmail', $data, function ($message) use ($pathToFile_nalytic, $pathToFile_comparison, $sender_mail, $sender_name, $email, $name) {    //sale.pyxis@gmail.com  				abfzlwidalmnluas
			$message->from($sender_mail, $sender_name);
			$message->attach($pathToFile_nalytic);
			$message->attach($pathToFile_comparison);
			$message->to($email, $name);
			$message->subject('ACS Smileys - Báo cáo kết quả trải nghiệm khách hàng');
		});
		// thực hiện xóa file đã xuất
		if (file_exists($pathToFile_comparison)) {
			unlink($pathToFile_comparison);
		}
		if (file_exists($pathToFile_nalytic)) {
			unlink($pathToFile_nalytic);
		}
	}
}
