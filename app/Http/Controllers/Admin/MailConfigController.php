<?php

namespace App\Http\Controllers\Admin;

use App\Location;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\Terminal;
use App\EmailConfig as DefaultModel;
use Carbon\Carbon;

class MailConfigController extends Controller
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
            $request_user = $request->user();
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|min:1'
            ], $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            $user_id =  $request_user->id;
            $data = [];
            $organization_id = $request->organization_id;
            $deleted = $request->deleted;
            $data['retrieveData'] = DefaultModel::where('organization_id', $organization_id)->first();
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
    // cập nhật vị trí theo site of organization
    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                'organization_id.required' => 'Mã tổ chức không được để trống',
                'organization_id.integer' => 'Mã tổ chức phải là số',
                'organization_id.min' => 'Mã tổ chức có giá trị nhỏ nhất là :min',
                // 
                'server.required' => 'Địa chỉ server không được để trống',
                'server.min' => 'Địa chỉ server có giá trị nhỏ nhất là :min',
                'server.max' => 'Địa chỉ server có giá trị nhỏ nhất là :min',
                //
                'port.required' => 'Địa chỉ cổng không được để trống',
                'port.integer' => 'Địa chỉ có giá trị nhỏ nhất là :min',
                // 
                'user_name.required' => 'Tên người gửi không được để trống',
                'user_name.min' => 'Tên người gửi phải có ít nhất: :min kí tự',
                'user_name.max' => 'Tên người gửi phải có nhiều nhất: :max kí tự',
                //
                'enable_ssl.required' => 'Trạng thái bật SSL không được bỏ trống',
                'enable_ssl.boolean' => 'Trạng thái bật SSL không phù hợp',
                //
                'pass_word.required' => 'Mật khẩu không được để trống',
                'pass_word.min' => 'Mật khẩu có ít nhất: :min kí tự',
                'pass_word.max' => 'Mật khẩu có nhiều nhất: :max kí tự',
                //
                'email.required' => 'Email không được để trống',
                'email.email' => 'Email không phù hợp',
                'email.min' => 'Email có ít nhất: :min kí tự',
                'email.max' => 'Email có nhiều nhất: :max kí tự',
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|min:1'
                , 'server_name' => 'required|min:5|max:100'
                , 'port' => 'required|integer|min:0'
                , 'user_name' => 'required|min:2|max:200'
                , 'email' => 'required|email|min:5|max:200'
                , 'pass_word' => 'required|min:2|max:200'
                , 'enable_ssl' => 'required|boolean'
            ], $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $id = $request->id;
            $request_user = $request->user();
            $date = $this->getDateNow();
            $organization_id = $request->organization_id;
            $currentObject = DefaultModel::where('organization_id', $organization_id)->first();
            $object = $currentObject == null ? new DefaultModel() : $currentObject;
            $object->server = trim($request->server_name);
            $object->port = trim($request->port);
            $object->user_name = trim($request->user_name);
            $object->email = trim($request->email);
            $object->pass_word = trim($request->pass_word);
            $object->updated_at = $date;
            $object->updated_by = $request_user->id;
            $object->enable_ssl = $request->enable_ssl;
            $object->organization_id = $organization_id;
            $object->save();
            DB::commit();
            $response = [];
            $response['status'] = 1;
            $response['updatedData'] = $object;
            return response()->json($response);
        } catch (Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            $response['code'] = $e->getCode();
            $response['line'] = $request->server;
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
                'id' => 'required|numeric|min:1'
                , 'deleted' => 'required|numeric|boolean'
            ], $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $id = $request->id;
            $object = DefaultModel::findOrFail($id);
            if($request->deleted == 1) {
                $object->deleted = 0;
            } else if($request->deleted == 0) {
                $object->deleted = 1;
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
                'id.required' => 'Mã mail config không được để trống',
                'id.integer' => 'Mã mail config phải là số',
                'id.min' => 'Mã mail config có giá trị nhỏ nhất là :min'
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|min:1'
            ], $error_messages);
            if($validator->fails()) {
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
