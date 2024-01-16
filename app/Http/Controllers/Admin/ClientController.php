<?php

namespace App\Http\Controllers\Admin;

use App\Clients;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;
use Carbon\Carbon;

class ClientController extends Controller
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
            $organization_id = $request->organization_id;
            $retrieveData = DB::select("exec sp_get_organization_client $organization_id");
            $response = [];
            $response['status'] = 1;
            $response['retrieveData'] = $retrieveData;
            return response()->json($response);
        } catch (Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            $response['code'] = $e->getCode();
            return response()->json($response, 404);
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
                'name.required' => 'Tên ứng dụng không được để trống',
                'name.min' => 'Tên ứng dụng phải có ít nhất :min kí tự',
                'name.max' => 'Tên ứng dụng có nhiều nhất :max kí tự',
                //
                'secret.required' => 'Mã secret không được để trống',
                'secret.min' => 'Mã secret có ít nhất: :min kí tự',
            ];
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|min:1'
                , 'name' => 'required|min:3|max:100'
                , 'secret' => 'required|min:3'
            ], $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $date = $this->getDateNow();
            $request_user = $request->user();
            $object = new Clients();
            $object->name = $request->name;
            $object->created_at = $date;
            $object->updated_at = $date;
            $object->redirect = $request->redirect;
            $object->revoked = 0;
            $object->password_client = 1;
            $object->personal_access_client = 0;
            $secret = trim($request->secret);
            $object->secret = $this->stripUnicode($secret);
            $object->redirect = 'http://localhost';
            $object->save();
            DB::table('organization_clients')->insert([
                'created_at' => $date
                , 'updated_at' => $date
                , 'created_by' => $request_user->id
                , 'updated_by' => $request_user->id
                , 'organization_id' => $request->organization_id
                , 'client_id' => $object->id
            ]);
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
            $response['line'] = $e->getLine();
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
                'id.required' => 'Mã ứng dụng không được để trống',
                'id.integer' => 'Mã ứng dụng phải là số',
                'id.min' => 'Mã ứng dụng nhỏ nhất phải là :min',
                // Trường tên danh mục
                'name.required' => 'Tên ứng dụng không được để trống',
                'name.min' => 'Tên ứng dụng phải có ít nhất :min kí tự',
                'name.max' => 'Tên ứng dụng có nhiều nhất :max kí tự',
                //
                'secret.required' => 'Mã secret không được để trống',
                'secret.min' => 'Mã secret có ít nhất: :min kí tự',
            ];
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|min:1'
                , 'name' => 'required|min:3|max:100'
                , 'secret' => 'required|min:3'
            ], $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $date = $this->getDateNow();
            $request_user = $request->user();
            $object = Clients::findOrFail($request->id);
            $object->updated_at = $date;
            $object->name = $request->name;
            $object->secret = $request->secret;
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
                'id.required' => 'Mã ứng dụng không được để trống'
                , 'id.min' => 'Mã ứng dụng có giá trị nhỏ nhất là :min'
                , 'id.integer' => 'Mã ứng dụng phải là giá trị số'
            ];
            $request_user = $request->user();
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|min:1'
            ], $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $id = $request->id;
            Clients::where('id', $id)->delete();
            DB::table('organization_clients')->where('client_id', $id)->delete();
            DB::table('oauth_access_tokens')->where('client_id', $id)->delete();
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
