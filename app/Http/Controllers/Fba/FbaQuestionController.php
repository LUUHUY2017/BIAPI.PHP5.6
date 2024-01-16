<?php

namespace App\Http\Controllers\Fba;

use App\FbaQuestion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;

use App\FbaQuestionResponse;
use App\FbaReasonResponse;
use App\FbaReasonOther;
use App\FbaCustomersInfo;
use App\FbaTablet;
use App\FbaReason;
use Carbon\Carbon;
// use ElephantIO\Client;
// use ElephantIO\Engine\SocketIO\Version1X;
// require __DIR__ . '/../../../../vendor/autoload.php';

use ElephantIO\Client, ElephantIO\Engine\SocketIO\Version2X, ElephantIO\Exception\ServerConnectionFailureException;

require __DIR__ . '/../../../../vendor/autoload.php';

// Nghĩa thêm library để xử lý ảnh
use Image;
// end nghĩa

class FbaQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $request_user = $request->user();
        $user_id = $request_user->id;

        $organization_id = intval($request->organization_id);
        $question_id = intval($request->question_id);

        $items = DB::select("exec sp_fba_get_question $user_id, $organization_id, $question_id");
        return response()->json($items);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\FbaQuestion  $fbaQuestion
     * @return \Illuminate\Http\Response
     */
    public function show(FbaQuestion $fbaQuestion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\FbaQuestion  $fbaQuestion
     * @return \Illuminate\Http\Response
     */
    public function edit(FbaQuestion $fbaQuestion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\FbaQuestion  $fbaQuestion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FbaQuestion $fbaQuestion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\FbaQuestion  $fbaQuestion
     * @return \Illuminate\Http\Response
     */
    public function destroy(FbaQuestion $fbaQuestion)
    {
        //
    }

    public function question_feedback(Request $request)
    {
        DB::beginTransaction();

        try {
            $request_user = $request->user();
            $user_id = $request_user->id;

            $data = $request->data;

            $serial_number = $data['serial_number'];
            $organization_id = $request_user->organization_id;
            $location_id = $data['location_id'];
            $question_id = $data['question_id'];
            $answer = $data['answer'];


            $question_response = new FbaQuestionResponse;
            $question_response->serial_number = $serial_number;
            $question_response->organization_id = $organization_id;
            $question_response->location_id = $location_id;
            $question_response->question_id = $question_id;
            $question_response->created_by = $user_id;
            $question_response->updated_by = $user_id;
            $question_response->start_time = $data['start_time'];
            $question_response->start_time_int = $data['start_time_int'];

            $question_response->answer = $answer;

            $question_response->very_negative = ($answer == 'very_negative'); // $data['very_negative'];
            $question_response->negative = ($answer == 'negative'); //$data['negative'];
            $question_response->positive = ($answer == 'positive'); //$data['positive'];
            $question_response->very_positive = ($answer == 'very_positive'); //$data['very_positive'];
            $question_response->save();
            $question_response_id = $question_response->id;
            // reasons
            if (array_key_exists('reasons', $data)) {
                $reasons = $data['reasons'];
                foreach ($reasons as $key => $value) {
                    $reason_response = new FbaReasonResponse;
                    $reason_response->serial_number = $serial_number;
                    $reason_response->organization_id = $organization_id;
                    $reason_response->location_id = $location_id;
                    $reason_response->question_id = $question_id;
                    $reason_response->created_by = $user_id;
                    $reason_response->updated_by = $user_id;
                    $reason_response->question_response_id = $question_response_id;
                    $reason_response->reason_id = $value;
                    $reason_response->save();
                }
            }


            $reason_other = new FbaReasonOther;
            $reason_other->serial_number = $serial_number;
            $reason_other->organization_id = $organization_id;
            $reason_other->location_id = $location_id;
            $reason_other->question_id = $question_id;
            $reason_other->created_by = $user_id;
            $reason_other->updated_by = $user_id;
            $reason_other->question_response_id = $question_response_id;
            $reason_other->reason_name = $data['reason_other'];
            $reason_other->save();

            if (array_key_exists('customer_info', $data)) {
                $customer_info = new FbaCustomersInfo;
                $customer_info->serial_number = $serial_number;
                $customer_info->organization_id = $organization_id;
                $customer_info->location_id = $location_id;
                $customer_info->question_id = $question_id;
                $customer_info->created_by = $user_id;
                $customer_info->updated_by = $user_id;
                $customer_info->question_response_id = $question_response_id;

                $cus_info = $data['customer_info'];
                $customer_info->customer_name = $cus_info['customer_name'];
                $customer_info->customer_phone = $cus_info['customer_phone'];
                $customer_info->customer_email = $cus_info['customer_email'];

                $customer_info->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
    }

    public function question_tablet_feedback(Request $request)
    {
        $package_id = "";
        $datas = "";
        $action_result = 0;

        $serial_number = "";
        $organization_id = 0;
        $location_id = 0;
        $question_id = 0;

        // DB::beginTransaction();
        try {

            $data = $request->data;

            $package_id = "'" . $data['package_id'] . "'";

            $datas = $data['datas'];

            $json_datas = json_decode($datas);
            if (is_array($json_datas)) {
                foreach ($json_datas as $item) {
                    $serial_number = $item->serial_number;
                    $organization_id = $item->organization_id;
                    $location_id = $item->location_id;
                    $question_id = $item->question_id;
                    $answer = $item->answer;

                    // $startTime =  Carbon::parse($item->start_time)->format('H:i');
                    // $hour =  DB::select("exec sp_fba_get_open_close_hour_from_site  $location_id ");
                    // $hour = $hour[0];
                    // $open_hour = $hour->open_hour;
                    // $close_hour = $hour->close_hour;
                    // // $close_hour = date('H:i',strtotime('+1 hour',strtotime($hour->close_hour)));
                    // if ($open_hour <= $startTime && $startTime  < $close_hour) {

                    $question_response = new FbaQuestionResponse;
                    $question_response->serial_number = $serial_number;
                    $question_response->organization_id = $organization_id;
                    $question_response->location_id = $location_id;
                    $question_response->question_id = $question_id;
                    $question_response->start_time = $item->start_time;
                    $question_response->start_time_int = $item->start_time_int;

                    $question_response->answer = $answer;

                    $question_response->very_negative = ($answer == 'very_negative');
                    $question_response->negative = ($answer == 'negative');
                    $question_response->positive = ($answer == 'positive');
                    $question_response->very_positive = ($answer == 'very_positive');
                    $a = $question_response->save();
                    $question_response_id = $question_response->id;
                    // reasons
                    if ($item->reasons != null && is_array($item->reasons)) {
                        $reasons = $item->reasons;
                        foreach ($reasons as $key => $value) {
                            $reason_response = new FbaReasonResponse;
                            $reason_response->serial_number = $serial_number;
                            $reason_response->organization_id = $organization_id;
                            $reason_response->location_id = $location_id;
                            $reason_response->question_id = $question_id;
                            $reason_response->question_response_id = $question_response_id;
                            $reason_response->reason_id = $value;
                            $d = $reason_response->save();
                        }
                    }

                    if ($item->reason_other != null) {
                        $reason_other = new FbaReasonOther;
                        $reason_other->serial_number = $serial_number;
                        $reason_other->organization_id = $organization_id;
                        $reason_other->location_id = $location_id;
                        $reason_other->question_id = $question_id;
                        $reason_other->question_response_id = $question_response_id;
                        $reason_other->reason_name = $item->reason_other;
                        $b = $reason_other->save();
                    }

                    if (isset($item->customer_info) && $item->customer_info != null) {
                        $cus_info = $item->customer_info;
                        if (isset($cus_info) && $cus_info != null) {
                            $customer_name = isset($cus_info->customer_name) ? $cus_info->customer_name : "";
                            $customer_phone = isset($cus_info->customer_phone) ? $cus_info->customer_phone : "";
                            $customer_email = isset($cus_info->customer_email) ? $cus_info->customer_email : "";
                            if ($customer_name != "" || $customer_phone != "" || $customer_email != "") {
                                $customer_info = new FbaCustomersInfo;
                                $customer_info->serial_number = $serial_number;
                                $customer_info->organization_id = $organization_id;
                                $customer_info->location_id = $location_id;
                                $customer_info->question_id = $question_id;
                                $customer_info->question_response_id = $question_response_id;


                                $customer_info->customer_name = $customer_name;
                                $customer_info->customer_phone = $customer_phone;
                                $customer_info->customer_email = $customer_email;

                                $c = $customer_info->save();
                            }
                        }
                    }
                    // }
                    // DB::commit();
                }
                try {
                    $time_now = date('Y-m-d H:i:s');
                    $tablet = DB::table('fba_tablets')->where('serial_number', $serial_number)->update([
                        'monitor' =>  2, 'recerviced_time' => $time_now
                    ]);
                } catch (\Exception $e) {
                }
                $action_result = 1;
            }
        } catch (\Exception $e) {
            $action_result = 0;
            DB::rollback();
        }

        // $package_content =  json_encode($datas);
        // if($package_id!="")
        //     $tablet_feedback_packages = DB::select("exec sp_insert_fba_tablet_feedback_packages $action_result, $package_id,  $package_content");

        return response()->json(array(
            'package_id' =>  $package_id, 'status' => $action_result
        ));
    }

    public function tablet_get_data(Request $request)
    {
        $organization_id = 0;
        $location_id = 0;

        // Nếu có serial_number
        if ($request->has('serial_number')) {
            $serial_number = $request->serial_number;

            // Lấy thông tin của thiết bị dựa vào serial_number
            $tablets =  FbaTablet::where('serial_number', $serial_number)->get();
            if (count($tablets) < 1) {
                $tablet = new FbaTablet;
                $tablet->organization_id = $organization_id;
                $tablet->location_id = $location_id;
                $tablet->serial_number = $serial_number;
                $tablet->imei = $serial_number;
                $tablet->actived = 0;

                $tablet->save();
            }

            if (count($tablets) > 0) {
                $tablet = $tablets[0];
                $organization_id = (int) $tablet->organization_id;
                $location_id = (int) $tablet->location_id;
            }

            // $tablets =  DB::select("exec sp_fba_tablets_get_by_serial_number $serial_number");
            // Nếu chưa có có thiết bị chèn mới vào CSDL
            // if(!$tablets){
            //     $tablet = new FbaTablet;
            //     $tablet->organization_id = $organization_id;
            //     $tablet->location_id = $location_id;
            //     $tablet->serial_number = $serial_number;
            //     $tablet->imei = $serial_number;
            //     $tablet->actived = 0;

            //     $tablet->save();
            // }

            // if($tablets){
            //     $tablet = $tablets[0];
            //     $organization_id = (int)$tablet->organization_id;
            //     $location_id = (int)$tablet->location_id;
            // }
        }

        if ($request->has('organization_id'))
            $organization_id = (int) $request->organization_id;
        if ($request->has('location_id'))
            $location_id = (int) $request->location_id;

        return response()->json($this->get_data($organization_id, $location_id));
    }
    private function get_data($organization_id, $location_id)
    {
        // lấy câu hỏi mặc định
        $questions = DB::select("exec sp_fba_get_question_for_tablet_default $organization_id");
        $question_default =  $this->get_other_for_question($questions)[0];

        // lấy danh sách câu hỏi
        $questions = DB::select("exec sp_fba_get_question_for_tablet $organization_id, $location_id");
        $question_arr =  $this->get_other_for_question($questions);

        // Lấy Cấu hình của ứng dụng
        $apps = DB::select("exec sp_fba_application_setting_for_tablet $organization_id");
        $app_seting = array();
        if ($apps) {
            $organization_name = '';
            $site_id = 0;
            $site_name = '';
            $location_name = '';
            // if($serial_number != null){
            //     $tablet_details = DB::select("EXEC sp_get_tablet_details_location_site_organization  $serial_number");

            //     if($tablet_details)
            //     {
            //         $organization_name = $tablet_details[0]->organization_name ?  $tablet_details[0]->organization_name : '';

            //         $site_id = $tablet_details[0]->site_id ?  (int)$tablet_details[0]->site_id : 0;
            //         $site_name = $tablet_details[0]->site_name ?  $tablet_details[0]->site_name : '';

            //         $location_name = $tablet_details[0]->location_name ?  $tablet_details[0]->location_name : '';
            //     }
            // }

            $folder = public_path('/images/fba/');
            $app = $apps[0];
            $app_seting = array(
                'id' => $app->id, 'organization_id' => $organization_id, 'organization_name' => $organization_name, 'site_id' => $site_id, 'site_name' => $site_name, 'location_id' => $location_id, 'location_name' => $location_name

                // , 'company_logo' => base64_encode(file_get_contents($folder.$app->company_logo))
                // , 'application_logo' => base64_encode(file_get_contents($folder.$app->application_logo))

                , 'company_logo' => $this->get_image($app->company_logo), 'application_logo' =>  $this->get_image($app->application_logo), 'login_title' => $app->login_title, 'login_txt_username' => $app->login_txt_username, 'login_txt_password' => $app->login_txt_password, 'login_btn_signin' => $app->login_btn_signin, 'finish_message' => $app->finish_message, 'finish_message2' => $app->finish_message2, 'reason_title' => $app->reason_title, 'reason_other_title' => $app->reason_other_title, 'reason_other_highligt' => $app->reason_other_highligt, 'btn_cancel' => $app->btn_cancel, 'btn_send' => $app->btn_send, 'customer_info_title' => $app->customer_info_title, 'customer_info_name' => $app->customer_info_name, 'customer_info_phone' => $app->customer_info_phone, 'customer_info_email' => $app->customer_info_email, 'customer_info_name_require' => (bool) $app->customer_info_name_require, 'customer_info_phone_require' => (bool) $app->customer_info_phone_require, 'customer_info_email_require' => (bool) $app->customer_info_email_require, 'customer_info_any_require' => (bool) $app->customer_info_any_require, 'device_info_time_out' => (int) $app->device_info_time_out
            );
        }

        return array(
            'app_setting' =>  $app_seting, 'questions' => $question_arr, 'question_default' => $question_default
        );
    }
    private function get_other_for_question($questions)
    {
        // lấy thư mục ảnh
        $folder = public_path('/images/fba/');

        // khởi tạo mảng dữ liệu câu hỏi
        $question_arr = array();
        if ($questions != null) {
            foreach ($questions as $question) {
                $question_id = (int) $question->id;
                $smile_touch_layouts = DB::select("exec sp_fba_get_smile_touch_layout_by_question_id $question_id");
                $smile_touch_layout_arr = array();
                if ($smile_touch_layouts != null) {
                    foreach ($smile_touch_layouts as $layout) {
                        $smile_touch_layout_arr[] = array(
                            'id' => $layout->id, 'name' => $layout->name, 'orderby' => (int) $layout->orderby
                        );
                    }
                }

                $reasons = DB::select("select * from fba_reasons where actived = 1 and question_id = $question_id");
                $reason_arr = array();
                if ($reasons != null) {
                    foreach ($reasons as $reason) {
                        $reason_arr[] = array(
                            'reason_id' => (int) $reason->id, 'question_id' => (int) $reason->question_id, 'organization_id' => (int) $reason->organization_id, 'reason_name' => $reason->reason_name, 'reason_img' => base64_encode(file_get_contents($folder . $reason->reason_img))
                        );
                    }
                }

                $question_arr[] = array(
                    'question_id' => (int) $question->id, 'organization_id' => (int) $question->organization_id, 'start_time' => $question->start_time, 'end_time' => $question->end_time, 'question_name' => $question->question_name, 'very_negative' => $question->very_negative, 'negative' => $question->negative, 'positive' => $question->positive, 'very_positive' => $question->very_positive, 'answer_label_visit' => (bool) $question->answer_label_visit, 'very_negative_popup' => (int) $question->very_negative_popup, 'negative_popup' => (int) $question->negative_popup, 'positive_popup' => (int) $question->positive_popup, 'very_positive_popup' => (int) $question->very_positive_popup, 'other_popup' => (int) $question->other_popup, 'is_default' => (bool) $question->is_default, 'very_negative_img' => base64_encode(file_get_contents($folder . $question->very_negative_img)), 'negative_img' => base64_encode(file_get_contents($folder . $question->negative_img)), 'positive_img' => base64_encode(file_get_contents($folder . $question->positive_img)), 'very_positive_img' => base64_encode(file_get_contents($folder . $question->very_positive_img)), 'layouts' => $smile_touch_layout_arr, 'reasons' => $reason_arr, 'popup_time_out' =>  (int) $question->popup_time_out // Thoi gian timeout của các màn hình
                    , 'reason_time_out' =>  (int) $question->reason_time_out, 'reason_other_time_out' =>  (int) $question->reason_other_time_out, 'customer_info_time_out' =>  (int) $question->customer_info_time_out, 'finish_time_out' =>  (int) $question->finish_time_out, 'data_version' =>  (int) $question->data_version
                );
            }
        }
        return $question_arr;
    }

    public function get_question_for_report(Request $request)
    {
        $request_user = $request->user();

        $user_id = $request_user->id;
        $organization_id = $request->organization_id;

        $questions_ongoing = DB::select("exec sp_fba_get_question_for_report $organization_id, N'ongoing'");
        $questions_ongoing_arr = $this->get_question_for_report_detail($questions_ongoing);

        $questions_ended = DB::select("exec sp_fba_get_question_for_report $organization_id, N'ended'");
        $questions_ended_arr = $this->get_question_for_report_detail($questions_ended);

        $questions_upcoming = DB::select("exec sp_fba_get_question_for_report $organization_id, N'upcoming'");
        $questions_upcoming_arr = $this->get_question_for_report_detail($questions_upcoming);


        return response()->json(array(
            'questions_ongoing' => $questions_ongoing_arr,
            'questions_ended' => $questions_ended_arr,
            'questions_upcoming' => $questions_upcoming_arr,
        ));
    }
    private function get_question_for_report_detail($questions)
    {
        $folder = public_path('/images/fba/');
        $question_arr = array();
        foreach ($questions as $question) {
            $positive_img =  file_exists($folder . $question->positive_img) ?  $question->positive_img : 'no_image.png';
            $very_positive_img =  file_exists($folder . $question->very_positive_img) ?  $question->very_positive_img : 'no_image.png';
            $negative_img =  file_exists($folder . $question->negative_img) ?  $question->negative_img : 'no_image.png';
            $very_negative_img =  file_exists($folder . $question->very_negative_img) ?  $question->very_negative_img : 'no_image.png';

            $question_arr[] = array(
                'question_id' => (int) $question->id,
                'organization_id' => (int) $question->organization_id,
                'start_time' => $question->start_time,
                'end_time' => $question->end_time,
                'question_name' => $question->question_name,
                'campaign_name' => $question->campaign_name,
                'very_negative' => $question->very_negative,
                'negative' => $question->negative,
                'positive' => $question->positive,
                'very_positive' => $question->very_positive,
                'very_negative_img' => base64_encode(file_get_contents($folder . $very_negative_img)),
                'negative_img' => base64_encode(file_get_contents($folder . $negative_img)),
                'positive_img' => base64_encode(file_get_contents($folder . $positive_img)),
                'very_positive_img' => base64_encode(file_get_contents($folder . $very_positive_img))

            );
        }
        return $question_arr;
    }
    private function get_image($image)
    {
        $folder = public_path('/images/fba/');
        // return base64_encode(file_get_contents($folder.$app->company_logo));
        if (file_exists($folder . $image)) {
            return base64_encode(file_get_contents($folder . $image));
        } else {
            return base64_encode(file_get_contents($folder . 'no_image.png'));
        }
    }


    //

    public function tablet_get_data_v2(Request $request)
    {
        $organization_id = 0;
        $location_id = 0;

        // Nếu có serial_number
        if ($request->has('serial_number')) {
            $serial_number = $request->serial_number;

            // Lấy thông tin của thiết bị dựa vào serial_number
            $tablets =  FbaTablet::where('serial_number', $serial_number)->get();
            if (count($tablets) < 1) {
                $tablet = new FbaTablet;
                $tablet->organization_id = $organization_id;
                $tablet->location_id = $location_id;
                $tablet->serial_number = $serial_number;
                $tablet->imei = $serial_number;
                $tablet->actived = 0;

                $tablet->save();
            }

            if (count($tablets) > 0) {
                $tablet = $tablets[0];
                $organization_id = (int) $tablet->organization_id;
                $location_id = (int) $tablet->location_id;
            }

            // $tablets =  DB::select("exec sp_fba_tablets_get_by_serial_number $serial_number");
            // Nếu chưa có có thiết bị chèn mới vào CSDL
            // if(!$tablets){
            //     $tablet = new FbaTablet;
            //     $tablet->organization_id = $organization_id;
            //     $tablet->location_id = $location_id;
            //     $tablet->serial_number = $serial_number;
            //     $tablet->imei = $serial_number;
            //     $tablet->actived = 0;

            //     $tablet->save();
            // }

            // if($tablets){
            //     $tablet = $tablets[0];
            //     $organization_id = (int)$tablet->organization_id;
            //     $location_id = (int)$tablet->location_id;
            // }
        }

        if ($request->has('organization_id'))
            $organization_id = (int) $request->organization_id;
        if ($request->has('location_id'))
            $location_id = (int) $request->location_id;

        return response()->json($this->get_data_v2($organization_id, $location_id));
    }
    private function get_data_v2($organization_id, $location_id)
    {
        // lấy câu hỏi mặc định
        $questions = DB::select("exec sp_fba_get_question_for_tablet_default $organization_id");
        $question_default =  $this->get_other_for_question_v2($questions)[0];

        // lấy danh sách câu hỏi
        $questions = DB::select("exec sp_fba_get_question_for_tablet $organization_id, $location_id");
        $question_arr =  $this->get_other_for_question_v2($questions);

        // Lấy Cấu hình của ứng dụng
        $apps = DB::select("exec sp_fba_application_setting_for_tablet $organization_id");
        $app_seting = array();
        if ($apps) {
            $organization_name = '';
            $site_id = 0;
            $site_name = '';
            $location_name = '';
            // if($serial_number != null){
            //     $tablet_details = DB::select("EXEC sp_get_tablet_details_location_site_organization  $serial_number");

            //     if($tablet_details)
            //     {
            //         $organization_name = $tablet_details[0]->organization_name ?  $tablet_details[0]->organization_name : '';

            //         $site_id = $tablet_details[0]->site_id ?  (int)$tablet_details[0]->site_id : 0;
            //         $site_name = $tablet_details[0]->site_name ?  $tablet_details[0]->site_name : '';

            //         $location_name = $tablet_details[0]->location_name ?  $tablet_details[0]->location_name : '';
            //     }
            // }

            $folder = public_path('/images/fba/');
            $app = $apps[0];
            $app_seting = array(
                'id' => $app->id, 'organization_id' => $organization_id, 'organization_name' => $organization_name, 'site_id' => $site_id, 'site_name' => $site_name, 'location_id' => $location_id, 'location_name' => $location_name

                // , 'company_logo' => base64_encode(file_get_contents($folder.$app->company_logo))
                // , 'application_logo' => base64_encode(file_get_contents($folder.$app->application_logo))

                , 'company_logo' => $this->get_image($app->company_logo),
                'application_logo' =>  $this->get_image($app->application_logo),
                'login_title' => $app->login_title,
                'login_txt_username' => $app->login_txt_username,
                'login_txt_password' => $app->login_txt_password,
                'login_btn_signin' => $app->login_btn_signin,
                'finish_message' => $app->finish_message,
                'finish_message2' => $app->finish_message2,
                'reason_title' => $app->reason_title,
                'reason_other_title' => $app->reason_other_title,
                'reason_other_highligt' => $app->reason_other_highligt,
                'btn_cancel' => $app->btn_cancel,
                'btn_send' => $app->btn_send,
                'customer_info_title' => $app->customer_info_title,
                'customer_info_name' => $app->customer_info_name,
                'customer_info_phone' => $app->customer_info_phone,
                'customer_info_email' => $app->customer_info_email,
                'customer_info_name_require' => (bool) $app->customer_info_name_require,
                'customer_info_phone_require' => (bool) $app->customer_info_phone_require,
                'customer_info_email_require' => (bool) $app->customer_info_email_require,
                'customer_info_any_require' => (bool) $app->customer_info_any_require,
                'device_info_time_out' => (int) $app->device_info_time_out,
                'actived_cancel' => (int) $app->actived_cancel
            );
        }

        return array(
            'app_setting' =>  $app_seting, 'questions' => $question_arr, 'question_default' => $question_default
        );
    }
    private function get_other_for_question_v2($questions)
    {
        // lấy thư mục ảnh
        $folder = public_path('/images/fba/');

        // khởi tạo mảng dữ liệu câu hỏi
        $question_arr = array();
        if ($questions != null) {
            foreach ($questions as $question) {
                $question_id = (int) $question->id;
                $smile_touch_layouts = DB::select("exec sp_fba_get_smile_touch_layout_by_question_id_v2 $question_id");
                $smile_touch_layout_arr = array();
                if ($smile_touch_layouts != null) {
                    foreach ($smile_touch_layouts as $layout) {
                        $smile_touch_layout_arr[] = array(
                            'id' => $layout->id,
                            'question_id' => $layout->question_id,
                            'answer_id' => $layout->answer_id,
                            'layout_id' => $layout->layout_id,
                            'actived' => $layout->actived,
                            'name' => $layout->name,
                            'orderby' => (int) $layout->orderby
                        );
                    }
                }

                $reasons = DB::select("select * from fba_reasons where actived = 1 and question_id = $question_id");
                $reason_arr = array();
                if ($reasons != null) {
                    foreach ($reasons as $reason) {
                        $reason_img =  file_exists($folder . $reason->reason_img) ?  $reason->reason_img : 'no_image.png';
                        $reason_arr[] = array(
                            'reason_id' => (int) $reason->id,
                            'question_id' => (int) $reason->question_id,
                            'organization_id' => (int) $reason->organization_id,
                            'reason_name' => $reason->reason_name,
                            'reason_img' => base64_encode(file_get_contents($folder . $reason_img))
                        );
                    }
                }

                $positive_img =  file_exists($folder . $question->positive_img) ?  $question->positive_img : 'no_image.png';
                $very_positive_img =  file_exists($folder . $question->very_positive_img) ?  $question->very_positive_img : 'no_image.png';
                $negative_img =  file_exists($folder . $question->negative_img) ?  $question->negative_img : 'no_image.png';
                $very_negative_img =  file_exists($folder . $question->very_negative_img) ?  $question->very_negative_img : 'no_image.png';

                $question_arr[] = array(
                    'question_id' => (int) $question->id,
                    'organization_id' => (int) $question->organization_id,
                    'start_time' => $question->start_time,
                    'end_time' => $question->end_time,
                    'question_name' => $question->question_name,
                    'very_negative' => $question->very_negative,
                    'negative' => $question->negative,
                    'positive' => $question->positive,
                    'very_positive' => $question->very_positive,
                    'answer_label_visit' => (bool) $question->answer_label_visit, 'very_negative_popup' => (int) $question->very_negative_popup,
                    'negative_popup' => (int) $question->negative_popup,
                    'positive_popup' => (int) $question->positive_popup,
                    'very_positive_popup' => (int) $question->very_positive_popup,
                    'other_popup' => (int) $question->other_popup,
                    'is_default' => (bool) $question->is_default,

                    'very_negative_img' => base64_encode(file_get_contents($folder . $very_negative_img)),
                    'negative_img' => base64_encode(file_get_contents($folder . $negative_img)),
                    'positive_img' => base64_encode(file_get_contents($folder . $positive_img)),
                    'very_positive_img' => base64_encode(file_get_contents($folder . $very_positive_img)),

                    'layouts' => $smile_touch_layout_arr,
                    'reasons' => $reason_arr,
                    'popup_time_out' =>  (int) $question->popup_time_out // Thoi gian timeout của các màn hình
                    , 'reason_time_out' =>  (int) $question->reason_time_out,
                    'reason_other_time_out' =>  (int) $question->reason_other_time_out,
                    'customer_info_time_out' =>  (int) $question->customer_info_time_out,
                    'finish_time_out' =>  (int) $question->finish_time_out,
                    'data_version' =>  (int) $question->data_version
                );
            }
        }
        return $question_arr;
    }
    //
    /*-----------Huy thêm  30/11/2018---------------------------*/
    /*-----------Lấy danh sách câu hỏi--------------------------*/
    public function sp_ad_question(Request $request)
    {
        $request_user = $request->user();
        $user_id = $request_user->id;
        $organization_id = $request->organization_id;
        $viewby =  $request->viewby;
        $deleted =  $request->deleted;
        $question = DB::select("exec sp_fba_get_question_time $user_id, $organization_id,$viewby,$deleted");
        $questions_arr = $this->get_question_detail($question);

        return response()->json(array('get_question' => $questions_arr));
    }
    /*-----------Huy:1/12 Lấy câu hỏi thứ nhất và lý do thứ nhất---*/
    public function get_status_question_default()
    {
        $status_question = array(
            // array('label' => 'Tất cả câu hỏi', 'value'=> '0'),
            array('label' => 'Sắp diễn ra', 'value' => '1'),
            array('label' => 'Đang diễn ra', 'value' => '2'),
            array('label' => 'Kết thúc', 'value' => '3'),
        );
        $deleted_question = array(
            // array('label' => 'Tất cả câu hỏi', 'value'=> '2'),
            array('label' => 'Đang hoạt động', 'value' => '0'),
            array('label' => 'Đã xóa', 'value' => '1'),
        );
        $status_question_en = array(
            // array('label' => 'Tất cả câu hỏi', 'value'=> '0'),
            array('label' => 'Upcoming', 'value' => '1'),
            array('label' => 'Happenning', 'value' => '2'),
            array('label' => 'Ending', 'value' => '3'),
        );
        $deleted_question_en = array(
            // array('label' => 'Tất cả câu hỏi', 'value'=> '2'),
            array('label' => 'Actived', 'value' => '0'),
            array('label' => 'Deleted', 'value' => '1'),

        );

        $question =  FbaQuestion::where('id', 1)->get();
        $questions_arr = $this->get_question_detail($question);

        return response()->json(
            array(
                'status_question' => $status_question, 'get_question' => $questions_arr,
                'status_question_en' => $status_question_en,
                'deleted_question' => $deleted_question,
                'deleted_question_en' => $deleted_question_en
            )
        );
    }
    /*-----------Huy:3/12 lấy thông tin câu hỏi cần sửa------*/
    public function get_question_edit(Request $request)
    {
        $id = $request->id;
        $question =  FbaQuestion::where('id', $id)->get();
        $questions_arr = $this->get_question_detail($question);
        return response()->json(array(
            'get_question' => $questions_arr
        ));
    }

    /*-----------Huy:8/12 Xóa câu hỏi------------------------*/
    public function delete_question(Request $request)
    {
        $request_user = $request->user();
        $user_id = $request_user->id;
        $question_id = $request->id;
        $deleted = $request->deleted;
        $question = DB::select("exec sp_fba_question_delete $user_id, $question_id,$deleted");
        return response()->json($question);
    }

    // Lấy base64 ảnh question, các lý do của câu hỏi
    private function get_question_detail($questions)
    {
        // lấy thư mục ảnh
        $folder = public_path('/images/fba/');

        // khởi tạo mảng dữ liệu câu hỏi
        $question_arr = array();
        if ($questions != null) {
            foreach ($questions as $question) {
                $id = (int) $question->id;

                $reasons = DB::select("select * from fba_reasons where question_id = $id");
                //  actived = 1 and
                $reason_arr = array();
                if ($reasons != null) {
                    foreach ($reasons as $reason) {
                        $reason_arr[] = array(
                            'reason_id' => (int) $reason->id, 'question_id' => (int) $reason->question_id, 'organization_id' => (int) $reason->organization_id, 'actived' => (bool) $reason->actived, 'status' => (int) $reason->status, 'reason_name' => $reason->reason_name, 'reason_img' => base64_encode(file_get_contents($folder . $reason->reason_img))
                        );
                    }
                }
                $smile_answer_touch_layout = DB::select("exec sp_fba_get_answer_smile_touch_layout_by_question_id $id");
                $smile_answer_touch_layout_arr = array();
                if ($smile_answer_touch_layout != null) {
                    foreach ($smile_answer_touch_layout as $layout) {
                        $smile_answer_touch_layout_arr[] = array(
                            'id' => $layout->id, 'answer_id' => $layout->answer_id, 'smile_touch_layout_id' => $layout->smile_touch_layout_id, 'actived' => (bool) $layout->actived
                        );
                    }
                }

                $question_arr[] = array(
                    'id' => (int) $question->id, 'organization_id' => (int) $question->organization_id, 'start_time' => $question->start_time, 'end_time' => $question->end_time, 'question_name' => $question->question_name, 'campaign_name' => $question->campaign_name, 'very_negative' => $question->very_negative, 'negative' => $question->negative, 'positive' => $question->positive, 'very_positive' => $question->very_positive, 'very_negative_popup' => (int) $question->very_negative_popup, 'negative_popup' => (int) $question->negative_popup, 'positive_popup' => (int) $question->positive_popup, 'very_positive_popup' => (int) $question->very_positive_popup, 'deleted' => (int) $question->deleted, 'answer_label_visit' => (bool) $question->answer_label_visit, 'other_popup' => (bool) $question->other_popup, 'is_default' => (bool) $question->is_default, 'actived' => (bool) $question->actived, 'status' => (bool) $question->status, 'very_negative_img' => base64_encode(file_get_contents($folder . $question->very_negative_img)), 'negative_img' => base64_encode(file_get_contents($folder . $question->negative_img)), 'positive_img' => base64_encode(file_get_contents($folder . $question->positive_img)), 'very_positive_img' => base64_encode(file_get_contents($folder . $question->very_positive_img)), 'reasons' => $reason_arr, 'smile_answer_touch_layout_arr' => $smile_answer_touch_layout_arr, 'popup_time_out' =>  (int) $question->popup_time_out // Thoi gian timeout của các màn hình
                    , 'reason_time_out' =>  (int) $question->reason_time_out, 'reason_other_time_out' =>  (int) $question->reason_other_time_out, 'customer_info_time_out' =>  (int) $question->customer_info_time_out, 'finish_time_out' =>  (int) $question->finish_time_out, 'data_version' =>  (int) $question->data_version
                );
            }
        }
        return $question_arr;
    }
    /*-----------Huy:1/12 lấy base 64 ảnh reason-------------*/
    private function get_reason_detail($reasons)
    {
        $folder = public_path('/images/fba/');
        $reason_arr = array();
        foreach ($reasons as $reason) {
            $reason_arr[] = array(
                'id' => (int) $reason->id, 'question_id' => (int) $reason->question_id, 'organization_id' => (int) $reason->organization_id, 'status' => $reason->status, 'actived' => $reason->actived, 'reason_name' => $reason->reason_name, 'reason_img' => base64_encode(file_get_contents($folder . $reason->reason_img))
            );
        }
        return $reason_arr;
    }
    /*-------------Huy:1/12 Thêm question-------------*/
    public function insert_question(Request $request)
    {
        DB::beginTransaction();
        $action_result = 0;
        try {
            // // Dữ liệu submit gửi về
            $request_user = $request->user();
            $data = json_decode($request->data);
            $question_id =  $data->id_question;
            $user_id = $request_user->id;
            // // return response()->json(['return_data'=>$data]);

            // // Check ảnh đánh giá có thay đổi hay không
            if ($request->hasFile('very_positive_img')) {
                $file = $request->file('very_positive_img');
                $extension = $file->getClientOriginalName();
                $very_positive_image = $extension;
                $path = public_path('/images/fba/');
                $upload = $file->move($path, $very_positive_image);
            } else {
                $very_positive_image = 'very_positive.png';
            }
            if ($request->hasFile('positive_img')) {
                $file = $request->file('positive_img');
                $extension = $file->getClientOriginalName();
                $positive_image = $extension;
                $path = public_path('/images/fba/');
                $upload = $file->move($path, $positive_image);
            } else {
                $positive_image = 'positive.png';
            }
            if ($request->hasFile('very_negative_img')) {
                $file = $request->file('very_negative_img');
                $extension = $file->getClientOriginalName();
                $very_negative_image = $extension;
                $path = public_path('/images/fba/');
                $upload = $file->move($path, $very_negative_image);
            } else {
                $very_negative_image = 'very_negative.png';
            }
            if ($request->hasFile('negative_img')) {
                $file = $request->file('negative_img');
                $extension = $file->getClientOriginalName();
                $negative_image = $extension;
                $path = public_path('/images/fba/');
                $upload = $file->move($path, $negative_image);
            } else {
                $negative_image = 'negative.png';
            }

            // Check ảnh lý do có thay đổi hay không
            $i = 0;
            $loop = array('1', '2', '3', '4', '5', '6', '7', '8', '9');
            foreach ($loop as $row) {
                $i++;
                if ($request->hasFile('reason_img' . $i)) {
                    $file = $request->file('reason_img' . $i);
                    $extension = $file->getClientOriginalName();
                    ${'reason_image' . $i}  = $extension;
                    $path = public_path('/images/fba/');
                    $upload = $file->move($path, ${'reason_image' . $i});
                    // Nghĩa thêm đoạn này để resize ảnh
                    // $thumbnailpath = $path . "$extension";
                    // $img = Image::make($thumbnailpath)->resize(150, 150)->save($thumbnailpath);
                    // end nghĩa
                }
            }

            // // Lấy ảnh câu hỏi theo câu hỏi đã chọn
            $fbaquestion = FbaQuestion::where('id', $question_id)->get();
            $i = 0;
            foreach ($fbaquestion as $row) {
                $i++;
                $negative_img = $row->negative_img;
                $very_negative_img = $row->very_negative_img;
                $positive_img = $row->positive_img;
                $very_positive_img = $row->very_positive_img;
            }

            // Lấy ảnh lý do theo câu hỏi đã chọn
            $fbareason = FbaReason::where('question_id', $question_id)->select('id', 'reason_img')->get();
            $i = 0;
            foreach ($fbareason as $row) {
                $i++;
                ${'image' . $i} = $row->reason_img;
            }

            // //Thực hiện thêm mới
            $question =  new FbaQuestion;
            $question->organization_id =  $data->organization_id;
            $question->question_name =  $data->question_name;
            $question->campaign_name =  $data->campaign_name;
            $question->start_time =  $data->start_time;
            $question->end_time =  $data->end_time;
            $question->created_by =  $user_id;

            $question->very_negative =  $data->very_negative;
            $question->negative =  $data->negative;
            $question->very_positive =  $data->very_positive;
            $question->positive =  $data->positive;
            if ($request->hasFile('very_positive_img')) {
                $question->very_positive_img =  $very_positive_image;
            } else {
                $question->very_positive_img =  $very_positive_img;
            }

            if ($request->hasFile('positive_img')) {
                $question->positive_img = $positive_image;
            } else {
                $question->positive_img = $positive_img;
            }

            if ($request->hasFile('very_negative_img')) {
                $question->very_negative_img = $very_negative_image;
            } else {
                $question->very_negative_img = $very_negative_img;
            }

            if ($request->hasFile('negative_img')) {
                $question->negative_img = $negative_image;
            } else {
                $question->negative_img = $negative_img;
            }

            if ($data->actived_vp_reason || $data->actived_vp_other || $data->actived_vp_info) {
                $question->very_positive_popup = true;
            } else {
                $question->very_positive_popup = false;
            }
            if ($data->actived_p_reason || $data->actived_p_other || $data->actived_p_info) {
                $question->positive_popup = true;
            } else {
                $question->positive_popup = false;
            }
            if ($data->actived_vn_reason || $data->actived_vn_other || $data->actived_vn_info) {
                $question->very_negative_popup =  true;
            } else {
                $question->very_negative_popup =  false;
            }
            if ($data->actived_n_reason || $data->actived_n_other || $data->actived_n_info) {
                $question->negative_popup =  true;
            } else {
                $question->negative_popup =  false;
            }

            // $question->is_default=  $data->is_default;
            // $question->data_version=  $data->data_version;
            // $question->other_popup=  $data->other_popup;
            // $question->popup_time_out=  $data->popup_time_out;
            $question->answer_label_visit =  $data->answer_label_visit;
            $question->actived =  $data->actived;

            $question->reason_time_out =  $data->reason_time_out;
            $question->reason_other_time_out =  $data->reason_other_time_out;
            $question->customer_info_time_out =  $data->customer_info_time_out;
            $question->finish_time_out =  $data->finish_time_out;
            $question->save();
            $a = $question->id;
            // Thêm smile touch
            // vòng 1
            $this->insert_answer_smile_touch_layout($question->id, 'very_positive', 'reason', 1, $data->actived_vp_reason);
            $this->insert_answer_smile_touch_layout($question->id, 'very_positive', 'reason_other', 2, $data->actived_vp_other);
            $this->insert_answer_smile_touch_layout($question->id, 'very_positive', 'customer_info', 3, $data->actived_vp_info);
            // vòng 2
            $this->insert_answer_smile_touch_layout($question->id, 'positive', 'reason', 1, $data->actived_p_reason);
            $this->insert_answer_smile_touch_layout($question->id, 'positive', 'reason_other', 2, $data->actived_p_other);
            $this->insert_answer_smile_touch_layout($question->id, 'positive', 'customer_info', 3, $data->actived_p_info);
            // vòng 3
            $this->insert_answer_smile_touch_layout($question->id, 'negative', 'reason', 1, $data->actived_n_reason);
            $this->insert_answer_smile_touch_layout($question->id, 'negative', 'reason_other', 2, $data->actived_n_other);
            $this->insert_answer_smile_touch_layout($question->id, 'negative', 'customer_info', 3, $data->actived_n_info);
            // vòng 4
            $this->insert_answer_smile_touch_layout($question->id, 'very_negative', 'reason', 1, $data->actived_vn_reason);
            $this->insert_answer_smile_touch_layout($question->id, 'very_negative', 'reason_other', 2, $data->actived_vn_other);
            $this->insert_answer_smile_touch_layout($question->id, 'very_negative', 'customer_info', 3, $data->actived_vn_info);
            // //  Thực hiện thêm mới lý do
            $i = 0;
            $loop = array('1', '2', '3', '4', '5', '6', '7', '8', '9');
            foreach ($loop as $row) {
                $i++;
                if (isset($data->{'reason_name' . $i}) && $data->{'reason_name' . $i} != null) {
                    ${'reason' . $i} = new FbaReason;
                    ${'reason' . $i}->reason_name = $data->{'reason_name' . $i};
                    if ($request->hasFile('reason_img' . $i)) {
                        ${'reason' . $i}->reason_img = ${'reason_image' . $i};
                    } else {
                        ${'reason' . $i}->reason_img = ${'image' . $i};
                    }
                    ${'reason' . $i}->question_id = $question->id;
                    ${'reason' . $i}->organization_id = $data->organization_id;
                    if (isset($data->{'actived_reason' . $i})) {
                        ${'reason' . $i}->actived = $data->{'actived_reason' . $i};
                    } else {
                        ${'reason' . $i}->actived = false;
                    }
                    ${'reason' . $i}->save();
                }
            }
            $action_result = 1;
            DB::commit();
        } catch (\Exception $e) {
            $action_result = 0;
            DB::rollback();
        }
        return response()->json([
            'status' => $action_result
        ]);
    }
    function insert_answer_smile_touch_layout($question_id, $answer_id, $smile_touch_layout_id, $orderby, $actived)
    {
        $smile = DB::table('fba_question_answer_smile_touch_layout')->insert([
            'question_id' =>  $question_id,
            'answer_id' => $answer_id,
            'smile_touch_layout_id' => $smile_touch_layout_id,
            'orderby' => $orderby,
            'actived' => $actived,
        ]);
    }
    /*-----------Huy:1/12 Cập nhật question------------------*/
    function update_question(Request $request)
    {
        DB::beginTransaction();
        $action_result = 0;
        try {
            // // Dữ liệu submit gửi về
            $data = json_decode($request->data);
            $request_user = $request->user();
            $user_id = $request_user->id;
            $id = $data->id;
            $or = $data->organization_id;
            $access_token = $request->token;
            // // Check ảnh đánh giá có thay đổi hay không
            if ($request->hasFile('very_positive_img')) {
                $file = $request->file('very_positive_img');
                $extension = $file->getClientOriginalName();
                $very_positive_image = $extension;
                $path = public_path('/images/fba/');
                $upload = $file->move($path, $very_positive_image);
            } else {
                $very_positive_image = 'very_positive.png';
            }
            if ($request->hasFile('positive_img')) {
                $file = $request->file('positive_img');
                $extension = $file->getClientOriginalName();
                $positive_image = $extension;
                $path = public_path('/images/fba/');
                $upload = $file->move($path, $positive_image);
            } else {
                $positive_image = 'positive.png';
            }
            if ($request->hasFile('very_negative_img')) {
                $file = $request->file('very_negative_img');
                $extension = $file->getClientOriginalName();
                $very_negative_image = $extension;
                $path = public_path('/images/fba/');
                $upload = $file->move($path, $very_negative_image);
            } else {
                $very_negative_image = 'very_negative.png';
            }
            if ($request->hasFile('negative_img')) {
                $file = $request->file('negative_img');
                $extension = $file->getClientOriginalName();
                $negative_image = $extension;
                $path = public_path('/images/fba/');
                $upload = $file->move($path, $negative_image);
            } else {
                $negative_image = 'negative.png';
            }

            //  // Check ảnh lý do có thay đổi hay không
            $i = 0;
            $loop = array('1', '2', '3', '4', '5', '6', '7', '8', '9');
            foreach ($loop as $row) {
                $i++;
                // cần thêm ảnh trắng để gọi ra không sẽ lỗi nếu người dùng thêm lý do quên không chọn ảnh
                ${'image' . $i} = 'no_image.png';
                if ($request->hasFile('reason_img' . $i)) {
                    $file = $request->file('reason_img' . $i);
                    $extension = $file->getClientOriginalName();
                    ${'reason_image' . $i}  = $extension;
                    $path = public_path('/images/fba/');
                    $upload = $file->move($path, ${'reason_image' . $i});
                    // Nghĩa thêm đoạn này để resize ảnh
                    // $thumbnailpath = $path . "$extension";
                    // $img = Image::make($thumbnailpath)->resize(150, 150)->save($thumbnailpath);
                    // end nghĩa
                }
            }
            // //Thực hiện cập nhật câu hỏi theo id
            $question =  FbaQuestion::find($id);
            $question->organization_id =  $data->organization_id;
            $question->question_name =  $data->question_name;
            $question->campaign_name =  $data->campaign_name;
            $question->start_time =  $data->start_time;
            $question->end_time =  $data->end_time;
            $question->created_by =  $user_id;

            $question->very_negative =  $data->very_negative;
            $question->negative =  $data->negative;
            $question->very_positive =  $data->very_positive;
            $question->positive =  $data->positive;

            if ($request->hasFile('very_negative_img')) {
                $question->very_negative_img =  $very_negative_image;
            }
            if ($request->hasFile('negative_img')) {
                $question->negative_img =  $negative_image;
            }
            if ($request->hasFile('very_positive_img')) {
                $question->very_positive_img =  $very_positive_image;
            }
            if ($request->hasFile('positive_img')) {
                $question->positive_img =  $positive_image;
            }

            if ($data->actived_vp_reason || $data->actived_vp_other || $data->actived_vp_info)
                $question->very_positive_popup = true;
            else
                $question->very_positive_popup = false;

            if ($data->actived_p_reason || $data->actived_p_other || $data->actived_p_info) {
                $question->positive_popup = true;
            } else {
                $question->positive_popup = false;
            }
            if ($data->actived_vn_reason || $data->actived_vn_other || $data->actived_vn_info) {
                $question->very_negative_popup =  true;
            } else {
                $question->very_negative_popup =  false;
            }
            if ($data->actived_n_reason || $data->actived_n_other || $data->actived_n_info) {
                $question->negative_popup =  true;
            } else {
                $question->negative_popup =  false;
            }

            $question->answer_label_visit =  $data->answer_label_visit;
            // $question->other_popup=  $data->other_popup;
            // $question->is_default=  $data->is_default;
            // $question->data_version=  $data->data_version;
            // $question->popup_time_out=  $data->popup_time_out;
            $question->actived =  $data->actived;

            $question->reason_time_out =  $data->reason_time_out;
            $question->reason_other_time_out =  $data->reason_other_time_out;
            $question->customer_info_time_out =  $data->customer_info_time_out;
            $question->finish_time_out =  $data->finish_time_out;
            $question->save();
            // // Lấy id, ảnh lý do theo id question
            $fbareason = FbaReason::where('question_id', $id)->select('id', 'reason_img')->get();
            $i = 0;
            foreach ($fbareason as $row) {
                $i++;
                ${'image' . $i} = $row->reason_img;
                ${'id_res' . $i} = $row->id;
            }
            // Thực hiện cập nhật, thêm mới, xóa nếu lý do có biến đổi
            $i = 0;
            $loop = array('1', '2', '3', '4', '5', '6', '7', '8', '9');
            foreach ($loop as $row) {
                $i++;
                if (isset($data->{'reason_name' . $i}) && $data->{'reason_name' . $i} != null) {
                    if (isset(${'id_res' . $i})) {
                        ${'reason' . $i} = FbaReason::find(${'id_res' . $i});
                        ${'reason' . $i}->reason_name = $data->{'reason_name' . $i};
                        if ($request->hasFile('reason_img' . $i)) {
                            ${'reason' . $i}->reason_img = ${'reason_image' . $i};
                        } else {
                            ${'reason' . $i}->reason_img = ${'image' . $i};
                        }
                        ${'reason' . $i}->question_id = $question->id;
                        ${'reason' . $i}->organization_id = $data->organization_id;
                        if (isset($data->{'actived_reason' . $i})) {
                            ${'reason' . $i}->actived = $data->{'actived_reason' . $i};
                        } else {
                            ${'reason' . $i}->actived = false;
                        }
                        ${'reason' . $i}->save();
                    } else {
                        ${'reason' . $i} = new FbaReason;
                        ${'reason' . $i}->reason_name = $data->{'reason_name' . $i};
                        if ($request->hasFile('reason_img' . $i)) {
                            ${'reason' . $i}->reason_img = ${'reason_image' . $i};
                        } else {
                            ${'reason' . $i}->reason_img = ${'image' . $i};
                        }
                        ${'reason' . $i}->question_id = $question->id;
                        ${'reason' . $i}->organization_id = $data->organization_id;
                        if (isset($data->{'actived_reason' . $i})) {
                            ${'reason' . $i}->actived = $data->{'actived_reason' . $i};
                        } else {
                            ${'reason' . $i}->actived = false;
                        }
                        ${'reason' . $i}->save();
                    }
                } else {
                    if (isset(${'id_res' . $i})) {
                        $reason = FbaReason::find(${'id_res' . $i});
                        $reason->delete();
                    }
                }
            }
            // Thực hiện thêm mới  smile
            // vòng 1
            $this->update_answer_smile_touch_layout($id, 'very_positive', 'reason', 1, $data->actived_vp_reason);
            $this->update_answer_smile_touch_layout($id, 'very_positive', 'reason_other', 2, $data->actived_vp_other);
            $this->update_answer_smile_touch_layout($id, 'very_positive', 'customer_info', 3, $data->actived_vp_info);
            // vòng 2
            $this->update_answer_smile_touch_layout($id, 'positive', 'reason', 1, $data->actived_p_reason);
            $this->update_answer_smile_touch_layout($id, 'positive', 'reason_other', 2, $data->actived_p_other);
            $this->update_answer_smile_touch_layout($id, 'positive', 'customer_info', 3, $data->actived_p_info);
            // vòng 3
            $this->update_answer_smile_touch_layout($id, 'negative', 'reason', 1, $data->actived_n_reason);
            $this->update_answer_smile_touch_layout($id, 'negative', 'reason_other', 2, $data->actived_n_other);
            $this->update_answer_smile_touch_layout($id, 'negative', 'customer_info', 3, $data->actived_n_info);
            // vòng 4
            $this->update_answer_smile_touch_layout($id, 'very_negative', 'reason', 1, $data->actived_vn_reason);
            $this->update_answer_smile_touch_layout($id, 'very_negative', 'reason_other', 2, $data->actived_vn_other);
            $this->update_answer_smile_touch_layout($id, 'very_negative', 'customer_info', 3, $data->actived_vn_info);
            $action_result = 1;
            DB::commit();
            try {
                $tocken_type  = 'Bearer';
                $url_socket = env('URL_SOCKET');
                $socketClient =  new Client(new Version2X($url_socket));
                $socketClient->initialize();
                $socketClient->emit('fba_tablet_reload_data', ['organization_id' => $or, 'tocken_type' => $tocken_type, 'access_token' => $access_token]);              // string array
                $socketClient->close();
                $socket = 'OK';
            } catch (ServerConnectionFailureException $e) {
                $socket = $e;
            }
        } catch (\Exception $e) {
            $socket = '';
            $action_result = 0;
            DB::rollback();
        }
        return response()->json([
            'status' => $action_result,
            'socket' => $socket,
        ]);
    }
    function update_answer_smile_touch_layout($question_id, $answer_id, $smile_touch_layout_id, $orderby, $actived)
    {
        $smile = DB::table('fba_question_answer_smile_touch_layout')
            ->where([['question_id', $question_id], ['answer_id', $answer_id], ['smile_touch_layout_id', $smile_touch_layout_id]])
            ->update(['orderby' => $orderby,  'actived' => $actived]);
    }
}
