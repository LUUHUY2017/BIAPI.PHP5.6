<?php

namespace App\Http\Controllers\Admin;

use App\FbaTablet as DefaultModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\Terminal;
use Carbon\Carbon;
use ElephantIO\Client, ElephantIO\Engine\SocketIO\Version2X, ElephantIO\Exception\ServerConnectionFailureException;

require __DIR__ . '/../../../../vendor/autoload.php';

class FbaTabletController extends Controller
{
    public function getData(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'organization_id.required' => 'Mã tổ chức không được để trống',
                'organization_id.integer' => 'Mã tổ chức phải là số',
                'organization_id.min' => 'Mã tổ chức có giá trị nhỏ nhất là :min',
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|min:1', 'deleted' => 'required|boolean'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            $user_id =  $request_user->id;
            $data = [];
            $organization_id = $request->organization_id;
            $deleted = $request->deleted;
            $data['retrieveData'] = DB::select("exec sp_fba_get_tablet_v2 $user_id, $organization_id, $deleted");
            $settingArray = DefaultModel::tryGetSettingManager($organization_id);
            $data['siteArray'] = $settingArray['_site'];
            $data['locationArray'] = $settingArray['_location'];
            $data['userInfo'] = $request_user;
            $data['orgId'] = $organization_id;
            $data['status'] = 1;
            return response()->json($data);
        } catch (Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            $response['code'] = $e->getCode();
            DB::rollback();
            return response()->json($response);
        }
    }
    // thêm vị trí theo site of organization
    public function insert(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'organization_id.required' => 'Mã tổ chức không được để trống',
                'organization_id.integer' => 'Mã tổ chức phải là số',
                'organization_id.min' => 'Mã tổ chức có giá trị nhỏ nhất là :min',
                // Mã địa điểm phải là số
                'location_id.required' => 'Mã địa điểm không được để trống',
                'location_id.integer' => 'Mã địa điểm phải là số',
                'location_id.min' => 'Mã địa điểm có giá trị nhỏ nhất là :min',
                // Trường tên địa điểm
                'tablet_name.required' => 'Tên vị trí không được để trống',
                'tablet_name.min' => 'Tên vị trí phải có ít nhất :min kí tự',
                'tablet_name.max' => 'Tên vị trí có nhiều nhất :max kí tự',
                // Trường mã địa điểm
                'imei.required' => 'Module không được để trống',
                //
                'actived.required' => 'Mã hoạt động không được bỏ trống',
                'actived.boolean' => 'Mã hoạt động không phù hợp'

            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|min:1', 'imei' => 'required', 'tablet_name' => 'required|min:3', 'location_id' => 'required|integer|min:1', 'actived' => 'required|boolean'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            $serial_number = $request->imei;
            $date = $this->getDateNow();
            $tablet = DefaultModel::where('serial_number', $serial_number)->first();
            if (!$tablet) {
                $object = new DefaultModel();
                $object->organization_id = $request->organization_id;
                $object->location_id = $request->location_id;
                $object->tablet_name = trim($request->tablet_name);
                $object->imei = $request->imei;
                $object->serial_number = $request->imei;
                $object->created_at = $date;
                $object->updated_at = $date;
                $object->created_by = $request_user->id;
                $object->updated_by = $request_user->id;
                $object->actived = $request->actived;
                $object->deleted = 0;
                $object->save();
            } else {
                $object = DefaultModel::findOrFail($tablet->id);
                $object->organization_id = $request->organization_id;
                $object->location_id = $request->location_id;
                $object->tablet_name = trim($request->tablet_name);
                $object->imei = $request->imei;
                $object->serial_number = $request->imei;
                $object->created_at = $date;
                $object->updated_at = $date;
                $object->created_by = $request_user->id;
                $object->updated_by = $request_user->id;
                $object->actived = $request->actived;
                $object->deleted = 0;
                $object->save();
            }
            DB::commit();
            try {
                $access_token = $request->token;
                $tocken_type  = 'Bearer';
                $url_socket = env('URL_SOCKET');
                $socketClient =  new Client(new Version2X($url_socket));
                $socketClient->initialize();
                $socketClient->emit('fba_table_reload_data_single', ['serial_number' => $request->imei, 'tocken_type' => $tocken_type, 'access_token' => $access_token]); // string array
                $socketClient->close();
            } catch (ServerConnectionFailureException $e) {
                $response['socket'] = $e;
            }
            $response = [];
            $response['status'] = 1;
            $object->location_name = $request->localtionName;
            $response['insertedData'] = $object;
            return response()->json($response);
        } catch (Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            $response['code'] = $e->getCode();
            DB::rollback();
            return response()->json($response);
        }
    }
    // cập nhật vị trí theo site of organization
    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'id.required' => 'Id không được để trống',
                // 'id.integer' => 'Id phải là số',
                'id.min' => 'Id có giá trị nhỏ nhất là :min',
                // Mã địa điểm phải là số
                'location_id.required' => 'Mã địa điểm không được để trống',
                // 'location_id.integer' => 'Mã địa điểm phải là số',
                'location_id.min' => 'Mã địa điểm có giá trị nhỏ nhất là :min',
                // Trường tên địa điểm
                'tablet_name.required' => 'Tên vị trí không được để trống',
                'tablet_name.min' => 'Tên vị trí phải có ít nhất :min kí tự',
                'tablet_name.max' => 'Tên vị trí có nhiều nhất :max kí tự',
                // Trường mã địa điểm
                'imei.required' => 'Module không được để trống',
                //
                'actived.required' => 'Mã hoạt động không được bỏ trống',
                'actived.boolean' => 'Mã hoạt động không phù hợp'

            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'id' => 'required|min:1', 'tablet_name' => 'required|min:3', 'location_id' => 'required|min:1', 'actived' => 'required|boolean'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            $id = $request->id;
            $access_token = $request->token;
            $date = $this->getDateNow();
            $object = DefaultModel::findOrFail($id);
            $object->location_id = $request->location_id;
            $object->tablet_name = trim($request->tablet_name);
            if (isset($request->imei)) {
                $object->imei = trim($request->imei);
            }
            $object->updated_at = $date;
            $object->updated_by = $request_user->id;
            $object->actived = $request->actived;
            $object->save();
            DB::commit();

            try {
                $access_token = $request->token;
                $tocken_type  = 'Bearer';
                $url_socket = env('URL_SOCKET');
                $socketClient =  new Client(new Version2X($url_socket));
                $socketClient->initialize();
                $socketClient->emit('fba_table_reload_data_single', ['serial_number' => $request->imei, 'tocken_type' => $tocken_type, 'access_token' => $access_token]); // string array
                $socketClient->close();
                $response['socket'] = 'OK';
            } catch (ServerConnectionFailureException $e) {
                $response['socket'] = $e;
            }

            $response = [];
            $response['status'] = 1;
            $object->location_name = $request->localtionName;
            $response['updatedData'] = $object;
            return response()->json($response);
        } catch (Exception $e) {
            $response = [];
            $response['socket'] = '';
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            $response['code'] = $e->getCode();
            DB::rollback();
            return response()->json($response);
        }
    }
    public function softDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã vị trí phải là số
                'id.required' => 'Mã vị trí không được để trống',
                'id.numeric' => 'Mã vị trí phải là số',
                'id.min' => 'Mã vị trí có giá trị nhỏ nhất là :min',
                // Mã vị trí phải là số
                'deleted.required' => 'Mã deleted không được để trống',
                'deleted.numeric' => 'Mã deleted phải là số',
                'deleted.boolean' => 'Mã deleted không phù hợp',
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|min:1', 'deleted' => 'required|numeric|boolean'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $id = $request->id;
            $object = Location::findOrFail($id);
            if ($request->deleted == 1) {
                $object->deleted = 0;
                Terminal::where('location_id', $id)->update(['deleted' => 0]);
            } else if ($request->deleted == 0) {
                $object->deleted = 1;
                Terminal::where('location_id', $id)->update(['deleted' => 1]);
            }
            $object->save();
            DB::commit();
            return response()->json(['status' => 1]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['status' => 0, 'message' => $e->getMessage()]);
        }
    }

    public function delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã vị trí phải là số
                'id.required' => 'Mã vị trí không được để trống',
                'id.numeric' => 'Mã vị trí phải là số',
                'id.min' => 'Mã vị trí có giá trị nhỏ nhất là :min'
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'id' => 'required|min:1'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $id = $request->id;
            DefaultModel::where('id', $id)->delete();
            DB::commit();
            return response()->json(['status' => 1]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 0, 'message' => $e->getMessage()]);
        }
    }
}
