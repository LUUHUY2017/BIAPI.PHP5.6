<?php

namespace App\Http\Controllers\Poc\Terminals;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\BrickStreamTerminal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Mtownsend\XmlToArray\XmlToArray;
use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version2X;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Log;

use Mail;


require __DIR__ . '/../../../../../vendor/autoload.php';

class BrickstreamController extends Controller
{
    public function __construct()
    {
        $this->middleware('xml');
    }
    public function index(Request $request)
    {
        $request_user = $request->user();
        $organization_id = $request->organization_id;
        $user_id = $request_user->id;
        $deleted = $request->deleted;
        $site_id = $request->site_id;
        $data = [];
        $data['brick_array'] = DB::select("exec sp_footfall_get_brickstream_terminals $user_id, $organization_id, $deleted, $site_id");
        $data['locationArray'] = DB::select("SELECT l.id AS value, l.location_name AS label, l.site_id FROM locations l INNER JOIN fc_get_site_in_role($organization_id, $user_id) fc ON l.site_id = fc.id WHERE fc.enables = 1 AND l.module = 1 AND l.actived = 1 AND l.deleted = 0");
        return response()->json($data);
    }

    public function send_email_status_terminal(Request $request) {
        try {
            $data = ['title' => 'Thống kê thiết bị 3D BrickStream', 'body' => []];
            $orgArray = DB::table("organizations")->get();
            if(count($orgArray) > 0) {
                for ($i = 0; $i < count($orgArray); $i++) {
                    $orgItem = $orgArray[$i];
                    if ($orgItem->id == 6) {
                        continue;
                    }
                    $brickStreamTerminalArray = DB::select("exec sp_footfall_get_brickstream_terminals 1, $orgItem->id, 0, 0");
                    for ($j = 0; $j < count($brickStreamTerminalArray); $j++) {
                        $item = $brickStreamTerminalArray[$j];
                        if ($item->online == 0) {
                            $data['body'][] = $item;
                        }
                    }
                }
                $email = ['anhnh@acs.vn', 'quyet@acs.vn', 'hovu@acs.vn', 'nghiant@acs.vn'];
                if (count($data['body']) > 0) {
                   foreach ($email as $key => $value) {
                        Mail::send('notification.send_terminal_status', $data, function($msg) use ($value){
                            $msg->from(env('EMail_AD'),'ACS Solution');
                            $msg->to($value,'ACS Solution')->subject("Thống kê các thiết bị BrickStream đang ngắt kết nối");
                        });
                    } 
                }
            }
            return response()->json(['status' => true, 'errMsg' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'errMsg' => $e->getMessage()]);
        }
        
    }

    public function post_add(Request $request)
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
                //
                'device_name.required' => 'Tên thiết bị không được để trống',
                'device_name.min' => 'Tên thiết bị có ít nhất :min kí tự',
                'device_name.max' => 'Tên thiết bị có nhiều nhất :max kí tự'
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'serial_number' => 'required|min:1|unique:poc_brickstream_terminals', 'organization_id' => 'required|numeric|min:1', 'location_id' => 'required|numeric|min:1', 'device_name' => 'required|min:3|max:50'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            $date = Carbon::now()->format('Y-m-d H:i:s');
            $user_id = $request_user->id;
            $object = new BrickStreamTerminal;
            $object->created_by = $user_id;
            $object->created_at = $date;
            $object->updated_by = $user_id;
            $object->updated_at = $date;
            $object->organization_id = $request->organization_id;
            $object->location_id = $request->location_id;
            $object->device_name = $request->device_name;
            $object->serial_number = $request->serial_number;
            $object->actived = 1;
            $object->deleted = 0;
            $object->save();
            DB::commit();
            return response()->json(['message' => 1]);
        } catch (\Exception $e) {
            $response = [];
            $response['message'] = $e->getMessage();
            $response['code'] = $e->getCode();
            DB::rollback();
            return response()->json($response);
        }
    }

    public function post_update(Request $request)
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
                //
                'device_name.required' => 'Tên thiết bị không được để trống',
                'device_name.min' => 'Tên thiết bị có ít nhất :min kí tự',
                'device_name.max' => 'Tên thiết bị có nhiều nhất :max kí tự'
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'location_id' => 'required|numeric|min:1', 'device_name' => 'required|min:3|max:50', 'actived' => 'required|between:0,1'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            $date = Carbon::now()->format('Y-m-d H:i:s');
            $user_id = $request_user->id;
            $data = [
                'updated_by' => $user_id, 'updated_at' => $date, 'device_name' => $request->device_name, 'location_id' => $request->location_id, 'actived' => $request->actived
            ];
            DB::table('poc_brickstream_terminals')->where('serial_number', $request->serial_number)->update($data);
            DB::commit();
            return response()->json(['message' => 1]);
        } catch (\Exception $e) {
            $response = [];
            $response['message'] = $e->getMessage();
            $response['code'] = $e->getCode();
            DB::rollback();
            return response()->json($response);
        }
    }

    public function soft_delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                // SERIAL NUMBER
                'serial_number.required' => 'Serial number không được để trống',
                'serial_number.numeric' => 'Serial number phải là số',
                'serial_number.min' => 'Serial number có giá trị nhỏ nhất là :min',
                'serial_number.unique' => 'Serial number đã tồn tại',
                // Trường mã địa điểm
                'deleted.required' => 'Deleted không được để trống',
                'deleted.between' => 'Deleted có giá trị là :min hoặc :max',
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'serial_number' => 'required|min:1|without_space', 'deleted' => 'required|between:0,1'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $serial_number = $request->serial_number;
            if ($request->deleted == 1) {
                $updateData['actived'] = 1;
                $updateData['deleted'] = 0;
            } else { // Nếu là đánh dấu xóa
                $updateData['actived'] = 0;
                $updateData['deleted'] = 1;
            }
            DB::table('terminals')->where('serial_number', $serial_number)->update($updateData);
            DB::commit();
            return response()->json(['message' => 1]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
    public function delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                // SERIAL NUMBER
                'serial_number.required' => 'Serial number không được để trống',
                'serial_number.numeric' => 'Serial number phải là số',
                'serial_number.min' => 'Serial number có giá trị nhỏ nhất là :min',
                'serial_number.unique' => 'Serial number đã tồn tại',
                // Trường mã địa điểm
                'deleted.required' => 'Deleted không được để trống',
                'deleted.between' => 'Deleted có giá trị là :min hoặc :max',
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'serial_number' => 'required|min:1|without_space'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $serial_number = $request->serial_number;
            DB::table('terminals')->where('serial_number', $serial_number)->delete();
            DB::commit();
            return response()->json(['message' => 1]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function get_data(Request $request)
    {
        $Acknowledgement = "HTTP/1.1 200 OK\r\nContent-type: text/html\r\nConnection: close\r\nContent-Length: 0\r\n\r\n";
        $NegativeAcknowledgement = "HTTP/1.1 400 Bad Request\r\nContent-type: text/html\r\nConnection: close\r\nContent-Length: 43\r\n\r\n<ErrorList><Error>error</Error></ErrorList>";
        try {
            $xml_data = $request->getContent();
            $wan_ip_address = $_SERVER['REMOTE_ADDR'];  // get ip send
            date_default_timezone_set('Asia/Ho_Chi_Minh');

            // $current_date = date('y-m-d');
            // log::usefiles(base_path() . '/dailylog/' . $current_date . '3d+.log', 'info');
            // log::info($xml_data);

            $array = XmlToArray::convert($xml_data);
            $properties = $array['Properties'];

            $SerialNumber =   $properties['SerialNumber'];
            // Check Thông tin serial_number trong database
            $items = DB::select("exec sp_poc_brickstream_terminals_locations_site $SerialNumber");
            if (count($items) === 0)
                return response($NegativeAcknowledgement);
            // return response()->json(['message'=> 'Not Found!'],404);

            $Version =   isset($properties['Version']) ? $properties['Version'] : '';
            $TransmitTime =   isset($properties['TransmitTime']) ? $properties['TransmitTime'] : '';
            $IpAddress =   isset($properties['IpAddress']) ?  $properties['IpAddress'] : '';
            $MacAddress =   isset($properties['MacAddress']) ? $properties['MacAddress'] : '';
            $HttpPort =   isset($properties['HttpPort']) ? $properties['HttpPort'] : '';
            $HttpsPort =   isset($properties['HttpsPort']) ? $properties['HttpsPort'] : '';
            $Timezone =   isset($properties['Timezone']) ? $properties['Timezone'] : '';
            $HwPlatform =   isset($properties['HwPlatform']) ? $properties['HwPlatform'] : '';
            $HttpsPort =   isset($properties['HttpsPort']) ?  $properties['HttpsPort'] : '';

            $reportdata = $array['ReportData'];
            $date = $reportdata['Report']['@attributes']['Date'];

            $site =  $array['@attributes'];
            $SiteId =  isset($site['SiteId']) ?  $site['SiteId'] : '';
            $Sitename =  isset($site['Sitename']) ? $site['Sitename'] : '';
            $DeviceId =  isset($site['DeviceId']) ? $site['DeviceId'] : '';
            $Devicename =  isset($site['Devicename']) ? $site['Devicename'] : '';



            $organization_id = $items[0]->organization_id;
            $location_id = $items[0]->location_id;

            $open_hour =  Carbon::parse($items[0]->open_hour)->format('H:i');
            $close_hour =  Carbon::parse($items[0]->close_hour)->format('H:i');

            // Lặp gói tin
            if (!isset($reportdata['Report']['Object'])) {
                $interval = 15;
                $date_now = Date('Y-m-d H:i:s');
                // Update thông tin bricktream
                DB::select("exec sp_poc_brickstream_terminals_update_info '$SerialNumber',$interval,'$Version','$TransmitTime','$MacAddress','$IpAddress','$HttpPort','$HttpsPort','$Timezone','$HwPlatform','$date_now','$wan_ip_address','$SiteId','$Sitename','$DeviceId','$Devicename'");
                return response($Acknowledgement);
            }

            $object = $reportdata['Report']['Object'];
            $interval = $reportdata['@attributes']['Interval'];
            $status = true;
            if (isset($object['Count'])) {
                foreach ($object['Count'] as $key => $data) {
                    $value = isset($object['Count']['@attributes']) ?  $data : $data['@attributes'];
                    $start_date_time = "'" . $date . ' ' . $value['StartTime'] . "'";
                    $result = $this->insert_data_into_database($value, $date, $open_hour, $close_hour, $SerialNumber, $interval, $organization_id, $location_id);
                    if ($result == 0)
                        $status = false;
                }
            } else {
                foreach ($object as $key => $data) {
                    if (isset($data['Count'])) {
                        $value = $data['Count']['@attributes'];
                        $start_date_time = "'" . $date . ' ' . $value['StartTime'] . "'";
                        $result = $this->insert_data_into_database($value, $date, $open_hour, $close_hour, $SerialNumber, $interval, $organization_id, $location_id);
                        if ($result == 0)
                            $status = false;
                    }
                }
            }

            // Update thông tin bricktream
            DB::select("exec sp_poc_brickstream_terminals_update_info '$SerialNumber',$interval,'$Version','$TransmitTime','$MacAddress','$IpAddress','$HttpPort','$HttpsPort','$Timezone','$HwPlatform',$start_date_time,'$wan_ip_address','$SiteId','$Sitename','$DeviceId','$Devicename'");
            if (!$status)
                return response($NegativeAcknowledgement);
            //return response()->json(['message'=> 'Not Found!'],404);
            if ($result == 1)
                return response($Acknowledgement);
            else
                return response($NegativeAcknowledgement);
            // return response()->json(['message'=> 'Not Found!'],404);
        } catch (\Exception $e) {
            //return response($NegativeAcknowledgement);
            // return response()->json(['message' => 'Not Found!'], 404);
            return response($NegativeAcknowledgement);
        }
    }

    function insert_data_into_database(&$value, &$date, &$open_hour, &$close_hour, &$SerialNumber, &$interval, &$organization_id, &$location_id)
    {
        $StartTime =   $value['StartTime'];
        $EndTime =   $value['EndTime'];
        $Enters =   $value['Enters'];
        $Exits =   $value['Exits'];
        $Time =  Carbon::parse($StartTime)->format('H:i');
        $start_date_time = "'" . $date . ' ' . $StartTime . "'";
        $end_date_time = "'" . $date . ' ' . $EndTime . "'";
        $result = 1;
        if (($Enters > 0 || $Exits > 0) &&  $open_hour <=  $Time  &&  $Time  <=  $close_hour) {
            $insert_data_in_out = DB::select("exec sp_poc_data_in_out_insert_2 $SerialNumber,$start_date_time,$end_date_time,$Enters,$Exits,$interval,$organization_id,$location_id");
            $result = (int) $insert_data_in_out[0]->result;
        }
        return $result;
    }
}
