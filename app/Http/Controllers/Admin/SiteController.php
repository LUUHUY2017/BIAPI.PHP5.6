<?php

namespace App\Http\Controllers\Admin;

use App\Site as DefaultModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ErrorHandleController as Error;
use Exception;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Category;

class SiteController extends Controller
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
            $user_id = $request_user->id;
            $organization_id = $request->organization_id;
            $deleted = $request->deleted;
            $data = [];
            $data['categoryData'] = Category::where('organization_id', $organization_id)->get();
            $data['retrieveData'] = DefaultModel::tryGetDataCrud($organization_id, $deleted);
            $data['siteData'] = DefaultModel::tryGetSiteInRole($organization_id);
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

    public function get_site_for_report(Request $request)
    {
        try {
            $request_user = $request->user();
            $user_id = (int)$request_user->id;
            $lever = (int) $request_user->lever; // Lever người dùng
            $user_org = (int) $request_user->organization_id; // Tổ chức người dùng
            // Check kiểu dữ liệu truyền vào
            $valid = Validator::make($request->all(), [
                'organization_id' => 'required|integer|min:0'
            ]);
            // Nếu check ok.
            if(!$valid->fails()) {
                // Nếu là người dùng thuộc tổ chức
                $organization_id = $user_org;
                // Nếu không thuộc tổ chức
                if($lever == 0 && $user_org == 0) {
                    $organization_id = $request->organization_id;
                }
                $data = DB::select("SELECT * FROM fc_get_site_in_role($organization_id,$user_id)");
                return response()->json(['site_array' => $data]);
            } else {
                throw new Exception('Lỗi kiểu dữ liệu', 770);
            }
            
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }
    public function index_tree(Request $request)
    {
        $request_user = $request->user();
        $user_id = $request_user->id;
        $organization_id = $request->organization_id;
        $site_id = $request->site_id;
        if (!$site_id)
            $site_id = 0;
        $retVals = DB::select("exec sp_get_site_tree $user_id, $organization_id, $site_id");
        $retVal = array();
        foreach ($retVals as $value) {
            $retVal[] = array(
                'site_id' => $value->site_id,
                'open_hour' => $value->open_hour,
                'close_hour' => $value->close_hour,
                'alevel' => $value->alevel,
                'store' => (bool)$value->store,
                'organization_id' => $value->organization_id,
                'parent_id' => $value->parent_id,
                'site_code' => $value->site_code,
                'site_name' => $value->site_name,
                'site_shortname' => $value->site_shortname,
                'category_id' => $value->category_id,
            );
        }
        return response()->json($retVal);
    }
    
    public function softDelete(Request $request)
    {
        try {
            $validatorArray = [
                'id' => 'required|integer|min:1'
                , 'deleted' => 'required|boolean'
            ];
            $validator = Validator::make($request->all(), $validatorArray);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $id = $request->id;
            $deleted = $request->deleted == 1 ? 0 : 1;
            $delete = DB::select("exec sp_soft_delete_parent_site $id, $deleted");
            if ($delete[0]->result == 0) {
                $json_error = json_encode(['Thực hiện xóa site không thành công']);
                throw new Exception($json_error, 770);
            }
            $response = [];
            $response['status'] = 1;
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
    public function insert(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'organization_id.required' => 'Mã tổ chức không được để trống',
                'organization_id.integer' => 'Mã tổ chức phải là số',
                'organization_id.min' => 'Mã tổ chức có giá trị nhỏ nhất là :min',
                // Trường mã cha
                'parent_id.required' => 'Mã cha không được để trống',
                'parent_id.integer' => 'Mã cha phải là số',
                'parent_id.min' => 'Mã cha phải có ít nhất :min kí tự',
                // Trường mã code site
                'site_code.required' => 'Mã địa điểm không được để trống',
                'site_code.min' => 'Mã địa điểm phải có ít nhất :min kí tự',
                'site_code.max' => 'Mã địa điểm phải có nhiều nhất :max kí tự',
                // Trường tên viết tắt
                'site_shortname.required' => 'Tên tóm tắt không được để trống',
                'site_shortname.min' => 'Tên tóm tắt phải có ít nhất :min kí tự',
                'site_shortname.max' => 'Tên tóm tắt phải có nhiều nhất :max kí tự',
                // Trường tên địa điểm
                'site_name.required' => 'Tên địa điểm không được để trống',
                'site_name.min' => 'Tên địa điểm phải có ít nhất :min kí tự',
                'site_name.max' => 'Tên địa điểm phải có nhiều nhất :max kí tự',
                // Trường mã code site
                'site_code.required' => 'Mã địa điểm không được để trống',
                'site_code.min' => 'Mã địa điểm phải có ít nhất :min kí tự',
                'site_code.max' => 'Mã địa điểm phải có nhiều nhất :max kí tự',
                // Trường Email
                'store.between' => 'Mã store có giá trị từ :min đến :max',
                'store.integer' => 'Mã store phải là số',
                // Trường Open Hour
                'open_hour.required' => 'Giờ mở cửa không được để trống',
                'close_hour.required' => 'Giờ đóng cửa không được để trống',
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validatorArray = [
                'organization_id' => 'required|integer|min:1'
                , 'parent_id' => 'required|integer|min:0' // bao gồm cả dấu cách
                , 'site_code' => 'required|min:2|max:100'
                , 'site_shortname' => 'required|min:2|max:100'
                , 'site_name' => 'required|min:2|max:100'
                , 'store' => 'required|boolean'
            ];
            if($request->store) {
                $validatorArray['open_hour'] = 'required';
                $validatorArray['close_hour'] = 'required';
            }
            $validator = Validator::make($request->all(),$validatorArray, $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            $date = Carbon::now()->format('Y-m-d H:i:s');
            $object = new DefaultModel();
            $user_id = $request_user->id;
            $object->created_by = $user_id;
            $object->created_at = $date;
            $object->updated_by = $user_id;
            $object->updated_at = $date;
            $object->organization_id = $request->organization_id;
            $object->parent_id = $request->parent_id;
            $object->site_code = trim($this->stripUnicode($request->site_code));
            $object->site_shortname = trim($request->site_shortname);
            $object->site_name = trim($request->site_name);
            if(isset($request->category_id)) {
                $object->category_id = $request->category_id;
            }
            if(isset($request->site_description)) {
                $object->site_description = trim($request->site_description);
            }
            $object->store = $request->store;
            if ($request->store) {
                $object->open_hour = $request->open_hour;
                $object->close_hour = $request->close_hour;
            } else {
                $object->open_hour = null;
                $object->close_hour = null;
            }
            $object->save();
            // nếu thêm mới nút cha thì thêm tự động vào role cha
            if ($object->parent_id == 0) {
                $roleArray = DB::select("SELECT * FROM fc_get_parent_role ($object->organization_id) WHERE role_type = 0");
                if (count($roleArray) === 0) {
                    throw new Exception(json_encode(['Tổ chức có lỗi xảy ra']), 770);
                }
                $defaultRole = $roleArray[0];
                DB::table('role_site')->insert([
                    'role_id' => $defaultRole->id
                    , 'site_id' => $object->id
                    , 'organization_id' => $object->organization_id
                ]);
            }
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
    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'id.required' => 'Mã địa điểm không được để trống',
                'id.integer' => 'Mã địa điểm phải là số',
                'id.min' => 'Mã địa điểm có giá trị nhỏ nhất là :min',
                // Trường mã cha
                'parent_id.required' => 'Mã cha không được để trống',
                'parent_id.integer' => 'Mã cha phải là số',
                'parent_id.min' => 'Mã cha phải có ít nhất :min kí tự',
                // Trường mã code site
                'site_code.required' => 'Mã địa điểm không được để trống',
                'site_code.min' => 'Mã địa điểm phải có ít nhất :min kí tự',
                'site_code.max' => 'Mã địa điểm phải có nhiều nhất :max kí tự',
                // Trường tên viết tắt
                'site_shortname.required' => 'Tên tóm tắt không được để trống',
                'site_shortname.min' => 'Tên tóm tắt phải có ít nhất :min kí tự',
                'site_shortname.max' => 'Tên tóm tắt phải có nhiều nhất :max kí tự',
                // Trường tên địa điểm
                'site_name.required' => 'Tên địa điểm không được để trống',
                'site_name.min' => 'Tên địa điểm phải có ít nhất :min kí tự',
                'site_name.max' => 'Tên địa điểm phải có nhiều nhất :max kí tự',
                // Trường mã code site
                'site_code.required' => 'Mã địa điểm không được để trống',
                'site_code.min' => 'Mã địa điểm phải có ít nhất :min kí tự',
                'site_code.max' => 'Mã địa điểm phải có nhiều nhất :max kí tự',
                // Trường Email
                'store.between' => 'Mã store có giá trị từ :min đến :max',
                'store.integer' => 'Mã store phải là số',
                // Trường Open Hour
                'open_hour.required' => 'Giờ mở cửa không được để trống',
                'close_hour.required' => 'Giờ đóng cửa không được để trống',
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validatorArray = [
                'id' => 'required|integer|min:1'
                , 'parent_id' => 'required|integer|min:0' // bao gồm cả dấu cách
                , 'site_code' => 'required|min:2|max:100'
                , 'site_shortname' => 'required|min:2|max:100'
                , 'site_name' => 'required|min:2|max:100'
                , 'store' => 'required|boolean'
            ];
            if($request->store) {
                $validatorArray['open_hour'] = 'required';
                $validatorArray['close_hour'] = 'required';
            }
            $validator = Validator::make($request->all(),$validatorArray, $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            $id = $request->id;
            $date = Carbon::now()->format('Y-m-d H:i:s');
            $object = DefaultModel::findOrFail($id);
            $object->site_code = $this->stripUnicode($request->site_code);
            $object->site_shortname = trim($request->site_shortname);
            $object->site_name = trim($request->site_name);
            if(isset($request->category_id)) {
                $object->category_id = $request->category_id;
            }
            $object->parent_id = $request->parent_id;
            $object->actived = $request->actived;
            $object->store = $request->store;
            $object->updated_at = $date;
            $object->updated_by = $request_user->id;
            if ($request->store) {
                $object->store = $request->store;
                $object->open_hour = $request->open_hour;
                $object->close_hour = $request->close_hour;
            }
            $object->save();
            $exec = DB::select("exec sp_update_parent_site_id $object->id, $object->actived");
            if($exec[0]->result == 0) {
                $json_error = json_encode(["Execute ['sp_update_parent_site_id'] not success"]);
                throw new Exception($json_error, 770);
            }
            $roleArray = DB::select("SELECT * FROM fc_get_parent_role($object->organization_id) WHERE role_type = 0");
            if (count($roleArray) === 0) {
                $json_error = json_encode(["Lỗi role"]);
                throw new Exception($json_error, 770);
            }
            $defaultRole = $roleArray[0];
            // Đầu tiên là xóa ở trong parent Role đi (nếu có)
            $deleteParent = DB::table('role_site')->where([
                'role_id' => $defaultRole->id
                , 'site_id' => $object->id
                , 'organization_id' => $object->organization_id
            ])->delete();
            $insertParentRole = false;
            // Chuyển lại vào parent Role

            if ($object->parent_id == 0) {
                $insertParentRole = DB::table('role_site')->insert([
                    'role_id' => $defaultRole->id
                    , 'site_id' => $object->id
                    , 'organization_id' => $object->organization_id
                ]);
            }
            // nếu cập nhật từ nút con thành nút cha
            // if ($old_site != 0) {
            //     if ($object->parent_id == 0) {
            //         // xóa tại các role cha
            //         $role_array = DB::select("SELECT * FROM fc_get_parent_role ($object->organization_id)");
            //         foreach ($role_array as $value) {
            //             // Xóa dữ liệu cũ
            //             DB::table('role_site')->where([
            //                 'role_id' => $value->id
            //                 , 'site_id' => $object->id
            //                 , 'organization_id' => $object->organization_id
            //             ])->delete();
            //             // Thêm dữ liệu mới
            //             DB::table('role_site')->insert([
            //                 'role_id' => $value->id
            //                 , 'site_id' => $object->id
            //                 , 'organization_id' => $object->organization_id
            //             ]);
            //         }
            //     }
            //     // Nếu chuyển từ nút cha thành nút con
            // } else {
            //     if ($object->parent_id != 0) {
            //         $role_array = DB::select("SELECT * FROM fc_get_parent_role ($object->organization_id)");
            //         foreach ($role_array as $value) {
            //             DB::table('role_site')->where([
            //                 'role_id' => $value->id
            //                 , 'site_id' => $object->id
            //                 , 'organization_id' => $object->organization_id
            //             ])->delete();
            //         }
            //     }
            // }
            DB::commit();
            $response = [];
            $response['status'] = 1;
            $response['deleteParent'] = $deleteParent;
            $response['insertParentRole'] = $insertParentRole;
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
    public function get_site_tablets(Request $request)
    {

        $request_user = $request->user();
        $user_id =  $request_user->id;
        $organization_id = $request->organization_id;
        $site_tablets = DB::select(" exec sp_fba_get_site_tablets $user_id, $organization_id");
        $site_tablet = array();
        foreach ($site_tablets as $item) {
            $site_tablet[] =  array('label' => $item->site_name, 'value' => strval($item->id));
        }
        return response()->json(['site_tablet' => $site_tablet]);
    }

    // Nghĩa thêm function lấy site theo quyền
    public function sp_get_site_with_permission(Request $request)
    {
        $request_user = $request->user();
        $user_id = $request_user->id;
        $organization_id = $request->organization_id;
        $deleted = $request->deleted;
        $response = [];
        $response['site_array'] = DB::select("exec sp_get_site_with_permission $organization_id, $user_id, $deleted");
        $response['site_in_role'] = DB::select("SELECT * FROM fc_get_site_in_role($organization_id, $user_id)");
        $response['category_array'] = DB::table('categories')->where('organization_id', $organization_id)->select('id AS value','category_name AS label')->get();
        return response()->json($response);
    }
}
