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

class EmailController extends Controller
{
    public function getEmailConfig(Request $request)
    {
        try {
            $request_user = $request->user();
            $userId =  $request_user->id;
            $moduleId = $request->module_id;
            $retrieveData = DB::select("SELECT * FROM user_email_module WHERE module_id = $moduleId AND user_id = $userId");
            $pageData = DB::table('pages')->where('page_module', $moduleId)->get();
            $now = $this->getDateNow();
            return response()->json(['retrieveData' => $retrieveData, 'pageData' => $pageData, 'recordDate' => $now, 'status' => 1]);
        } catch (Exception $e) {
            return response()->json(['status' => 0, 'errMsg' => $e->getMessage()]);
        }
    }
    public function addReceiveReportEmail(Request $request)
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
            $endPoint = $page !== null ? $page->end_point : null;
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
            $object->save();
            $siteInfo = Site::tryGetOrganizationInfo($jsonParam->site_id, $jsonParam->organization_id);
            if($siteInfo === null) {
                $json_error = json_encode(['Dữ liệu không phù hợp']);
                throw new Exception($json_error, 770);
            } else {
                $object->site_name = $siteInfo->site_name;
                $object->organization_name = $siteInfo->organization_name;
            }
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

    public function updateReceiveReportEmail(Request $request)
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
                , 'site_id' => 'required|integer|min:0'
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
            $endPoint = $page !== null ? $page->end_point : null;
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
            $siteInfo = Site::tryGetOrganizationInfo($jsonParam->site_id, $jsonParam->organization_id);
            if($siteInfo === null) {
                $json_error = json_encode(['Dữ liệu không phù hợp']);
                throw new Exception($json_error, 770);
            } else {
                $object->site_name = $siteInfo->site_name;
                $object->organization_name = $siteInfo->organization_name;
            }
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

    public function getData(Request $request) {
        DB::beginTransaction();
        try {
            $error_messages = [
                'organization_id.required' => 'Mã tổ chức không được để trống'
                , 'organization_id.min' => 'Mã tổ chức có giá trị nhỏ nhất là :min'
                , 'organization_id.integer' => 'Mã tổ chức không phù hợp'
                , 'page_id.required' => 'Mã page không được để trống'
            ];
            $request_user = $request->user();
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|min:1'
                , 'page_id' => 'required'
            ], $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $page_id = $request->page_id;
            $defaultModel = new DefaultModel();
            $object = $defaultModel->tryGetDataWithPageId($page_id);
            $response = [];
            $response['status'] = 1;
            $response['retrieveData'] = $object;
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

    public function deleteReceiveReportEmail(Request $request)
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
}
