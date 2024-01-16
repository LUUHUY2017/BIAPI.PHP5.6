<?php

namespace App\Http\Controllers\GenderAge\Terminals;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version2X;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\GenderageTerminal;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/../../../../../vendor/autoload.php';

class TerminalController extends Controller
{

    public function open_api_get_data(Request $request)
    {
        DB::beginTransaction();
        try {
            // Set Log
            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $current_date = date('Y-m-d');
            Log::useFiles(base_path() . '/dailyLog/' . $current_date . '-serviceInfo.log', 'info');
            // End Set Log
            $properties = $request['Properties'];
            $terminal_array = DB::table('poc_horizon_robotic_terminals')->where('serial_number', $properties['SerialNumber'])->where('actived', 1)->where('deleted', 0)->get();
            if (count($terminal_array) === 0) {
                Log::info('Device is not exist');
                throw new Exception('Device is not exist', 770);
            }
            $currentTerminal = $terminal_array[0];
            $updateData = [];
            // Update thhiet bi
            $date = Carbon::now()->format('Y-m-d H:i:00');
            $end_date = date('Y-m-d H:i:00', strtotime("+1 minutes"));
            $timestamp = $date;
            $site_array = DB::select("SELECT s.id, s.organization_id FROM sites s INNER JOIN locations l ON s.id = l.site_id WHERE l.id = $currentTerminal->location_id");
            //
            $serial_number = '\'' . $currentTerminal->serial_number . '\'';
            $start_time = '\'' . $date . '\'';
            $end_time = '\'' . $end_date . '\'';
            $organization_id = $currentTerminal->organization_id;
            $location_id = $currentTerminal->location_id;
            $interval = isset($currentTerminal->interval) ? $currentTerminal->interval * 60 : 60;
            $ip_address = '\'' . $currentTerminal->ip_address . '\'';
            $smartData = $request['SmartData'];
            // Dữ liệu sau khoảng thời gian nhất định
            foreach ($smartData as $k => $value) {
                // $updateData['interval'] = $value['Interval'] / 60;
                // $timestamp = Carbon::createFromTimestamp($value['TimeStamp'])->toDateTimeString();
                if ($k == 'FlowStats') {
                    $updateData['interval'] = $value['Interval'] / 60;
                    Log::info('FlowStatic');
                } else if ($k == 'FlowEvent') {
                    // Log::info($value);
                    Log::info('FlowEvent');
                    $snapshot = $value['PersonId'];
                    if ($value['LineType'] == 0) {
                        $num_to_enter = $value['FlowType'] == 0 ? 1 : 0;
                        $num_to_exit = $value['FlowType'] == 1 ? 1 : 0;
                        $passer_by = 0;
                        if ($value['FlowType'] == 0) {
                            $snapshot = '\'' . $value['PersonId'] . '\'';
                            $stats = DB::select("exec sp_poc_gender_age_insert_inshop_v2 $serial_number, $organization_id, $location_id, $start_time, 'unknown', 0, 0, $snapshot");
                            Log::info($stats);
                        }
                    } else {
                        $num_to_enter = 0;
                        $num_to_exit = 0;
                        $passer_in = $value['FlowType'] == 0 ? 1 : 0;
                        $passer_out = $value['FlowType'] == 1 ? 1 : 0;
                        $passer_by = $passer_in + $passer_out;
                    }
                    $proc_stats = DB::select("exec sp_poc_data_in_out_insert_horizon_robotics_v2 $serial_number, $start_time, $end_time, $organization_id, $location_id, $interval, $snapshot, $num_to_enter, $num_to_exit, $passer_by");
                    if ($proc_stats[0]->result != 1) {
                        // if($inserted) {
                        throw new Exception("Error Processing Request", 1);
                    } else {
                        Log::info("insert success " . $proc_stats[0]->plans);
                    }
                } else if ($k == 'Face') {
                    $snapshot = $value['PersonId'];
                    Log::info('Face Identify');
                    // Log::info($value);
                    $listAge = $this->getListAge($value['Age']);
                    $age_min = $listAge['age_min'];
                    $age_max = $listAge['age_max'];
                    $gender = $this->getGenderString($value['Gender']);
                    Log::info($gender);
                    Log::info('age_max ' . $age_max);
                    $proc_stats = DB::select("exec sp_poc_gender_age_update $serial_number, $snapshot, $age_min, $age_max, $gender");
                    if ($proc_stats[0]->result != 1) {
                        throw new Exception("Error Processing Request", 1);
                    } else {
                        Log::info("insert success " . $proc_stats[0]->plans);
                    };
                    $url_socket = env('URL_SOCKET');
                    $socketClient = new Client(new Version2X($url_socket));
                    $socketClient->initialize();
                    $socketClient->emit('admin_staff_vip_backlist_send_snapshot_request_from_web_to_serve', ['site_id' => $site_array[0]->id, 'base64_snapshot' => $value['SnapShot'], 'organization_id' => $site_array[0]->organization_id, 'serial_number' => $currentTerminal->serial_number]);
                    $socketClient->close();
                } else {
                    Log::info('Not Supply');
                    throw new Exception('Data response not supply', 770);
                }
            }
            if ($currentTerminal->ip_address != $properties['IPv4Address']) {
                $updateData['ip_address'] = $properties['IPv4Address'];
            }
            if ($currentTerminal->mac_address != $properties['MacAddress']) {
                $updateData['mac_address'] = $properties['MacAddress'];
            }
            if ($currentTerminal->sdk_version != $properties['SDKVer']) {
                $updateData['sdk_version'] = $properties['SDKVer'];
            }
            if ($currentTerminal->firmware_version != $properties['FirmwareVer']) {
                $updateData['firmware_version'] = $properties['FirmwareVer'];
            }
            if ($currentTerminal->ota_version != $properties['OTAVer']) {
                $updateData['ota_version'] = $properties['OTAVer'];
            }
            if ($currentTerminal->base_version != $properties['BaseVer']) {
                $updateData['base_version'] = $properties['BaseVer'];
            }
            if ($currentTerminal->model_version != $properties['ModelVer']) {
                $updateData['model_version'] = $properties['ModelVer'];
            }
            if ($currentTerminal->device_name != $properties['DeviceName']) {
                $updateData['device_name'] = $properties['DeviceName'];
            }
            $updateData['last_time_update_data'] = $timestamp;
            $updateData['last_time_update_socket'] = $timestamp;
            DB::table('poc_horizon_robotic_terminals')->where('serial_number', $properties['SerialNumber'])->update($updateData);
            DB::commit();
            return response()->json(['message' => 1]);
        } catch (\Exception $e) {
            Log::info($e->getMessage() . $e->getLine());
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function getGenderString($genderNumber)
    {
        if ($genderNumber == '0') {
            return 'female';
        } else if ($genderNumber == '1') {
            return 'male';
        } else {
            return 'unknown';
        }
    }

    public function getListAge($age)
    {
        if ($age == 1) {
            return ['age_min' => 1, 'age_max' => 18];
        }
        if ($age == 2) {
            return ['age_min' => 19, 'age_max' => 35];
            return "19-35";
        }
        if ($age == 3) {
            return ['age_min' => 36, 'age_max' => 55];
            return "36-55";
        }
        if ($age == 4) {
            return ['age_min' => 56, 'age_max' => 99];
        }
        return ['age_min' => 0, 'age_max' => 0];
    }
    // Lấy thiết bị để quản trị theo tổ chức, và thiết bị mới ở bên dưới
    public function sp_poc_gender_age_get_terminals(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|min:1', 'site_id' => 'required|integer|min:0', 'deleted' => 'required|boolean'
                // , 'ip_address' => 'required|ip'
            ]);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            $user_id = $request_user->id;
            $organization_id = $request->organization_id;
            $site_id = $request->site_id;
            $deleted = $request->deleted;
            $data = [];
            $date = Carbon::now()->format('Y-m-d H:i:s');
            $data['terminalArray'] = DB::select("exec sp_poc_gender_age_get_terminals $user_id, $organization_id, $deleted, $site_id");
            $data['recordDate'] = $date;
            // Lấy các tất cả các vị trí có module = 3 và người dùng được phép truy cập
            $data['locationArray'] = DB::select("SELECT l.id AS value, l.location_name AS label, l.site_id FROM locations l WHERE l.module = 3 AND l.actived = 1 AND l.deleted = 0 AND l.organization_id = $organization_id");
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
    // thêm mới thiết bị
    public function gender_age_terminals_insert(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                // SERIAL NUMBER
                'serial_number.required' => 'Serial number không được để trống',
                'serial_number.numeric' => 'Serial number phải là số',
                'serial_number.min' => 'Serial number có giá trị nhỏ nhất là :min',
                'serial_number.unique' => 'Serial number đã tồn tại',
                // MÃ TỔ CHỨC
                'organization_id.required' => 'Mã tổ chức không được để trống',
                'organization_id.numeric' => 'Mã tổ chức phải là số',
                'organization_id.min' => 'Mã tổ chức có giá trị nhỏ nhất là :min',
                // MÃ SITE
                'site_id.required' => 'Mã site không được để trống',
                'site_id.numeric' => 'Mã site phải là số',
                'site_id.min' => 'Mã site có giá trị nhỏ nhất là :min',
                // MÃ VỊ TRÍ PHẢI LÀ SỐ
                'location_id.required' => 'Mã vị trí không được để trống',
                'location_id.numeric' => 'Mã vị trí phải là số',
                'location_id.min' => 'Mã vị trí có giá trị nhỏ nhất là :min',
                // ĐỊA CHỈ IP
                'ip_address.required' => 'Địa chỉ IP không được để trống',
                'ip_address.ip' => 'Địa chỉ IP không phù hợp'
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'serial_number' => 'required|min:1|unique:poc_horizon_robotic_terminals', 'organization_id' => 'required|numeric|min:1', 'site_id' => 'required|numeric|min:1', 'location_id' => 'required|numeric|min:1'
                // , 'ip_address' => 'required|ip'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            $date = Carbon::now()->format('Y-m-d H:i:s');
            $serial_number = trim($request->serial_number);
            $object = new GenderageTerminal;
            $object->serial_number = $serial_number;
            $object->organization_id = $request->organization_id;
            $object->site_id = $request->site_id;
            $object->location_id = $request->location_id;
            // $object->ip_address = $request->ip_address;
            $object->created_by = $request_user->id;
            $object->created_at = $date;
            $object->actived = "1";
            $object->deleted = 0;
            $object->save();
            DB::commit();
            $object->gid = DB::table('poc_horizon_robotic_terminals')->where('serial_number', $serial_number)->select('id')->get()[0]->id;
            $object->location_name = $request->location_name;
            $object->site_name = $request->site_name;
            $response = [];
            $response['message'] = 1;
            $response['insertedData'] = $object;
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [];
            $response['message'] = $e->getMessage();
            $response['code'] = $e->getCode();
            DB::rollback();
            return response()->json($response);
        }
    }
    public function gender_age_terminals_update(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã thiết bị phải là số
                'id.required' => 'Serial number không được để trống',
                'id.gui_type' => 'Serial number phải là kiểu guid',
                // SERIAL NUMBER
                'serial_number.required' => 'Serial number không được để trống',
                'serial_number.numeric' => 'Serial number phải là số',
                'serial_number.min' => 'Serial number có giá trị nhỏ nhất là :min',
                'serial_number.unique' => 'Serial number đã tồn tại',
                // MÃ TỔ CHỨC
                'organization_id.required' => 'Mã tổ chức không được để trống',
                'organization_id.numeric' => 'Mã tổ chức phải là số',
                'organization_id.min' => 'Mã tổ chức có giá trị nhỏ nhất là :min',
                // MÃ SITE
                'site_id.required' => 'Mã site không được để trống',
                'site_id.numeric' => 'Mã site phải là số',
                'site_id.min' => 'Mã site có giá trị nhỏ nhất là :min',
                // MÃ VỊ TRÍ PHẢI LÀ SỐ
                'location_id.required' => 'Mã vị trí không được để trống',
                'location_id.numeric' => 'Mã vị trí phải là số',
                'location_id.min' => 'Mã vị trí có giá trị nhỏ nhất là :min',
                // ĐỊA CHỈ IP
                'ip_address.required' => 'Địa chỉ IP không được để trống',
                'ip_address.ip' => 'Địa chỉ IP không phù hợp'
            ];
            $validatorArray = [
                'id' => 'required|gui_type', 'site_id' => 'required|numeric|min:1', 'location_id' => 'required|numeric|min:1'
                // , 'ip_address' => 'required|ip'
            ];
            if (isset($request->serial_number)) {
                $validatorArray['serial_number'] = 'required|min:1|unique:poc_horizon_robotic_terminals';
            }
            $validator = Validator::make($request->all(), $validatorArray, $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            $date = Carbon::now()->format('Y-m-d H:i:s');
            $serial_number = trim($request->serial_number);
            $object = GenderageTerminal::find($request->id);
            if (isset($request->serial_number)) {
                $object->serial_number = $serial_number;
            }
            $object->site_id = $request->site_id;
            $object->location_id = $request->location_id;
            // $object->ip_address = $request->ip_address;
            $object->updated_by = $request_user->id;
            $object->updated_at = $date;
            $object->actived = $request->actived;
            $object->save();
            DB::commit();
            $object->gid = DB::table('poc_horizon_robotic_terminals')->where('id', $request->id)->select('id')->get()[0]->id;
            $object->location_name = $request->location_name;
            $object->site_name = $request->site_name;
            $response = [];
            $response['message'] = 1;
            $response['updatedData'] = $object;
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [];
            $response['message'] = $e->getMessage();
            $response['code'] = $e->getCode();
            DB::rollback();
            return response()->json($response);
        }
    }
    public function gender_age_terminals_soft_delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã thiết bị phải là số
                'id.required' => 'Mã thiết bị không được để trống',
                'id.gui_type' => 'Mã thiết bị phải là kiểu guid',
                // Mã vị trí phải là số
                'deleted.required' => 'Mã deleted không được để trống',
                'deleted.numeric' => 'Mã deleted phải là số',
                'deleted.between' => 'The :attribute must be between :min - :max.',
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'id' => 'required|gui_type', 'deleted' => 'required|numeric|between:0,1'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $id = $request->id;
            $updateData = [];
            if ($request->deleted == 1) {
                $updateData['actived'] = 1;
                $updateData['deleted'] = 0;
            } else if ($request->deleted == 0) { // Nếu là đánh dấu xóa
                $updateData['actived'] = 0;
                $updateData['deleted'] = 1;
            }
            DB::table('poc_horizon_robotic_terminals')->where('id', $id)->update($updateData);
            DB::commit();
            return response()->json(['message' => 1]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()]);
        }
    }
    public function gender_age_terminals_delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã thiết bị phải là số
                'id.required' => 'Mã thiết bị không được để trống',
                'id.gui_type' => 'Mã thiết bị phải là kiểu guid',
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'id' => 'required|gui_type'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $id = $request->id;
            DB::table('poc_horizon_robotic_terminals')->where('id', $id)->delete();
            DB::commit();
            return response()->json(['message' => 1]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()]);
        }
    }
    public function sp_poc_gender_metric_analytic(Request $request)
    {
        try {
            $request_user = $request->user();
            $user_id = $request_user->id;
            $organization_id = $request->organization_id;
            $site_id = $request->site_id;
            $start_hour = $request->start_hour;
            $end_hour = $request->end_hour;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $view_by = $request->view_by;
            $items = DB::select("exec sp_poc_gender_metric_analytic $user_id, $organization_id, $site_id, $start_hour, $end_hour, $start_date, $end_date, $view_by");
            return response()->json($items);
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }
    public function sp_poc_gender_metrics_comparison(Request $request)
    {
        try {
            $request_user = $request->user();
            $user_id = $request_user->id;
            $organization_id = $request->organization_id;
            $site_id = $request->site_id;
            $start_hour = $request->start_hour;
            $end_hour = $request->end_hour;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $view_by = $request->view_by;
            $items = DB::select("exec sp_poc_gender_metrics_comparison $user_id, $organization_id, $site_id, $start_hour, $end_hour, $start_date, $end_date, $view_by");
            return response()->json($items);
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }
    public function sp_poc_gender_age_by_day(Request $request)
    {
        try {
            $request_user = $request->user();
            $user_id = $request_user->id;
            $organization_id = $request->organization_id;
            $site_id = $request->site_id;
            $start_hour = $request->start_hour;
            $end_hour = $request->end_hour;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $view_by = $request->view_by;
            $items = DB::select("exec sp_poc_gender_age_visits $user_id, $organization_id, $site_id, $start_hour, $end_hour, $start_date, $end_date");
            return response()->json($items);
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }
}
