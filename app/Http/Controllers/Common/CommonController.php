<?php

namespace App\Http\Controllers\Common;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\DB;

use App\UserPageParametter;
use App\Organization;
use App\UserOrganizationLanguage;
use Carbon\Language;
use Exception;
use App\Site;
use App\UserEmailModule;

class CommonController extends Controller
{
    public $org_table = 'organizations';
    public function get_start_time(&$format_hour = 1)
    {

        $start_time = array(
            array('label' => '12:00 AM', 'value' => '00:00'),
            array('label' => '1:00 AM', 'value' => '1:00'),
            array('label' => '2:00 AM', 'value' => '2:00'),
            array('label' => '3:00 AM', 'value' => '3:00'),
            array('label' => '4:00 AM', 'value' => '4:00'),
            array('label' => '5:00 AM', 'value' => '5:00'),
            array('label' => '6:00 AM', 'value' => '6:00'),
            array('label' => '7:00 AM', 'value' => '7:00'),
            array('label' => '8:00 AM', 'value' => '8:00'),
            array('label' => '9:00 AM', 'value' => '9:00'),
            array('label' => '10:00 AM', 'value' => '10:00'),
            array('label' => '11:00 AM', 'value' => '11:00'),
            array('label' => '12:00 PM', 'value' => '12:00'),
            array('label' => '1:00 PM', 'value' => '13:00'),
            array('label' => '2:00 PM', 'value' => '14:00'),
            array('label' => '3:00 PM', 'value' => '15:00'),
            array('label' => '4:00 PM', 'value' => '16:00'),
            array('label' => '5:00 PM', 'value' => '17:00'),
            array('label' => '6:00 PM', 'value' => '18:00'),
            array('label' => '7:00 PM', 'value' => '19:00'),
            array('label' => '8:00 PM', 'value' => '20:00'),
            array('label' => '9:00 PM', 'value' => '21:00'),
            array('label' => '10:00 PM', 'value' => '22:00'),
            array('label' => '11:00 PM', 'value' => '23:00'),
        );
        if ($format_hour !== 1)
            $start_time = array(
                array('label' => '00:00', 'value' => '00:00'),
                array('label' => '01:00', 'value' => '1:00'),
                array('label' => '02:00', 'value' => '2:00'),
                array('label' => '03:00', 'value' => '3:00'),
                array('label' => '04:00', 'value' => '4:00'),
                array('label' => '05:00', 'value' => '5:00'),
                array('label' => '06:00', 'value' => '6:00'),
                array('label' => '07:00', 'value' => '7:00'),
                array('label' => '08:00', 'value' => '8:00'),
                array('label' => '09:00', 'value' => '9:00'),
                array('label' => '10:00', 'value' => '10:00'),
                array('label' => '11:00', 'value' => '11:00'),
                array('label' => '12:00', 'value' => '12:00'),
                array('label' => '13:00', 'value' => '13:00'),
                array('label' => '14:00', 'value' => '14:00'),
                array('label' => '15:00', 'value' => '15:00'),
                array('label' => '16:00', 'value' => '16:00'),
                array('label' => '17:00', 'value' => '17:00'),
                array('label' => '18:00', 'value' => '18:00'),
                array('label' => '19:00', 'value' => '19:00'),
                array('label' => '20:00', 'value' => '20:00'),
                array('label' => '21:00', 'value' => '21:00'),
                array('label' => '22:00', 'value' => '22:00'),
                array('label' => '23:00', 'value' => '23:00'),
            );
        return $start_time;
    }
    public function get_end_time(&$format_hour = 1)
    {

        $end_time = array(
            array('label' => '12:00 AM', 'value' => '00:59'),
            array('label' => '1:00 AM', 'value' => '1:59'),
            array('label' => '2:00 AM', 'value' => '2:59'),
            array('label' => '3:00 AM', 'value' => '3:59'),
            array('label' => '4:00 AM', 'value' => '4:59'),
            array('label' => '5:00 AM', 'value' => '5:59'),
            array('label' => '6:00 AM', 'value' => '6:59'),
            array('label' => '7:00 AM', 'value' => '7:59'),
            array('label' => '8:00 AM', 'value' => '8:59'),
            array('label' => '9:00 AM', 'value' => '9:59'),
            array('label' => '10:00 AM', 'value' => '10:59'),
            array('label' => '11:00 AM', 'value' => '11:59'),
            array('label' => '12:00 PM', 'value' => '12:59'),
            array('label' => '1:00 PM', 'value' => '13:59'),
            array('label' => '2:00 PM', 'value' => '14:59'),
            array('label' => '3:00 PM', 'value' => '15:59'),
            array('label' => '4:00 PM', 'value' => '16:59'),
            array('label' => '5:00 PM', 'value' => '17:59'),
            array('label' => '6:00 PM', 'value' => '18:59'),
            array('label' => '7:00 PM', 'value' => '19:59'),
            array('label' => '8:00 PM', 'value' => '20:59'),
            array('label' => '9:00 PM', 'value' => '21:59'),
            array('label' => '10:00 PM', 'value' => '22:59'),
            array('label' => '11:00 PM', 'value' => '23:59'),
        );
        if ($format_hour !== 1)
            $end_time = array(
                array('label' => '00:00 ', 'value' => '00:59'),
                array('label' => '01:00 ', 'value' => '1:59'),
                array('label' => '02:00 ', 'value' => '2:59'),
                array('label' => '03:00 ', 'value' => '3:59'),
                array('label' => '04:00 ', 'value' => '4:59'),
                array('label' => '05:00', 'value' => '5:59'),
                array('label' => '06:00', 'value' => '6:59'),
                array('label' => '07:00', 'value' => '7:59'),
                array('label' => '08:00', 'value' => '8:59'),
                array('label' => '09:00', 'value' => '9:59'),
                array('label' => '10:00', 'value' => '10:59'),
                array('label' => '11:00', 'value' => '11:59'),
                array('label' => '12:00', 'value' => '12:59'),
                array('label' => '13:00', 'value' => '13:59'),
                array('label' => '14:00', 'value' => '14:59'),
                array('label' => '15:00', 'value' => '15:59'),
                array('label' => '16:00', 'value' => '16:59'),
                array('label' => '17:00', 'value' => '17:59'),
                array('label' => '18:00', 'value' => '18:59'),
                array('label' => '19:00', 'value' => '19:59'),
                array('label' => '20:00', 'value' => '20:59'),
                array('label' => '21:00', 'value' => '21:59'),
                array('label' => '22:00', 'value' => '22:59'),
                array('label' => '23:00 ', 'value' => '23:59'),
            );
        return $end_time;
    }

    public function get_traffic_index()
    {
        $retVal = array(
            array('label' => 'Visits', 'value' => 'visits'),
            array('label' => 'Traffic Flow', 'value' => 'traffic_flow'),
            array('label' => 'Avg time', 'value' => 'avg_time'),
        );
        return response()->json($retVal);
    }
    public function get_day_of_week()
    {
        $retVal = array(
            array('label' => 'Tất cả', 'value' => '0'),
            array('label' => 'Thứ 2', 'value' => '2'),
            array('label' => 'Thứ 3', 'value' => '3'),
            array('label' => 'Thứ 4', 'value' => '4'),
            array('label' => 'Thứ 5', 'value' => '5'),
            array('label' => 'Thứ 6', 'value' => '6'),
            array('label' => 'Thứ 7', 'value' => '7'),
            array('label' => 'Chủ nhật', 'value' => '8'),
        );
        return response()->json($retVal);
    }
    public function get_date_time()
    {
        $date = getdate();
        return  $date;
    }
    public function get_user_page_parametter(Request $request)
    {
        $request_user = $request->user();
        $user_id = $request_user->id;

        $page_id = $request->page_id;
        $format_hour = 1;
        $language_index = '\'vn\'';

        // Lấy tham só theo user và trang
        // Nghĩa bắt đầu sửa từ ngày 27/08/2019
        $user_page_parametter = UserPageParametter::where([['user_id',  $user_id], ['page_id',  $page_id]])->first();
        // Lấy danh sách Organization theo users
        if ($request_user->lever === '0' && $request_user->organization_id === '0') {
            $organization = Organization::all();
        } else {
            $organization_id = $request_user->organization_id;
            $organization = Organization::where('id', $request_user->organization_id)->get();
        }
        $organization_arr = array();
        $organizations = json_decode($organization);
        $format_hour = (int) $organizations[0]->time_setting_12h;

        // Lấy list start time
        $start_time = $this->get_start_time($format_hour);
        // Lấy list end time
        $end_time = $this->get_end_time($format_hour);
        // Lấy list traffic_index
        $traffic_index = [];
        $performance_index_group = [];
        $fba_index = [];
        if ($request_user->lever === '0' && $request_user->organization_id === '0') {
            $role_index = DB::select("SELECT im.id, im.index_name, im.group_name, im.module_id, im.description, m.module_name, m.module_code FROM index_module im INNER JOIN modules m ON im.module_id = m.id  where im.sorted_by <> 0  ORDER BY im.group_sorted_by,sorted_by ASC");
        } else {
            $role_index = DB::select("SELECT * FROM fc_get_page_module_language ($request_user->organization_id, $language_index)");
        }


        foreach ($role_index as $key => $value) {
            if (strtolower($value->module_code) === 'footfall') {
                $traffic_index[] = array('label' => $value->index_name, 'value' => $value->description);
            }
            if (strtolower($value->module_code) === 'fba') {
                $fba_index[] = array('label' => $value->index_name, 'value' => $value->description);
            }
            $performance_index_group[] = array('label' => $value->index_name, 'value' => $value->description, 'group' => $value->group_name);
        }
        $index_module = $this->get_language_time_period($language);
        $fba_time_period_metrics = $index_module;
        array_splice($index_module, 4, 1); // vị trí thứ 4 vị trí của 14 ngày trước
        // error_log(date('y-m-d'). ':'. print_r($index_module,true), 3 , 'log.log');
        $fba_time_period_overview = $index_module;

        // Huy 13/12 update lấy thời kỳ; overview k dùng
        $fba_time_period_metrics = array(
            array('label' => 'Hôm nay', 'value' => 'today'),
            array('label' => 'Hôm qua', 'value' => 'yesterday'),
            array('label' => 'Tuần này', 'value' => 'this_week'),
            array('label' => 'Tuần trước', 'value' => 'last_week'),
            array('label' => '14 ngày trước', 'value' => 'last_fourteen_day'),
            array('label' => 'Tháng này', 'value' => 'this_month'),
            array('label' => 'Tháng trước', 'value' => 'last_month'),
            array('label' => 'Năm nay', 'value' => 'this_year'),
            array('label' => 'Năm trước', 'value' => 'last_year'),
        );
        foreach ($organizations as $item) {
            $organization_arr[] =  array('label' => $item->organization_name, 'value' => strval($item->id));
        }

        return response()->json(array(
            'start_time_list' => $start_time, 'end_time_list' => $end_time, 'traffic_index' => $traffic_index, 'user_page_parametter' =>  $user_page_parametter ? json_decode($user_page_parametter->parametter) : [], 'organization' => $organization->toJson(), 'organization_arr' => $organization_arr, 'fba_index' => $fba_index, 'performance_index_group' => $performance_index_group, 'fba_time_period_overview' => $fba_time_period_overview, 'fba_time_period_metrics' => $fba_time_period_metrics
        ));
    }


    public function get_user_page_parametter_v2(Request $request)
    {
        $request_user = $request->user();
        $user_id = $request_user->id;

        $page_id = $request->page_id;
        $language = $request->language;
        $format_hour = 1;
        $language_index = '\'vn\'';

        // Lấy tham só theo user và trang
        // Nghĩa bắt đầu sửa từ ngày 27/08/2019
        $user_page_parametter = UserPageParametter::where([['user_id',  $user_id], ['page_id',  $page_id]])->first();
        // Lấy danh sách Organization theo users
        if ($request_user->lever === '0' && $request_user->organization_id === '0') {
            $organization = Organization::all();
        } else {
            $organization_id = $request_user->organization_id;
            $organization = Organization::where('id', $organization_id)->get();
        }
        $organization_arr = array();
        $organizations = json_decode($organization);
        $format_hour = (int) $organizations[0]->time_setting_12h;
        $save_session = (int) $organizations[0]->save_session;
        $change_language_index =   (bool) $organizations[0]->change_language_index;
        if ($change_language_index)
            $language_index =  '\'' . $request->language . '\'';
        else
            $language_index = '\'en\'';

        // Lấy list start time
        $start_time = $this->get_start_time($format_hour);
        // Lấy list end time
        $end_time = $this->get_end_time($format_hour);
        // Lấy list traffic_index
        $traffic_index = [];
        $performance_index_group = [];
        $fba_index = [];
        $index = DB::select("SELECT * FROM fc_get_list_index_language($language_index)");
        foreach ($index as $value) {
            $name[strtolower($value->name_chart)] =  $value->index_name;
            $values[strtolower($value->name_chart)] =  $value->description;
        }
        $list_index =  (object) $name;
        $list_index_value =  (object) $values;
        if ($request_user->lever === '0' && $request_user->organization_id === '0') {
            $role_index = DB::select("SELECT im.id, im.index_name, im.group_name, im.module_id, im.description, m.module_name, m.module_code FROM index_module im INNER JOIN modules m ON im.module_id = m.id  where im.sorted_by <> 0  ORDER BY im.group_sorted_by,sorted_by ASC");
        } else {
            $role_index = DB::select("SELECT * FROM fc_get_page_module_language ($request_user->organization_id, $language_index)");
        }

        foreach ($role_index as $key => $value) {
            if (strtolower($value->module_code) === 'footfall') {
                $traffic_index[] = array('label' => $value->index_name, 'value' => $value->description);
            }
            if (strtolower($value->module_code) === 'fba') {
                $fba_index[] = array('label' => $value->index_name, 'value' => $value->description);
            }
            $performance_index_group[] = array('label' => $value->index_name, 'value' => $value->description, 'group' => $value->group_name);
        }

        $index_module = $this->get_language_time_period($language);

        $type_language = strtolower($language) == 'vn' ? 1 : 0;

        try {
            $obj = UserOrganizationLanguage::where([['user_id',  $user_id],['organization_id',  $organization_id]])->first();
            if ($obj == null) {
                $obj = new UserOrganizationLanguage;
                $obj->user_id =  $user_id;
                $obj->organization_id =  (int)$organization_id;
            }
            $obj->language_vn =  $type_language;
            $obj->save();
        } catch (\Exception $exception) {
        }

        $fba_time_period_metrics = $index_module;
        array_splice($index_module, 4, 1); // xóa vị trí thứ 4: 14 ngày trước
        $fba_time_period_overview = $index_module;
        array_splice($index_module, 1, 1);
        array_splice($index_module, 2, 1);
        array_splice($index_module, 3, 1);
        array_splice($index_module, 4, 1);
        $dashboard_overview  = $index_module;

        foreach ($organizations as $item) {
            $organization_arr[] =  array('label' => $item->organization_name, 'value' => strval($item->id));
        }

        return response()->json(array(
            'start_time_list' => $start_time,
            'end_time_list' => $end_time,
            'traffic_index' => $traffic_index,
            'user_page_parametter' =>  $user_page_parametter ? json_decode($user_page_parametter->parametter) : [],
            'organization' => $organization->toJson(),
            'save_session' => $save_session,
            'list_index' => $list_index,
            'list_index_value' => $list_index_value,
            'language_index' => $language_index,
            'organization_arr' => $organization_arr,
            'fba_index' => $fba_index,
            'performance_index_group' => $performance_index_group,
            'fba_time_period_overview' => $fba_time_period_overview,
            'fba_time_period_metrics' => $fba_time_period_metrics,
            'dashboard_overview' => $dashboard_overview
        ));
    }

    public function get_language_time_period(&$language)
    {
        $fba_time_period_metrics = array(
            array('label' => 'Hôm nay', 'value' => 'today'),
            array('label' => 'Hôm qua', 'value' => 'yesterday'),
            array('label' => 'Tuần này', 'value' => 'this_week'),
            array('label' => 'Tuần trước', 'value' => 'last_week'),
            array('label' => '14 ngày trước', 'value' => 'last_fourteen_day'),
            array('label' => 'Tháng này', 'value' => 'this_month'),
            array('label' => 'Tháng trước', 'value' => 'last_month'),
            array('label' => 'Năm nay', 'value' => 'this_year'),
            array('label' => 'Năm trước', 'value' => 'last_year'),
        );
        if (strtolower($language) == 'en') {
            $fba_time_period_metrics = array(
                array('label' => 'Today',  'value' => 'today'),
                array('label' => 'Yesterday',   'value' => 'yesterday'),
                array('label' => 'This week',  'value' => 'this_week'),
                array('label' => 'Last week', 'value' => 'last_week'),
                array('label' => 'Last fourteen days',  'value' => 'last_fourteen_day'),
                array('label' => 'This month', 'value' => 'this_month'),
                array('label' => 'Last month',  'value' => 'last_month'),
                array('label' => 'This year', 'value' => 'this_year'),
                array('label' => 'Last year',  'value' => 'last_year'),
            );
        }
        return $fba_time_period_metrics;
    }

    public function userGetOrg(Request $request)
    {
        try {
            // lấy thông tin người dùng. Nếu không có là lỗi
            $request_user = $request->user();
            $lever = (int) $request_user->lever;
            $organization_id = (int) $request_user->organization_id;
            $user_id = (int) $request_user->id;
            $response = [];
            // Lấy danh sách Organization theo users
            $organization_array = [];
            $isSuperAdmin = $this->isSuperAdmin($request_user);
            $isOrgAdmin = $this->isOrgAdmin($request_user);
            $org = new Organization;
            $columnArray = [
                'organization_name AS label', 'id AS value'
            ];
            $response['organization_arr'] = $org->tryGetAllData($columnArray);
            $response['moduleArray'] = DB::select("SELECT m.id AS value, m.module_name AS label FROM fc_get_module_for_user_v2($organization_id, $user_id) fc INNER JOIN modules m ON fc.page_module = m.module_code");
            $response['isSuperAdmin'] = $isSuperAdmin;
            $response['isOrgAdmin'] = $isOrgAdmin;
            $response['status'] = 1;
            return response()->json($response);
        } catch (Exception $e) {
            $response = [
                'status' => 0, 'message' => $e->getMessage()
            ];
            return response()->json($response);
        }
    }

    // function lay theo tung page cua nguoi dung
    public function getSpecificPageSchedule(Request $request)
    {
        try {
            $error_messages = [
                'page_id.required' => 'Mã page không được để trống'
            ];
            $validator = Validator::make($request->all(), [
                'page_id' => 'required'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $pageId = $request->page_id;
            //
            $request_user = $request->user();
            $userId = $request_user->id;
            $orgId = $request_user->organization_id;
            $columnArray = [
                'organization_name AS label', 'id AS value'
            ];
            $startHourArray = $this->get_start_time();
            $endHourArray = $this->get_end_time();
            $org = new Organization;
            $orgArray = $org->tryGetAllData($columnArray);
            $isSuperAdmin = $this->isSuperAdmin($request_user);
            // $siteArray = Site::tryGetSiteInRole($orgId);
            // Đã lọc report_type
            $retrieveData = UserEmailModule::tryGetSpecificDataWithPageId($pageId, $userId);

            $response = [
                'status' => 1, 'startHourArray' => $startHourArray, 'endHourArray' => $endHourArray, 'userInfo' => $request_user
                // , 'siteArray' => $siteArray
                , 'retrieveData' => $retrieveData, 'pageId' => $pageId, 'userId' => $userId, 'orgArray' => $orgArray, 'isSuperAdmin' => $isSuperAdmin
            ];
            return response()->json($response);
            // user_email_module
        } catch (Exception $e) {
            $response = [
                'status' => 0, 'message' => $e->getMessage(), 'code' => $e->getCode()
            ];
            return response()->json($response);
        }
    }

    // phía người dùng

    public function userGetMailScheduleInfo(Request $request)
    {
        try {
            $request_user = $request->user();
            $userId = $request_user->id;
            $orgId = $request_user->organization_id;
            $startTime = $this->get_start_time();
            $endTime = $this->get_end_time();
            $pageData = DB::select("SELECT p.id, p.page_name, p.end_point, p.page_module FROM pages p INNER JOIN fc_get_module_for_user_v2($orgId, $userId) fc ON p.page_module = fc.module_id WHERE p.deleted = 0 AND p.actived = 1");
            $moduleArray = DB::select("SELECT m.id, m.module_name FROM fc_get_module_for_user_v2($orgId, $userId) fc INNER JOIN modules m ON fc.page_module = m.module_code WHERE fc.module_id != 0");
            $siteArray = Site::tryGetSiteInRole($orgId);
            // Đã lọc report_type
            $retrieveData = DB::select("SELECT uem.*, p.page_name, fc.module_name FROM user_email_module uem INNER JOIN pages p ON p.id = uem.page_id INNER JOIN fc_get_module_for_user_v2($orgId, $userId) fc ON uem.module_id = fc.module_id WHERE uem.user_id = $userId AND uem.report_type != 0");
            $response = [
                'status' => 1, 'startTime' => $startTime, 'endTime' => $endTime, 'pageData' => $pageData, 'userData' => $request_user, 'moduleData' => $moduleArray, 'siteData' => $siteArray, 'retrieveData' => $retrieveData
            ];
            return response()->json($response);
            // user_email_module
        } catch (Exception $e) {
            $response = [
                'status' => 0, 'message' => $e->getMessage()
            ];
            return response()->json($response);
        }
    }

    // phía quản trị
    public function userMailScheduleGetData(Request $request)
    {
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'organization_id.required' => 'Mã tổ chức không được để trống',
                'organization_id.integer' => 'Mã tổ chức phải là số',
                'organization_id.min' => 'Mã tổ chức có giá trị ít nhất là: :min'
            ];
            $validator = Validator::make($request->all(), [
                'module_id' => 'required|integer|min:0', 'organization_id' => 'required|integer|min:0'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            $orgId = $request->organization_id;
            $moduleId = $request->module_id;
            $pageId = $request->page_id;
            $startTimeData = $this->get_start_time();
            $endTimeData = $this->get_end_time();
            $pageData = DB::table('pages')->where('page_module', $moduleId)->where('deleted', 0)->where('actived', 1)->get();
            $userData = DB::table('users')->where('organization_id', $orgId)->where('deleted', 0)->where('actived', 1)->get();
            $object = new UserEmailModule();
            // Đã lọc report_type
            $retrieveData = $object->tryGetDataWithPageIdAndModuleId($moduleId, $orgId);
            $response = [
                'status' => 1, 'startTimeData' => $startTimeData, 'endTimeData' => $endTimeData, 'pageData' => $pageData, 'userData' => $userData, 'retrieveData' => $retrieveData
            ];
            return response()->json($response);
            // user_email_module
        } catch (Exception $e) {
            $response = [
                'status' => 0, 'message' => $e->getMessage(), 'code' => $e->getCode()
            ];
            return response()->json($response);
        }
    }
    // mail thiet bi mat ket noi
    public function terminalUserGetData(Request $request)
    {
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'organization_id.required' => 'Mã tổ chức không được để trống',
                'organization_id.integer' => 'Mã tổ chức phải là số',
                'organization_id.min' => 'Mã tổ chức có giá trị ít nhất là: :min'
            ];
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|min:0'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            $orgId = $request->organization_id;
            $userData = DB::table('users')->where('organization_id', $orgId)->where('deleted', 0)->where('actived', 1)->get();
            $object = new UserEmailModule();
            $retrieveData = $object->tryGetDataWithPageId($orgId);
            $response = [
                'status' => 1, 'userData' => $userData, 'retrieveData' => $retrieveData
            ];
            return response()->json($response);
            // user_email_module
        } catch (Exception $e) {
            $response = [
                'status' => 0, 'message' => $e->getMessage(), 'code' => $e->getCode()
            ];
            return response()->json($response);
        }
    }

    public function fba_report_get_config_by_user(Request $request)
    {
        $request_user = $request->user();
        $user_id = $request_user->id;

        $page_id = intval($request->page_id);

        // Lấy tham só theo user và trang
        $user_page_parametter = UserPageParametter::where('user_id',  $user_id)->where('page_id',  $page_id)->first();

        // Lấy danh sách Organization theo users
        if ($request_user->lever === '0') {
            $organization = Organization::all()->toJson();
        } else {
            $organization_id =  $request_user->organization_id;
            $organization = Organization::where('id', $request_user->organization_id)->get()->toJson();
        }
        // Lấy list start time
        $start_time = $this->get_start_time();
        // Lấy list end time
        $end_time = $this->get_end_time();
        // Lấy fba_index
        $fba_index = array(
            array('label' => 'NPS Index', 'value' => 'nps_index'),
            array('label' => 'CX Index', 'value' => 'cx_index'),
        );

        return response()->json(array(
            'start_time_list' => $start_time, 'end_time_list' => $end_time, 'traffic_index' => $fba_index, 'user_page_parametter' => $user_page_parametter ? $user_page_parametter->parametter : [], 'organization' => $organization

        ));
    }
}
