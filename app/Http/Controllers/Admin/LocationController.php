<?php

namespace App\Http\Controllers\Admin;

use App\Location;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\Terminal;
use App\FbaTablet;
use Carbon\Carbon;

class LocationController extends Controller
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
                'organization_id' => 'required|integer|min:1'
                , 'deleted' => 'required|boolean'
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
            $data['retrieveData'] = DB::select("exec sp_fba_get_locations $user_id, $organization_id, $deleted, 0");
            $data['siteArray'] = DB::select("SELECT * FROM fc_get_site_in_role($organization_id, $user_id)");
            $data['moduleArray'] = $this->getDefaultModule();
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

    public function get_location_tablets(Request $request)
    {

        $request_user = $request->user();
        $user_id =  $request_user->id;
        $site_id = $request->site_id;
        $module = $request->module;
        if ($site_id) {
            $location_tablets = DB::select(" exec sp_fba_get_location_tablets $site_id,  $module");
        } else {
            $location_tablets = array();
        };
        $location_tablet = array();
        foreach ($location_tablets as $item) {
            $location_tablet[] =  array('label' => $item->location_name, 'value' => strval($item->id));
        }
        return response()->json(['location_tablet' => $location_tablet]);
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
                'site_id.required' => 'Mã địa điểm không được để trống',
                'site_id.integer' => 'Mã địa điểm phải là số',
                'site_id.min' => 'Mã địa điểm có giá trị nhỏ nhất là :min',
                // Trường tên địa điểm
                'location_name.required' => 'Tên vị trí không được để trống',
                'location_name.min' => 'Tên vị trí phải có ít nhất :min kí tự',
                'location_name.max' => 'Tên vị trí có nhiều nhất :max kí tự',
                // Trường mã địa điểm
                'location_code.required' => 'Mã vị trí không được để trống',
                'location_code.min' => 'Mã vị trí phải có ít nhất :min kí tự',
                'location_code.max' => 'Mã vị trí có nhiều nhất :max kí tự',
                // Trường mã địa điểm
                'module.required' => 'Module không được để trống',
                'module.integer' => 'Module phải là số',
                'module.min' => 'Module phải có giá trị ít nhất là :min',
                'module.max' => 'Module phải có giá trị nhiều nhất là :max',
                //
                'actived.required' => 'Mã hoạt động không được bỏ trống',
                'actived.boolean' => 'Mã hoạt động không phù hợp'

            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|min:1'
                , 'site_id' => 'required|integer|min:1'
                , 'location_name' => 'required|min:5|max:100'
                , 'module' => 'required|integer|min:1|max:5'
                , 'location_code' => 'required|min:3|max:100'
                , 'actived' => 'required|boolean'
            ], $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            $date = $this->getDateNow();
            $object = new Location;
            $object->organization_id = $request->organization_id;
            $object->site_id = $request->site_id;
            $object->location_name = trim($request->location_name);
            $object->location_code = trim($this->stripUnicode($request->location_code));
            if(isset($request->location_description)) {
                $object->location_description = $request->location_description;
            }
            $object->module = $request->module;
            $object->created_at = $date;
            $object->updated_at = $date;
            $object->created_by = $request_user->id;
            $object->updated_by = $request_user->id;
            $object->actived = $request->actived;
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
                'id.required' => 'ID vị trí không được để trống',
                'id.integer' => 'ID vị trí phải là số',
                'id.min' => 'ID vị trí có giá trị nhỏ nhất là :min',
                // Mã địa điểm phải là số
                'site_id.required' => 'Mã địa điểm không được để trống',
                'site_id.integer' => 'Mã địa điểm phải là số',
                'site_id.min' => 'Mã địa điểm có giá trị nhỏ nhất là :min',
                // Trường tên địa điểm
                'location_name.required' => 'Tên vị trí không được để trống',
                'location_name.min' => 'Tên vị trí phải có ít nhất :min kí tự',
                'location_name.max' => 'Tên vị trí có nhiều nhất :max kí tự',
                // Trường mã địa điểm
                'location_code.required' => 'Mã vị trí không được để trống',
                'location_code.min' => 'Mã vị trí phải có ít nhất :min kí tự',
                'location_code.max' => 'Mã vị trí có nhiều nhất :max kí tự',
                // Trường mã địa điểm
                'module.required' => 'Module không được để trống',
                'module.min' => 'Module phải có giá trị ít nhất là :min',
                'module.max' => 'Module phải có giá trị nhiều nhất là :max',
                //
                'actived.required' => 'Mã hoạt động không được bỏ trống',
                'actived.boolean' => 'Mã hoạt động không phù hợp'
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|min:1'
                , 'site_id' => 'required|integer|min:1'
                , 'location_name' => 'required|min:5|max:100'
                , 'location_code' => 'required|min:3|max:200'
                , 'actived' => 'required|boolean'
                , 'module' => 'required|integer|min:1|max:5'
            ], $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $id = $request->id;
            $request_user = $request->user();
            $date = $this->getDateNow();
            $object = Location::findOrFail($id);
            $object->site_id = $request->site_id;
            $object->location_name = trim($request->location_name);
            $object->location_code = trim($request->location_code);
            if(isset($request->location_description)) {
                $object->location_description = $request->location_description;
            }
            $object->updated_at = $date;
            $object->updated_by = $request_user->id;
            $object->actived = $request->actived;
            $object->module = $request->module;
            $object->save();
            Terminal::where('location_id', $id)->update(['actived' => $request->actived]);
            FbaTablet::where('location_id', $id)->update(['actived' => $request->actived]);
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
                'id' => 'required|integer|min:1'
                , 'deleted' => 'required|boolean'
            ], $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $id = $request->id;
            $object = Location::findOrFail($id);
            if($request->deleted == 1) {
                $object->deleted = 0;
                Terminal::where('location_id', $id)->update(['deleted' => 0]);
                FbaTablet::where('location_id', $id)->update(['deleted' => 0]);
            } else if($request->deleted == 0) {
                $object->deleted = 1;
                Terminal::where('location_id', $id)->update(['deleted' => 1]);
                FbaTablet::where('location_id', $id)->update(['deleted' => 1]);
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
                'id' => 'required|integer|min:1'
            ], $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $id = $request->id;
            Terminal::where('location_id', $id)->delete();
            FbaTablet::where('location_id', $id)->delete();
            Location::where('id', $id)->delete();
            DB::commit();
            return response()->json(['status' => 1]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 0, 'message' => $e->getMessage()]);
        }
    }
}
