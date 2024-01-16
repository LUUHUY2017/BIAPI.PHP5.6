<?php

namespace App\Http\Controllers\Terminal;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Terminal as DefaultModel;
use App\Organization;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;
use Mail;
// use Error;

class TerminalController extends Controller
{
    public function getData(Request $request)
    {
        try {
            $error_messages = [
                // MÃ TỔ CHỨC
                'organization_id.required' => 'Mã tổ chức không được để trống',
                'organization_id.integer' => 'Mã tổ chức phải là số',
                'organization_id.min' => 'Mã tổ chức có giá trị nhỏ nhất là :min',
                // MÃ SITE
                'site_id.required' => 'Mã site không được để trống',
                'site_id.integer' => 'Mã site phải là số',
                'site_id.min' => 'Mã site có giá trị nhỏ nhất là :min',
                // MÃ VỊ TRÍ PHẢI LÀ SỐ
                'deleted.required' => 'Mã delete không được để trống',
                'deleted.boolean' => 'Mã delete không phù hợp'
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|min:1'
                , 'site_id' => 'required|integer|min:1'
                , 'deleted' => 'required|boolean'
            ], $error_messages);
            $request_user = $request->user();
            $user_id = $request_user->id;
            $organization_id = $request->organization_id;
            $response = [];
            $response['retrieveData'] = DefaultModel::tryGetAllWithStatus($user_id, $organization_id);
            $settingArray = DefaultModel::tryGetSettingManager($organization_id);
            $response['locationData'] = $settingArray['_location'];
            $response['siteData'] = $settingArray['_site'];
            $response['recordDate'] = Carbon::now()->format('g:i A');
            $response['status'] = 1;
            return response()->json($response);
        } catch (Exception $e) {
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }

    public function insert(Request $request) {
        DB::beginTransaction();
        try {
            $error_messages = [
                // SERIAL NUMBER
                'serial_number.required' => 'Serial number không được để trống',
                'serial_number.integer' => 'Serial number phải là số',
                'serial_number.min' => 'Serial number có giá trị nhỏ nhất là :min',
                'serial_number.unique' => 'Serial number đã tồn tại',
                // MÃ TỔ CHỨC
                'organization_id.required' => 'Mã tổ chức không được để trống',
                'organization_id.integer' => 'Mã tổ chức phải là số',
                'organization_id.min' => 'Mã tổ chức có giá trị nhỏ nhất là :min',
                // MÃ SITE
                'site_id.required' => 'Mã site không được để trống',
                'site_id.integer' => 'Mã site phải là số',
                'site_id.min' => 'Mã site có giá trị nhỏ nhất là :min',
                // MÃ VỊ TRÍ PHẢI LÀ SỐ
                'location_id.required' => 'Mã vị trí không được để trống',
                'location_id.integer' => 'Mã vị trí phải là số',
                'location_id.min' => 'Mã vị trí có giá trị nhỏ nhất là :min',
                //
                'device_name.required' => 'Tên thiết bị không được để trống',
                'device_name.min' => 'Tên thiết bị có ít nhất :min kí tự',
                'device_name.max' => 'Tên thiết bị có nhiều nhất :max kí tự'
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'serial_number' => 'required|min:1|unique:terminals'
                , 'organization_id' => 'required|integer|min:1'
                , 'location_id' => 'required|integer|min:1'
                , 'site_id' => 'required|integer|min:1'
                , 'device_name' => 'required|min:1'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            // $newId = DB::select("SELECT NEWID() as new_id")[0]->new_id;
            $date = $this->getDateNow();
            $user_id = $request_user->id;
            $object = new DefaultModel;
            $object->created_by = $user_id;
            $object->created_at = $date;
            $object->updated_by = $user_id;
            $object->updated_at = $date;
            $object->organization_id = $request->organization_id;
            $object->location_id = $request->location_id;
            $object->site_id = $request->site_id;
            $object->device_name = $request->device_name;
            $object->serial_number = $request->serial_number;
            $object->actived = '1';
            $object->deleted = '0';
            $object->save();
            $object->guid = DB::table('terminals')->where('serial_number', $object->serial_number)->first()->id;
            DB::commit();
            $response = [
                'insertedData' => $object
                , 'status' => 1
            ];
            return response()->json($response);
        } catch (Exception $e) {
            $response = [];
            $response['message'] = $e->getMessage();
            $response['status'] = 0;
            $response['code'] = $e->getCode();
            DB::rollback();
            return response()->json($response);
        }
    }

    public function update(Request $request) {
        DB::beginTransaction();
        try {
            $error_messages = [
                // ID
                'id.required' => 'Id không được để trống',
                'id.gui_type' => 'Id đã tồn tại',
                // SERIAL NUMBER
                'serial_number.required' => 'Serial number không được để trống',
                'serial_number.integer' => 'Serial number phải là số',
                'serial_number.min' => 'Serial number có giá trị nhỏ nhất là :min',
                'serial_number.unique' => 'Serial number đã tồn tại',
                // MÃ SITE
                'site_id.required' => 'Mã site không được để trống',
                'site_id.integer' => 'Mã site phải là số',
                'site_id.min' => 'Mã site có giá trị nhỏ nhất là :min',
                // MÃ VỊ TRÍ PHẢI LÀ SỐ
                'location_id.required' => 'Mã vị trí không được để trống',
                'location_id.integer' => 'Mã vị trí phải là số',
                'location_id.min' => 'Mã vị trí có giá trị nhỏ nhất là :min',
                //
                'device_name.required' => 'Tên thiết bị không được để trống',
                'device_name.min' => 'Tên thiết bị có ít nhất :min kí tự',
                'device_name.max' => 'Tên thiết bị có nhiều nhất :max kí tự',
                //
                'actived.required' => 'Tình trạng không được để trống',
                'actived.boolean' => 'Tình trạng không phù hợp'
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validArray = [
                'id' => 'required|gui_type'
                , 'location_id' => 'required|integer|min:1'
                , 'site_id' => 'required|integer|min:1'
                , 'device_name' => 'required|min:1'
                , 'actived' => 'required|boolean'
            ];
            if(isset($request->serial_number)) {
                $validArray['serial_number'] = 'required|min:1|unique:terminals';
            }
            $validator = Validator::make($request->all(), $validArray , $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            // $newId = DB::select("SELECT NEWID() as new_id")[0]->new_id;
            $date = $this->getDateNow();
            $user_id = $request_user->id;
            $object = DefaultModel::where('id', $request->id)->first();
            $object->updated_by = $user_id;
            $object->updated_at = $date;
            $object->location_id = $request->location_id;
            $object->device_name = $request->device_name;
            if(isset($request->serial_number)) {
                $object->serial_number = $request->serial_number;
            }
            if(isset($request->note)) {
                $object->note = $request->note;
            }
            $object->site_id = $request->site_id;
            $object->actived = $request->actived;
            $object->save();
            DB::commit();
            $response = [
                'updatedData' => $object
                , 'status' => 1
            ];
            return response()->json($response);
        } catch (Exception $e) {
            $response = [];
            $response['message'] = $e->getMessage();
            $response['status'] = 0;
            $response['code'] = $e->getCode();
            DB::rollback();
            return response()->json($response);
        }
    }
    public function delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                'id.required' => 'Id không được để trống',
                'id.gui_type' => 'Id không hợp lệ'
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
            $object = DefaultModel::where('id', $id)->delete();
            DB::commit();
            $response = [
                'status' => 1
            ];
            return response()->json($response);
        } catch (Exception $e) {
            DB::rollback();
            $response = [
                'status' => 0
                , 'message' => $e->getMessage()
            ];
            return response()->json($response);
        }
    }
}