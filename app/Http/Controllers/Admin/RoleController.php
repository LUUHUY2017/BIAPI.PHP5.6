<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ErrorHandleController as Error;
use App\Role;
use App\RoleUser;
use DB;
use Exception;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class RoleController extends Controller
{

    public function update_user_single_role(Request $request) {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'organization_id.required' => 'Mã tổ chức không được để trống',
                'organization_id.numeric' => 'Mã tổ chức phải là số',
                'organization_id.min' => 'Mã tổ chức có giá trị nhỏ nhất là :min',
                // Mã quyền phải là số
                'role_id.required' => 'Mã quyền không được để trống',
                'role_id.numeric' => 'Mã quyền phải là số',
                'role_id.min' => 'Mã quyền có giá trị nhỏ nhất là :min',
                // Mã người dùng
                'user_id.required' => 'Mã người dùng không được để trống',
                'user_id.numeric' => 'Mã người dùng phải là số',
                'user_id.min' => 'Mã người dùng có giá trị nhỏ nhất là :min',
                // Mã người dùng
                'role_type.required' => 'Mã người dùng không được để trống',
                'role_type.numeric' => 'Mã người dùng phải là số',
                'role_type.between' => 'Mã người dùng có giá trị trong khoảng :min đến :max',
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'role_id' => 'required|numeric|min:1'
                , 'organization_id' => 'required|numeric|min:1'
                , 'user_id' => 'required|numeric|min:1'
                , 'role_type' => 'required|numeric|between:0,2'
            ], $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $roleArray = $request->role_array;
            if(count($roleArray) === 0) {
                $errorArray = ['role không được để trống'];
                $json_error = json_encode($errorArray);
                throw new Exception($json_error, 770);
            }
            // Lấy thông tin request
            $request_user = $request->user();
            $date = Carbon::now()->format('Y-m-d H:i:s');
            $role_id = $request->role_id;
            $role_type = $request->role_type;
            $organization_id = $request->organization_id;
            $user_id = $request->user_id;
            // Biến này dùng để lưu các site mà người dùng chọn có parent_id = 0
            $roleSiteParentArray = [];
            foreach ($roleArray as $value) {
                if($value['parent_id'] == 0) {
                    $roleSiteParentArray[] = $value;
                }
            }
            // End đoạn lấy site người dùng chọn có parent_id = 0
            // Truy vấn lấy tất cả các site thuộc tổ chức có parent_id = 0
            $siteParentArray = DB::table('sites')->where('parent_id', 0)->where('actived', 1)->where('deleted', 0)->where('organization_id', $organization_id)->get();
            // Biến này dùng để lấy ra 3 role mặc định của hệ thống
            $parentRole = DB::select("SELECT TOP 3 id, role_name, role_type, role_order FROM roles WHERE organization_id = $organization_id ORDER BY role_order ASC");
            /*
                Biến này dùng để xác định xem site mà người dùng lựa chọn có bằng tất cả các
                site thuộc tổ chức mà có parent_id = 0 hay không
            */
            $equalCountArray = count($roleSiteParentArray) === count($siteParentArray);
            $match = false;
            foreach ($parentRole as $value) {
                if($value->id == $role_id) {
                    $match = true;
                    break;
                }
            }
            // End đoạn xác định xem roles của người dùng có phải là role mặc định hay không
            // Nếu người dùng được sửa quyền quản trị đã thuộc nhóm quản trị, Role mặc định
            if($match) {
                /*
                    Trường hợp người dùng chọn tất cả các site thì chỉ thay đổi role của người dùng
                    dựa vào role_type. Tương đương số site parent_id = 0 mà người dùng chọn phải bằng số site parent_id = 0 mà tổ chức có.
                */
                // Đầu tiên xóa người dùng tại role cũ
                DB::table('role_user')->where('user_id', $user_id)->delete();
                if($equalCountArray) {
                    foreach ($parentRole as $value) {
                        if($value->role_type == $role_type ) {
                            // Thêm người dùng vào role có role_type = role_type mà người dùng chọn
                            // $parentRole đại diện cho 3 role mặc định của hệ thống
                          $role_u = DB::table('role_user')->insert([
                                'user_id' => $user_id
                                , 'role_id' => $value->id
                                , 'organization_id' => $organization_id
                            ]);
                            $roles = DB::table('roles')->where('id', $value->id)->update([
                                'updated_at' => $date
                                , 'updated_by' => $request_user->id
                            ]);
                            // Huy thêm quyền cho report các địa điểm cho user
                            foreach ($roleArray as $value) {
                                $insertRoleSiteArray[] = [
                                    'organization_id' => $organization_id
                                    , 'site_id' => $value['id']
                                    , 'role_id' => $role_id
                                ];
                            }
                            // thêm mới role site tại role_id mới
                            DB::table('role_site')->insert($insertRoleSiteArray);
                            break;
                        }
                    }
                } else {
                    // Tạo ra role mới dành cho họ
                    $lastIndex = DB::select("SELECT TOP 1 id, role_name, role_type, role_order FROM roles ORDER BY role_order DESC");
                    $object = new Role;
                    $object->organization_id = $organization_id;
                    $object->role_name = 'SINGLE ROLE';
                    $object->role_description = 'SINGLE ROLE';
                    $object->created_at = $date;
                    $object->updated_at = $date;
                    $object->created_by = $request_user->id;
                    $object->updated_by = $request_user->id;
                    $object->actived = 1;
                    $object->deleted = 0;
                    $object->role_type = $role_type;
                    $object->role_order = $lastIndex[0]->role_order + 1;
                    $object->save();
                    $insertRoleSiteArray = [];
                    // Duyệt tất cả các site mà người dùng chọn
                    foreach ($roleArray as $value) {
                        $insertRoleSiteArray[] = [
                            'organization_id' => $organization_id
                            , 'site_id' => $value['id']
                            , 'role_id' => $object->id
                        ];
                    }
                    // thêm mới role site tại role_id mới
                    DB::table('role_site')->insert($insertRoleSiteArray);
                    // Thêm mới role_user tại role_id mới
                    DB::table('role_user')->insert([
                        'organization_id' => $organization_id
                        , 'user_id' => $user_id
                        , 'role_id' => $object->id
                    ]);
                }
                /* Trường hợp người dùng không chọn tất cả các site thì tách khỏi role
                quản trị sau đó tạo role mới cho riêng người dùng đó. Tương đương số site parent_id = 0 mà người dùng chọn < số site parent_id = 0 mà tổ chức có.
                */
            } else {
                /*
                    Trường hợp người dùng chọn full site thì sẽ xóa role của họ, và gắn họ vào role mặc định của hệ thống. Doạn này phải đảm bảo không xóa role mặc định của tổ chức đó
                */
                // Lấy ra role_id tại role_user mà họ thuộc vào
                $object = DB::table('role_user')->where('user_id', $user_id)->get()[0];
                if($equalCountArray) {
                    // Đầu tiên xóa người dùng tại role cũ
                    DB::table('role_user')->where('user_id', $user_id)->delete();
                    DB::table('role_site')->where('role_id', $object->role_id)->delete();
                    DB::table('roles')->where('id', $object->role_id)->delete();
                    //
                    foreach ($parentRole as $value) {
                        if($value->role_type == $role_type) {
                            // Thêm người dùng vào role có role_type = role_type mà người dùng chọn
                            // $parentRole đại diện cho 3 role mặc định của hệ thống
                            DB::table('role_user')->insert([
                                'user_id' => $user_id
                                , 'role_id' => $value->id
                                , 'organization_id' => $organization_id
                            ]);
                            DB::table('roles')->where('id', $value->id)->update([
                                'updated_at' => $date
                                , 'updated_by' => $request_user->id
                            ]);
                            break;
                        }
                    }
                } else {
                    /*
                        Trường hợp người dùng không chọn full site parent_id = 0 thì sẽ chỉ sửa role_site tại role lẻ của họ.
                    */
                    // Xóa tất cả các role site tại role lẻ của họ
                    DB::table('roles')->where('id', $object->role_id)->update([
                        'role_type' => $role_type
                        , 'updated_at' => $date
                        , 'updated_by' => $request_user->id
                    ]);
                    DB::table('role_site')->where('role_id', $object->role_id)->delete();
                    // Thêm lại role_site
                    foreach ($roleArray as $value) {
                        $insertRoleSiteArray[] = [
                            'organization_id' => $organization_id
                            , 'site_id' => $value['id']
                            , 'role_id' => $object->role_id
                        ];
                    }
                    // thêm mới role site tại role_id mới
                    DB::table('role_site')->insert($insertRoleSiteArray);
                }
            }
            DB::commit();
            $response = [];
            $response['roleSiteParentArray'] = $roleSiteParentArray;
            $response['match'] = $match;
            $response['status'] = 1;
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            $response['code'] = $e->getLine();
            DB::rollback();
            return response()->json($response);
        }
    }
}
