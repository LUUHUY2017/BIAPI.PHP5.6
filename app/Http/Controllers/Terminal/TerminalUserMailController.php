<?php

namespace App\Http\Controllers\Terminal;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\UserEmailModule as DefaultModel;
use App\Organization;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;
use Mail;
// use Error;

class TerminalUserMailController extends Controller
{
    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'id.required' => 'Id không được để trống',
                'id.integer' => 'Id phải là số',
                'id.min' => 'Id có giá trị nhỏ nhất là: :min',
                'params.required' => 'Mã tài khoản không được để trống',
                'page_id.required' => 'Mã trang không được để trống',
                'site_id.required' => 'Mã site không được để trống',
                'site_id.integer' => 'Mã site không phù hợp',
                'site_id.min' => 'Mã site có giá trị nhỏ nhất là :min',
                'report_type.required' => 'Loại báo cáo không được để trống',
                'report_type.integer' => 'Loại báo cáo phải là số',
                'report_type.min' => 'Loại báo cáo có giá trị nhỏ nhất là: :min',
                'module_id.required' => 'Mã module không được để trống',
                'module_id.integer' => 'Mã module phải là số',
                'module_id.min' => 'Mã module có giá trị nhỏ nhất là: :min',
                'user_id.required' => 'Mã người dùng không được để trống',
                'user_id.integer' => 'Mã người dùng phải là số',
                'user_id.min' => 'Mã người dùng có giá trị nhỏ nhất là: :min',
            ];
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|min:1'
                , 'page_id' =>  'required'
                , 'params' => 'required'
                , 'report_type' => 'required|integer|min:0'
                , 'module_id' => 'required|integer|min:0'
                , 'actived' => 'required|boolean'
                , 'user_id' => 'required|integer|min:1'
            ], $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $date = $this->getDateNow();
            $request_user = $request->user();
            $page_id = $request->page_id;
            $object = DefaultModel::findOrFail($request->id);
            $object->updated_at = $date;
            $object->updated_by = $request_user->id;
            $object->user_id = $request->user_id;
            $object->module_id = $request->module_id;
            $object->site_id = $request->site_id;
            $object->page_id = $request->page_id;
            $object->params = $request->params;
            $object->report_type = $request->report_type;
            $object->actived = $request->actived;
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
            DB::rollback();
            return response()->json($response);
        }
    }
}