<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\UserEmailModule as DefaultModel;
use App\User;
use App\Page;
use App\Site;
use Exception;

class UserMailReportController extends Controller
{
    public function insert(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'params.required' => 'Params không được để trống',
                'page_id.required' => 'Mã trang không được để trống',
                'site_id.required' => 'Mã site không được để trống',
                'site_id.integer' => 'Mã site không phù hợp',
                'site_id.min' => 'Mã site có giá trị nhỏ nhất là: :min',
                'report_type.required' => 'Loại báo cáo không được để trống',
                'report_type.integer' => 'Loại báo cáo phải là số',
                'report_type.min' => 'Loại báo cáo có giá trị nhỏ nhất là: :min',
                'module_id.required' => 'Mã module không được để trống',
                'module_id.integer' => 'Mã module phải là số',
                'module_id.min' => 'Mã module có giá trị nhỏ nhất là: :min',
                'user_id.required' => 'Mã người dùng không được để trống',
                'user_id.integer' => 'Mã người dùng phải là số',
                'user_id.min' => 'Mã người dùng có giá trị ít nhất là: :min',
                'organization_id.required' => 'Mã tổ chức không được để trống',
                'organization_id.integer' => 'Mã tổ chức phải là số',
                'organization_id.min' => 'Mã tổ chức có giá trị ít nhất là: :min'
            ];
            $validator = Validator::make($request->all(), [
                'page_id' =>  'required'
                , 'params' => 'required'
                , 'report_type' => 'required|integer|min:0'
                , 'module_id' => 'required|integer|min:0'
                , 'user_id' => 'required|integer|min:0'
                , 'organization_id' => 'required|integer|min:0'
                , 'actived' => 'required|boolean'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $page_id = $request->page_id;
            $page = DB::table('pages')->where('id', $page_id)->first();
            if($page == null) {
                $json_error = json_encode(['page end point not found']);
                throw new Exception($json_error, 770);
            }
            $endPoint = $page->end_point;
            $jsonParam = json_decode($request->params);
            $jsonParam->end_point = $endPoint;
            $date = $this->getDateNow();
            $request_user = $request->user();
            $object = new DefaultModel();
            $object->created_at = $date;
            $object->updated_at = $date;
            $object->created_by = $request_user->id;
            $object->updated_by = $request_user->id;
            $object->user_id = $request->user_id;
            $object->page_id = $page_id;
            $object->organization_id = $request->organization_id;
            $object->params = json_encode($jsonParam);
            $object->deleted = 0;
            $object->actived = $request->actived;
            $object->report_type = $request->report_type;
            $object->module_id = $request->module_id;
            $object->secret_key = $this->randomMd5String(32);
            $object->deleted = 0;
            $object->save();
            DB::commit();
            $response = [];
            $response['status'] = 1;
            $response['insertedData'] = $object;
            return response()->json($response);
        } catch (Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            $response['code'] = $e->getCode();
            $response['line'] = $e->getLine();
            DB::rollback();
            return response()->json($response);
        }
    }

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
                'module_id.min' => 'Mã module có giá trị nhỏ nhất là: :min'
            ];
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|min:1'
                , 'page_id' =>  'required'
                , 'params' => 'required'
                , 'report_type' => 'required|integer|min:0'
                , 'module_id' => 'required|integer|min:0'
                , 'actived' => 'required|boolean'
            ], $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $date = $this->getDateNow();
            $request_user = $request->user();
            $page_id = $request->page_id;
            $page = DB::table('pages')->where('id', $page_id)->first();
            if($page == null) {
                $json_error = json_encode(['page end point not found']);
                throw new Exception($json_error, 770);
            }
            $endPoint = $page->end_point;
            $jsonParam = json_decode($request->params);
            $jsonParam->end_point = $endPoint;
            $object = DefaultModel::findOrFail($request->id);
            $object->updated_at = $date;
            $object->updated_by = $request_user->id;
            $object->page_id = $request->page_id;
            $object->params = json_encode($jsonParam);
            $object->module_id = $request->module_id;
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

    public function delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                'id.required' => 'Mã tài khoản không được để trống'
                , 'id.min' => 'Mã tài khoản có giá trị nhỏ nhất là :min'
                , 'id.integer' => 'Mã tài khoản phải là giá trị số'
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
            $object = DefaultModel::where('id', $id)->delete();
            DB::commit();
            $response = [];
            $response['status'] = 1;
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

    public function unsubcribeNotification(Request $request)
    {
        DB::beginTransaction();
        try {
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'secret_key' => 'required|min:1'
            ]);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $secret_key = $request->secret_key;
            $object = DefaultModel::where('secret_key', $secret_key)->first();
            if ($object == null) {
                $json_error = json_encode(['Đường link không tồn tại']);
                throw new Exception($json_error, 770);
            }
            if($object->deleted == 1) {
                $json_error = json_encode(['Đường link không tồn tại']);
                throw new Exception($json_error, 770);
            } else if($object->deleted == 0) {
                $object->deleted = 1;
            }
            $object->save();
            DB::commit();
            $response = [
                'status' => 1
                , 'object' => $object
            ];
            return response()->json($response);
        } catch (Exception $e) {
            DB::rollback();
            $response = [
                'status' => 0
                , 'message' => $e->getMessage()
                , 'code' => $e->getCode()
            ];
            return response()->json($response);
        }
    }

    public function descriptionMail(Request $request)
    {
        try {
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'id' => 'required|min:1'
                , 'secret_key' => 'required|min:1'
            ]);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $secret_key = $request->secret_key;
            $id = $request->id;
            $retrieveData = DB::table('send_emails')->join('send_email_details', 'send_emails.id', '=', 'send_email_details.send_mail_id')->where('send_email_details.send_mail_id', $id)->where('send_email_details.secret_key', $secret_key)->select('send_email_details.*')->first();
            if ($retrieveData == null) {
                $json_error = json_encode(['Đã có lỗi xảy ra']);
                throw new Exception($json_error, 770);
            }
            $response = [
                'status' => 1
                , 'retrieveData' => $retrieveData
            ];
            return response()->json($response);
        } catch (Exception $e) {
            $response = [
                'status' => 0
                , 'message' => $e->getMessage()
                , 'code' => $e->getCode()
            ];
            return response()->json($response);
        }
    }
    public function unsubcribeSchedule(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                'secret_key.required' => 'Mã lịch mail không được để trống'
            ];
            $request_user = $request->user();
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'secret_key' => 'required|min:1'
            ], $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $secret_key = $request->secret_key;
            $object = DefaultModel::where('secret_key', $secret_key)->first();
            if ($object == null) {
                $json_error = json_encode(['Đường link không tồn tại']);
                throw new Exception($json_error, 770);
            }
            DefaultModel::where('secret_key', $secret_key)->delete();
            DB::commit();
            $response = [];
            $response['status'] = 1;
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
    public function checkExistParam(Request $request)
    {
        try {
            DB::enableQueryLog();
            $request_user = $request->user();
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'params' => 'required'
                , 'page_id' => 'required'
                , 'report_type' => 'required|integer|min:1'
            ]);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $page_id = '\'' . $request->page_id . '\'';
            $report_type = $request->report_type;
            $pageInfo = DB::table('pages')->where('id', $request->page_id)->first();
            if ($pageInfo == null) {
                $json_error = json_encode(['Không tồn tại page']);
                throw new Exception($json_error, 770);
            }
            $end_point = $pageInfo->end_point;
            $jsonParam = json_decode($request->params);
            $jsonParam->end_point = $end_point;
            $encodedParam = json_encode($jsonParam);
            $retrieveData = DefaultModel::where('page_id', $request->page_id)->where('params', $encodedParam)->where('report_type', $report_type)->where('user_id', $request_user->id)->first();
            $lastQuery = DB::getQueryLog();
            $response = [];
            $response['status'] = 1;
            $response['retrieveData'] = $retrieveData;
            $response['lastQuery'] = $lastQuery;
            return response()->json($response);
        } catch (Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            $response['code'] = $e->getCode();
            return response()->json($response);
        }
    }
}
