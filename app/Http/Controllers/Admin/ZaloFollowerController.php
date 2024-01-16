<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Carbon\Carbon;
use Exception;
use App\ZaloFollower as DefaultModel;
use Illuminate\Support\Facades\Validator;
class ZaloFollowerController extends Controller
{
    public function get_oa_zalo(Request $request) {
        $request_user = $request->user();
        $user_id = $request_user->id;
        $organization_id = $request->organization_id;
        $response = [];
        $response['siteArray'] = DB::select("SELECT * FROM fc_get_site_in_role($organization_id, $user_id)");
        $response['oa_array'] = DB::table('oa_zalo')->where('actived', 1)->where('deleted', 0)->where('organization_id', $organization_id)->get();
        return response()->json($response);
    }
    public function getData(Request $request) {
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'organization_id.required' => 'Mã tổ chức không được để trống',
                'organization_id.integer' => 'Mã tổ chức phải là số',
                'organization_id.min' => 'Mã tổ chức nhỏ nhất phải là :min',
                'deleted.required' => 'Mã deleted không được để trống',
                'deleted.boolean' => 'Mã deleted không phù hợp'
            ];
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|min:1'
                // , 'deleted' => 'required|boolean'
            ], $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            $user_id =  $request_user->id;
            $organization_id = $request->organization_id;
            // $deleted = $request->deleted;
            $defaultModel = new DefaultModel();
            $retrieveData = $defaultModel->tryGetData($organization_id);
            $response = [];
            $response['status'] = 1;
            $response['retrieveData'] = $retrieveData;
            $response['siteArray'] = DB::select("SELECT * FROM fc_get_site_in_role($organization_id, $user_id)");
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }


    public function update(Request $request) {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'id.required' => 'Mã người dùng không được để trống',
                'id.numeric' => 'Mã người dùng phải là số',
                'id.min' => 'Mã người dùng nhỏ nhất phải là :min',
                //
                'site_id.required' => 'Mã địa điểm không được để trống',
                'site_id.numeric' => 'Mã địa điểm phải là số',
                'site_id.min' => 'Mã địa điểm nhỏ nhất phải là :min',
                //
                'actived.required' => 'Mã hoạt động không được để trống',
                'actived.numeric' => 'Mã hoạt động phải là số',
                'actived.between' => 'Mã hoạt động là :min hoặc :max',
                // Trường tên tài khoản
                'account_name.required' => 'Tên tài khoản không được để trống',
                'account_name.without_space' => 'Tên tài khoản chỉ bao gồm kí tự A-Z, 0-9, gạch ngang và gạch dưới',
                'account_name.min' => 'Tên tài khoản phải có ít nhất :min kí tự',
                'account_name.max' => 'Tên tài khoản có nhiều nhất :max kí tự',
                // Trường mật khẩu
                'password.required' => 'Mật khẩu không được để trống',
                'password.without_space' => 'Mật khẩu chỉ bao gồm kí tự A-Z, 0-9, gạch ngang và gạch dưới',
                'password.min' => 'Mật khẩu phải có ít nhất :min kí tự',
                'password.max' => 'Mật khẩu có nhiều nhất :max kí tự',
                'same' => 'Mật khẩu nhập vào không trùng khớp',
                // Trường account_type
                'follower_name.required' => 'Tên hiển thị không được để trống',
                'follower_name.min' => 'Tên hiển thị phải có ít nhất :min kí tự',
                'follower_name.max' => 'Tên hiển thị phải có nhiều nhất là :max kí tự',
            ];
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|min:1'
                , 'site_id' => 'required|integer|min:0'
                , 'actived' => 'required|boolean'
                , 'follower_name' => 'required|min:3|max:100'
            ], $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $date = $this->getDateNow();
            $request_user = $request->user();
            $id = $request->id;
            $object = DefaultModel::findOrFail($id);
            $object->site_id = $request->site_id;
            $object->actived = $request->actived;
            $object->follower_name = $request->follower_name;
            $object->updated_at = $date;
            $object->updated_by = $request_user->id;
            $object->save();
            DB::commit();
            $response = [];
            $response['status'] = 1;
            $response['updatedData'] = $object;
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            $response['code'] = $e->getCode();
            DB::rollback();
            return response()->json($response);
        }
    }


    public function softDelete(Request $request) {
        DB::beginTransaction();
        try {
            $message = [
                'id.required' => 'Mã tài khoản không được để trống',
                'id.numeric' => 'Mã tài khoản phải là số',
                'id.min' => 'Mã tài khoản nhỏ nhất phải là :min',
                // Mã delete
                'deleted.required' => 'Mã deleted không được để trống',
                'deleted.numeric' => 'Mã deleted phải là số',
                'deleted.between' => 'Mã deleted chỉ là :min hoặc :max',
            ];
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|min:1'
                , 'deleted' => 'required|numeric|between:0,1'
            ], $message);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $updateData = [];
            // Nếu là rollback lại
            if($request->deleted == 1) {
                $updateData['actived'] = 1;
                $updateData['deleted'] = 0;
            } else if($request->deleted == 0) { // Nếu là đánh dấu xóa
                $updateData['actived'] = 0;
                $updateData['deleted'] = 1;
            }
            $id = $request->id;
            DefaultModel::where('id', $id)->update($updateData);
            DB::commit();
            $response = [];
            $response['status'] = 1;
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
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
                'id.required' => 'Mã danh mục không được để trống'
                , 'id.min' => 'Mã danh mục có giá trị nhỏ nhất là :min'
                , 'id.integer' => 'Mã danh mục phải là giá trị số'
            ];
            $request_user = $request->user();
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|min:1'
            ], $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $id = $request->id;
            $status = DB::table('event_account_follower')->where('follower_id', $id)->delete();
            DefaultModel::where('id', $id)->delete();
            DB::commit();
            $response = [];
            $response['status'] = 1;
            $response['delete'] = $status;
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
}
