<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\UserEmailModule as DefaultModel;
use App\User;
use App\Page;
use Exception;

class PageEmailController extends Controller
{
    public function getConfig(Request $request)
    {
        try {
            $request_user = $request->user();
            $userId =  $request_user->id;
            $moduleId = $request->module_id;
            $retrieveData = DB::select("SELECT * FROM user_email_module WHERE module_id = $moduleId AND user_id = $userId");
            $pageData = DB::table('pages')->where('page_module', $moduleId)->get();
            $now = $this->getDateNow();
            $response = [
                'retrieveData' => $retrieveData
                , 'pageData' => $pageData
                , 'recordDate' => $now
                , 'status' => 1
            ];
            return response()->json($response);
        } catch (Exception $e) {
            return response()->json(['status' => 0, 'errMsg' => $e->getMessage()]);
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
                , 'module_id' => 'required|integer|min:1'
            ], $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $page_id = $request->page_id;
            $module_id = $request->module_id;
            $defaultModel = new DefaultModel();
            $object = $defaultModel->tryGetDataWithPageId($page_id, $module_id);
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
}
