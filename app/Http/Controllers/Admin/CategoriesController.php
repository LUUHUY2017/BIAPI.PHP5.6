<?php

namespace App\Http\Controllers\Admin;

use App\Category as DefaultModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;
use Carbon\Carbon;

class CategoriesController extends Controller
{
    public function getData(Request $request)
    {
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'organization_id.required' => 'Mã tổ chức không được để trống',
                'organization_id.integer' => 'Mã tổ chức phải là số',
                'organization_id.min' => 'Mã tổ chức nhỏ nhất phải là :min'
            ];
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|min:1'
            ], $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            $user_id =  $request_user->id;
            $organization_id = $request->organization_id;
            $retrieveData = DB::select("exec sp_get_categories $user_id, $organization_id");
            $response = [];
            $response['status'] = 1;
            $response['retrieveData'] = $retrieveData;
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
        
    }
    public function insert(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'organization_id.required' => 'Mã tổ chức không được để trống',
                'organization_id.integer' => 'Mã tổ chức phải là số',
                'organization_id.min' => 'Mã tổ chức nhỏ nhất phải là :min',
                // Trường tên danh mục
                'category_name.required' => 'Danh mục không được để trống',
                'category_name.min' => 'Danh mục phải có ít nhất :min kí tự',
                'category_name.max' => 'Danh mục có nhiều nhất :max kí tự',
            ];
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|min:1'
                , 'category_name' => 'required|min:3|max:20'
            ], $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $date = $this->getDateNow();
            $request_user = $request->user();
            $object = new DefaultModel();
            $object->organization_id = $request->organization_id;
            $object->category_name = $request->category_name;
            $object->created_by = $request_user->id;
            $object->created_at = $date;
            $object->updated_at = $date;
            $object->updated_by = $request_user->id;
            $object->save();
            DB::commit();
            $response = [];
            $response['status'] = 1;
            $response['insertedData'] = $object;
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
    // cập nhật vị trí theo site of organization
    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'id.required' => 'Mã danh mục không được để trống',
                'id.numeric' => 'Mã danh mục phải là số',
                'id.min' => 'Mã danh mục nhỏ nhất phải là :min',
                // Trường tên danh mục
                'category_name.required' => 'Danh mục không được để trống',
                'category_name.min' => 'Danh mục phải có ít nhất :min kí tự',
                'category_name.max' => 'Danh mục có nhiều nhất :max kí tự',
            ];
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|min:1'
                , 'category_name' => 'required|min:3|max:20'
            ], $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $date = $this->getDateNow();
            $request_user = $request->user();
            $id = (int)$request->id;
            $object = DefaultModel::findOrFail($id);
            $object->category_name = $request->category_name;
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
            DB::table('sites')->where('category_id', $id)->update([
                'category_id' => 0
            ]);
            DefaultModel::where('id', $id)->delete();
            DB::commit();
            $response = [];
            $response['status'] = 1;
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
}
