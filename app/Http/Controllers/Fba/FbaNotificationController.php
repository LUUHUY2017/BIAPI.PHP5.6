<?php

namespace App\Http\Controllers\Fba;

use App\FbaNotifications;
use App\FbaQuestion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;
use Mail;
use Datetime;
use Carbon;
class FbaNotificationController extends Controller
{
    // Hàm lấy ra danh sách
    public function index(Request $request)
    {
        $request_user = $request->user();
        $user_id = $request_user->id;
        
        $organization_id = 0;
        
        if($request_user->organization_id)
           $organization_id = (int)$request_user->organization_id;
        $deleted = 0;
        $object = DB::select("exec sp_fba_notifications $user_id, $organization_id, $deleted");
        return response()->json(['list_of_notification' => $object]);
    }
    // End hàm lấy ra danh sách
    // Hàm thêm mới
    public function store(Request $request)
    {
        $request_user = $request->user();
        $user_id = $request_user->id;
        $organization_id = $request->organization_id ? $request->organization_id : $request_user->organization_id;
        DB::beginTransaction();
        try {
            $object = new FbaNotifications;
            $object->organization_id = $organization_id;
            $object->location_id = 0;
            $object->parameters = $request->parameters;
            $object->save();
            DB::commit();
            return response()->json(['message' => 1]);
        }
        catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 0]);
        }
    }
    // end hàm thêm mới
    // Hàm sửa dữ liệu
    public function get_update($id)
    {
        //
        $data = FbaNotifications::find($id);
        // $data = DB::select('exec sp_fba_notifications @id=$id');
        return response()->json(['data' => $data]);
    }

    public function post_update($id, Request $request)
    {
        $data = FbaNotifications::find($id);
        DB::beginTransaction();
        try {
            $data->parameters = $request->parameters;
            if($request->organization_id) {
                $data->organization_id = $request->organization_id;
            }
            $data->save();
            DB::commit();
            return response()->json(['message' => 1]);
        }
        catch (\Throwable $e) {
            DB::rollback();
            return response()->json(['message' => 0]);
        }
    }
    // End hàm sửa dữ liệu
    // Hàm xóa dữ liệu
    public function destroy(Request $request)
    {
        try {
            $action = DB::table('fba_notifications')->where('id',$request->id)->update([
            'deleted' => 1
            ]);
            return response()->json(['message' => $action]);
        }
        catch(\Exception $exception){
            return response()->json(['message' => 0]);
        }
    }
    // End hàm xóa dữ liệu

    public function get_notification() {
        // xử lý gửi mail.
        try {
            $view_by = 'hour';
            $start_date = "'".date('Y-m-d')."'";
            $end_date = $start_date;
            // $start_date = "'2019-01-7'";
            // $end_date = "'2019-01-13'";
            $site_array = DB::select("SELECT * FROM fc_get_location_for_notification()");
            foreach ($site_array as $value) { // 39 vòng
                $organization_id = $value->organization_id;
                $site_id = $value->id;
                $site_name = $value->site_name;
                $configure = DB::select('SELECT parameters FROM fba_notifications WHERE organization_id = '.$organization_id);
                $notification_number = 10;
                $time_to_fire = 2;
                if($configure !== []) {
                    $configure = json_decode($configure[0]->parameters);
                    $notification_number = $configure->notification_number;
                    $time_to_fire = $configure->time_to_fire;
                }
                $site_user_array = DB::select('SELECT users.email,users.name, groups.id, sites.id, sites.site_name
                    FROM group_user INNER JOIN users ON users.id = group_user.user_id 
                    INNER JOIN groups ON groups.id = group_user.group_id
                    INNER JOIN group_site ON group_site.group_id = groups.id INNER JOIN sites ON group_site.site_id = sites.id WHERE sites.id = '.$site_id);
                $question_array = DB::select('SELECT q.id,q.question_name FROM fba_questions q WHERE q.organization_id ='.$organization_id);
                $user_array = DB::select('SELECT u.id, u.email FROM users u WHERE u.lever = 1 AND u.organization_id ='.$organization_id);
                $start_time = "'" . DB::select("SELECT CAST(DATEADD(hour, -$time_to_fire, GetDate()) AS TIME(0)) AS DATE_START")[0]->DATE_START . "'";
                $end_time = "'" . DB::select("SELECT CAST(DATEADD(hour, 0, GetDate()) AS TIME(0)) AS DATE_END")[0]->DATE_END . "'";
                // echo $notification_number;
                // echo $time_to_fire;
                // echo '<br>';
                foreach ($question_array as $value) { // tổng số vòng lặp hiện tại là 66
                    $question_id = $value->id;
                    $user_id = $user_array[0]->id;
                    $data = DB::select("exec sp_fba_report_metrics_analytics 
                        @user_id = $user_id
                        , @organization_id = $organization_id
                        , @site_id = $site_id
                        , @question_id = $question_id
                        , @start_hour = $start_time
                        , @end_hour = $end_time
                        , @start_date = $start_date
                        , @end_date = $end_date
                        , @view_by = $view_by"
                    );
                    // return response()->json(['start_time' => $start_time,'end_time' => $end_time,'start_date' => $start_date,'end_date' => $end_date, 'view_by' => $view_by,'organization_id' => $organization_id, 'site_id' => $site_id, 'question_id' => $question_id,'user_id' => $user_id]);
                    $total_response = 0;
                    $total_negative = 0;
                    foreach ($data as $value) {
                        $total_response += $value->very_positive + $value->positive + $value->negative + $value->very_negative;
                        $total_negative += $value->negative + $value->very_negative;
                    }
                    // echo $total_response + '&nbsp;';
                    // echo $total_negative;
                    // echo '<br>';
                    // echo $i;
                    // echo '<br>';
                    if($total_response > 0) {
                        $comparison_number = ($total_negative / $total_response) * 100;
                        if($comparison_number > $notification_number) {
                            $data = [
                                'site_name' => $site_name,
                                'notification_number' => $total_negative
                            ];
                            if($site_user_array !== []) {
                                foreach ($site_user_array as $value) {
                                    $this->send_mail($value->email,$data);
                                }
                            }
                        }
                    }
                }
            }
            return response()->json(['message' => 1]);
        } catch (\Exception $exception) {
            return response()->json(['message' => 0]);
        }
    }
    public function send_mail($to_user, $data, $path = NULL) {
        Mail::send('notification.send_notification',$data, function($message) use ($path, $to_user) {
            $message->from('sale.pyxis@gmail.com','ACS Solutions');
            if ($path !== NULL) {
                $message->attach($path);
            }
            $message->to($to_user,'ACS Solutions')->subject('Thông báo tự động');
        });
    }
}
