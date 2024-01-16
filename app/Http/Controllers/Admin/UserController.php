<?php

namespace App\Http\Controllers\Admin;

use App\User as DefaultModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Mail;
use DateTime;
use Config;
use Auth;
use App\Http\Controllers\ErrorHandleController as Error;
use Exception;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\SmtpEmail;
use App\RoleUser;
use App\Role;
use App\EmailConfig;

class UserController extends Controller
{
    // function xử lý quên mật khẩu thì gửi mật khẩu mới
    public function forgotPassword(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                'email.required' => 'Email xác thực không được để trống',
                'email.email' => 'Email xác thực không phù hợp'
            ];
            $validArray = [
                'email' => 'required|email'
            ];
            $validator = Validator::make($request->all(), $validArray, $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $object = DefaultModel::where('email', $request->email)->where('deleted', 0)->first();
            if ($object == null) {
                throw new Exception(json_encode(['The account does not exist']), 770);
            }
            if ($object->actived == 0) {
                if ($object->token_email != null || $object->token_email_expired_time != null) {
                    throw new Exception(json_encode(['The account has not been activated']), 770);
                }
                throw new Exception(json_encode(['The account is deactivated']), 770);
            }
            $newPassword = str_random(10);
            $password = $this->getRandomStringHashed($newPassword);
            $object->password = $password;
            $object->save();
            // Gửi email
            $newEmail = new SmtpEmail;
            $newEmail->organization_id = $object->organization_id;
            $newEmail->create_time = $this->getDateNow();
            $newEmail->email_sender_id = 1;
            $newEmail->to_emails = $request->email;
            $newEmail->subject = "Gửi mật khẩu mới cho tài khoản $object->email tại website bi.acs.vn";
            $newEmail->body = "Chúng tôi xin gửi bạn thông tin đăng nhập mới:\n Tài khoản: $object->email\n Mật khẩu: $newPassword";
            $newEmail->sent = 0;
            $newEmail->save();
            // end
            DB::commit();
            $response = [
                'status' => 1
            ];
            return response()->json($response);
        } catch (Exception $e) {
            DB::rollback();
            $response = [
                'status' => 0, 'message' => $e->getMessage(), 'code' => $e->getCode()
            ];
            return response()->json($response);
        }
    }
    // DONE. Kích hoạt người dùng
    public function userActiveEmail(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'token.required' => 'Token không được để trống',
                // Mã người dùng
                'email.required' => 'Email không được để trống',
                'email.email' => 'Email không phù hợp'
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'token' => 'required', 'email' => 'required|email'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }

            $user = DefaultModel::where('email', $request->email)->where('deleted', 0)->first();
            if ($user == null) {
                throw new Exception(json_encode(['Đường link không tồn tại']), 770);
            }
            $user->activeUserFromTokenEmail();
            DB::commit();
            $response = [
                'status' => 1
            ];
            return response()->json($response);
        } catch (Exception $e) {
            DB::rollback();
            $response = [
                'status' => 0, 'message' => $e->getMessage(), 'code' => $e->getCode()
            ];
            return response()->json($response);
        }
    }
    //
    public function userGetSite(Request $request)
    {
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'organization_id.required' => 'Mã tổ chức không được để trống',
                'organization_id.integer' => 'Mã tổ chức phải là số',
                'organization_id.min' => 'Mã tổ chức có giá trị nhỏ nhất là :min',
                // Mã người dùng
                'user_id.required' => 'Mã người dùng không được để trống',
                'user_id.integer' => 'Mã người dùng phải là số',
                'user_id.min' => 'Mã người dùng có giá trị nhỏ nhất là :min',
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|min:1', 'user_id' => 'required|integer|min:1'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            $user_id = $request->user_id;
            $organization_id = $request->organization_id;
            $response = [];
            $response['roleArray'] = DB::select("SELECT r.id, r.organization_id, ru.user_id, r.role_type, u.name, r.updated_at, r.role_name, r.role_description FROM roles r INNER JOIN role_user ru ON r.id = ru.role_id INNER JOIN users u ON r.updated_by = u.id WHERE ru.user_id = $user_id");
            $response['siteArray'] = DB::select("SELECT * FROM fc_get_site_in_role($organization_id, $user_id)");
            $response['status'] = 1;
            return response()->json($response);
        } catch (Exception $e) {
            $response = [
                'status' => 0, 'message' => $e->getMessage(), 'code' => $e->getCode()
            ];
            return response()->json($response);
        }
    }
    // Logout khoi he thong
    public function logout(Request $request)
    {
        if (Auth::check()) {
            Auth::user()->token()->revoke();
            return response()->json(['success' => 'logout_success'], 200);
        } else {
            return response()->json(['error' => 'api.something_went_wrong'], 500);
        }
    }
    // Sau khi đăng nhập thì lấy thông tin đăng nhập để lưu vào trong Localstorage
    // Luư thêm thời gian đăng nhập
    public function getInfoFromLoginWeb(Request $request)
    {
        try {
            $request_user = $request->user();
            // Nếu như tài khoản đang bị đánh dấu xóa
            if ($request_user->deleted == 1) {
                throw new Exception("Tài khoản không tồn tại", 770);
            }
            // Nếu như vẫn còn tồn tại token_email
            if ($request_user->token_email != null || $request_user->token_email_expired_time != null) {
                throw new Exception("Tài khoản chưa được xác thực", 770);
            }
            if ($request_user->actived == 0) {
                throw new Exception("Tài khoản đang ngừng hoạt động", 770);
            }
            $userId = $request_user->id;
            $response = [];
            $date = $this->getDateNow();
            $userInfo = DB::table('organizations')->rightjoin('users', 'organizations.id', '=', 'users.organization_id')->select(
                'users.id',
                'users.created_at',
                'users.organization_id',
                'users.name',
                'users.email',
                'users.lever',
                'organizations.organization_name'
            )->where('users.id', $userId)->first();
            if ($userInfo !== null) {
                DB::table('users')->where('id', $userId)->update([
                    'last_time_login' => $date
                ]);
            }
            $response['userInfo'] = $userInfo;
            // Lấy thông tin module mà người dùng thuộc vào
            $org_id = $request_user->organization_id;
            $id = $request_user->id;
            $page_module_array = DB::select("SELECT * FROM fc_get_module_for_user_v2($org_id, $id)");
            if (count($page_module_array) === 0) {
                throw new Exception("Tài khoản chưa được phân quyền sử dụng trong hệ thống", 770);
            }
            $response['page_module_array'] = $page_module_array;
            // lay cac menu ma to chuc khong su dung
            $response['invisible_menu_item'] = DB::table('invisible_menu_item')->where('organization_id', $org_id)->select('name', 'url')->get();
            return response()->json($response);
        } catch (Exception $e) {
            $response = [
                'status' => 0, 'message' => $e->getMessage(), 'code' => $e->getCode()
            ];
            return response()->json($response);
        }
    }
    // DONE. đổi mật khẩu
    public function changePassword(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Trường mật khẩu
                'oldpassword.required' => 'Mật khẩu không được để trống',
                'oldpassword.min' => 'Mật khẩu phải có ít nhất :min kí tự',
                'oldpassword.max' => 'Mật khẩu có nhiều nhất :max kí tự',
                // Trường mật khẩu
                'newpassword.required' => 'Mật khẩu mới không được để trống',
                'newpassword.min' => 'Mật khẩu mới phải có ít nhất :min kí tự',
                'newpassword.max' => 'Mật khẩu mới có nhiều nhất :max kí tự',
                // Trường mật khẩu
                'confirmpassword.required' => 'Nhập lại mật khẩu không được để trống',
                // Trường confirm password
                'confirmpassword.same' => 'Mật khẩu mới phải trùng khớp'
            ];
            $validArray = [
                'oldpassword' => 'required|min:5|max:100', 'newpassword' => 'required|min:5|max:100', 'confirmpassword' => 'required|same:newpassword'
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), $validArray, $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            $id = $request_user->id;
            $user = DefaultModel::findOrFail($id);
            if (!Hash::check($request->oldpassword, $user->password)) {
                $json_error = json_encode(['Mật khẩu cũ không trùng khớp']);
                throw new Exception($json_error, 770);
            }
            $user->password = bcrypt($request->newpassword);
            $user->updated_by = $request_user->id;
            $user->updated_at = $this->getDateNow();
            $user->save();
            DB::commit();
            $response = [
                'status' => 1
            ];
            return response()->json($response);
        } catch (Exception $e) {
            DB::rollback();
            $response = [
                'status' => 0, 'message' => $e->getMessage(), 'code' => $e->getCode()
            ];
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
                'organization_id.numeric' => 'Mã tổ chức phải là số',
                // Trường họ tên
                'name.required' => 'Họ tên không được để trống',
                'name.min' => 'Họ tên phải có ít nhất :min kí tự',
                'name.max' => 'Họ tên có nhiều nhất :max kí tự',
                // Trường Email
                'email.required' => 'Email không được để trống',
                'email.min' => 'Email phải có ít nhất :min kí tự',
                'email.max' => 'Email có nhiều nhất :max kí tự',
                'email' => 'Email không hợp lệ',
                'email.unique' => 'Email đã tồn tại',
                // Trường mật khẩu
                'password.required' => 'Mật khẩu không được để trống',
                'password.min' => 'Mật khẩu phải có ít nhất :min kí tự',
                'password.max' => 'Mật khẩu có nhiều nhất :max kí tự',
                'password.alpha_dash' => 'Trường mật khẩu chỉ bao gồm các kí tự số, chữ cái từ A đến Z và gạch dưới',
                // Trường confirm password
                'same' => 'Mật khẩu phải trùng khớp',
                'without_space' => 'Mật khẩu không được chứa dấu cách'
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|numeric|min:0', 'name' => 'required|min:5|max:100' // bao gồm cả dấu cách
                , 'email' => 'required|email|min:7|max:100|unique:users', 'password' => 'required|min:5|max:100|without_space', 'confirmPassword' => 'same:password'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            // Lấy thông tin người tạo tài khoản
            $request_user = $request->user();
            $date = $this->getDateNow();
            // Tạo user mới
            $user = new DefaultModel;
            $user->created_by = $request_user->id;
            $user->created_at = $date;
            $user->updated_at = $date;
            $user->organization_id = $request->organization_id;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->actived = $request->actived;
            $user->deleted = 0;
            $user->token_email = $this->randomMd5String();
            $user->token_email_expired_time = Carbon::now()->addDay(1);
            $user->save();
            $subDomain = isset($request->subdomain) ? "$request->subdomain" : 'localhost:4200';
            $emailUrl = $user->generateTokenEmailUrl($user->email, $user->token_email, $subDomain);
            // Gửi email
            $newEmail = new SmtpEmail;
            $newEmail->organization_id = $user->organization_id;
            $newEmail->create_time = $date;
            $newEmail->email_sender_id = 1;
            $newEmail->to_emails = $user->email;
            $newEmail->subject = "Xác nhận email cho tài khoản $user->email tại website bi.acs.vn";
            $newEmail->body = "Để hoàn tất quá trình đăng ký mời bạn click vào <a href='$emailUrl'>link này</a> để kích hoạt tài khoản";
            $newEmail->sent = 0;
            $newEmail->save();
            // Biến này dùng để lấy ra 3 role mặc định của hệ thống
            $parentRole = DB::select("SELECT TOP 3 id, role_name, role_type, role_order FROM roles WHERE organization_id = $user->organization_id ORDER BY role_order ASC");
            $lastIndex = DB::select("SELECT TOP 1 id, role_name, role_type, role_order FROM roles ORDER BY role_order DESC");
            $created_by = $request_user->id;
            // Nếu là super admin thì thêm vào role mặc định của tổ chức với quyền là xem báo cáo
            if ($request_user->lever == 0 && $request_user->organization_id == 0) {
                $reportRole = null;
                foreach ($parentRole as $value) {
                    if ($value->role_type == 2) {
                        $reportRole = $value;
                        break;
                    }
                }
                if ($reportRole == null) {
                    throw new Exception(json_encode(['Tổ chức phân quyền đã gặp sự cố']), 770);
                }
                DB::table('role_user')->insert([
                    'role_id' => $reportRole->id, 'user_id' => $user->id, 'organization_id' => $user->organization_id
                ]);
                // Nếu không phải là super admin
            } else if ($request_user->lever != 0 && $request_user->organization_id != 0) {
                $current_user_role = DB::select("exec sp_get_role_of_user $created_by");
                $match = false;
                foreach ($parentRole as $value) {
                    if ($value->id == $current_user_role[0]->id) {
                        $match = true;
                        break;
                    }
                }

                // Nếu là role quyền mặc định thì thêm người đó vào role mặc định
                if ($match) {
                    DB::table('role_user')->insert([
                        'role_id' => $current_user_role[0]->id, 'user_id' => $user->id, 'organization_id' => $user->organization_id
                    ]);
                } else { // nếu không phải thì tạo role mới tương tự như role của người tạo
                    $current_role_site = DB::select("SELECT rs.role_id, rs.site_id FROM role_site rs INNER JOIN roles r ON rs.role_id = r.id INNER JOIN role_user ru ON r.id = ru.role_id WHERE ru.user_id = $created_by");
                    $object = new Role;
                    $object->organization_id = $user->organization_id;
                    $object->role_name = 'SINGLE ROLE';
                    $object->role_description = 'SINGLE ROLE';
                    $object->created_at = $date;
                    $object->updated_at = $date;
                    $object->created_by = $request_user->id;
                    $object->updated_by = $request_user->id;
                    $object->actived = 1;
                    $object->deleted = 0;
                    $object->role_type = $current_user_role[0]->role_type;
                    $object->role_order = $lastIndex[0]->role_order + 1;
                    $object->save();
                    DB::table('role_user')->insert([
                        'role_id' => $object->id, 'user_id' => $user->id, 'organization_id' => $user->organization_id
                    ]);
                    $insertRoleSite = [];
                    foreach ($current_role_site as $value) {
                        $insertRoleSite[] = [
                            'organization_id' => $user->organization_id, 'site_id' => $value->site_id, 'role_id' => $object->id
                        ];
                    }
                    DB::table('role_site')->insert($insertRoleSite);
                }
            }
            // End tạo user mới
            DB::commit();
            // thêm mới thành công thì gửi lại cho client hiển thị
            $response = [];
            $response['status'] = 1;
            $response['insertedData'] = $user;
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
                // Trường họ tên
                'name.required' => 'Họ tên không được để trống',
                'name.min' => 'Họ tên phải có ít nhất :min kí tự',
                'name.max' => 'Họ tên có nhiều nhất :max kí tự',
                // Trường Email
                'email.required' => 'Email không được để trống',
                'email.min' => 'Email phải có ít nhất :min kí tự',
                'email.max' => 'Email có nhiều nhất :max kí tự',
                'email' => 'Email không hợp lệ',
                'email.unique' => 'Email đã tồn tại',
                // Trường confirm password
                'same' => 'Mật khẩu phải trùng khớp',
                'without_space' => 'Mật khẩu không được chứa dấu cách',
                'id.required' => 'Mã người dùng không được trống',
                'id.numeric' => 'Mã người dùng phải là số',
                'id.min' => 'Mã người dùng là số nhỏ nhất là :min'
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validArray = [
                'id' => 'required|numeric|min:2', 'name' => 'required|min:5|max:100', 'actived' => 'required|boolean'
            ];
            if (isset($request->email)) {
                $validArray['email'] = 'required|email|min:7|max:100|unique:users';
            }
            $validator = Validator::make($request->all(), $validArray, $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $date = $this->getDateNow();
            $request_user = $request->user();
            $id = $request->id;
            $user = DefaultModel::findOrFail($id);
            // $user->organization_id = $request->organization_id;
            $user->name = $request->name;
            if (isset($request->email)) {
                $user->email = $request->email;
            }
            $user->updated_by = $request_user->id;
            $user->updated_at = $date;
            $user->actived = $request->actived;
            $user->save();
            DB::commit();
            $response = [];
            $response['status'] = 1;
            $response['updatedData'] = $user;
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

    public function softDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            $message = [
                'min' => ':attribute phải có ít nhất :min kí tự',
                'max'    => ':attribute phải có ít nhất :max kí tự',
                'between' => 'The :attribute must be between :min - :max.',
                'integer' => 'The :attribute phải là số',
            ];
            $validator = Validator::make($request->all(), [
                'deleted' => 'required|boolean', 'id' => 'required|integer|min:1'
            ], $message);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $id = $request->id;
            $object = DefaultModel::findOrFail($id);
            // Nếu là rollback lại
            if ($request->deleted == 1) {
                $object->deleted = 0;
            } else if ($request->deleted == 0) { // Nếu là đánh dấu xóa
                $object->deleted = 1;
            }
            $object->save();
            DB::commit();
            $response = [
                'status' => 1
            ];
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [
                'status' => 0, 'message' => $e->getMessage()
            ];
            DB::rollback();
            return response()->json($response);
        }
    }
    // x
    public function delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã vị trí phải là số
                'id.required' => 'Mã người dùng không được để trống',
                'id.integer' => 'Mã người dùng phải là số',
                'id.min' => 'Mã người dùng có giá trị nhỏ nhất là :min'
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|min:1'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $userId = $request->id;
            DefaultModel::where('id', $userId)->delete();
            $currentRoleInfo = Role::tryGetCurrentUserRole($userId);
            $parentRole = [];
            // xác định cả trường hợp 1 người thuộc nhiều role
            if (count($currentRoleInfo) > 0) {
                foreach ($currentRoleInfo as $value) {
                    $parentRole = Role::tryCheckInParentRole($value->id, $value->organization_id);
                    // Nếu không phải là role mặc định
                    if (count($parentRole) === 0) {
                        Role::where('id', $value->id)->delete();
                    }
                }
            }
            RoleUser::where('user_id', $userId)->delete();
            DB::commit();
            $response = [
                'status' => 1, 'parentRole' => $parentRole
            ];
            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollback();
            $response = [
                'status' => 1, 'message' => $e->getMessage(), 'code' => $e->getCode()
            ];
            return response()->json($response);
        }
    }
    // Nghĩa thêm function get insert
    public function getCurrentRoleUser(Request $request)
    {
        try {
            $validArray = [
                'user_compare_id' => 'required|integer|min:1', 'organization_id' => 'required|integer|min:1'
            ];
            $validator = Validator::make($request->all(), $validArray);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            $orgId = $request->organization_id;
            $userCompareId = $request->user_compare_id;
            $currentRoleInfo = Role::tryGetCurrentUserRole($userCompareId);
            if (count($currentRoleInfo) === 0) {
                throw new Exception('Người dùng không được phân quyền', 770);
            }
            $siteArray = DefaultModel::userGetRoleSiteForUpdateRole($orgId, $userCompareId);
            // $role_array = DB::table('roles')->where('organization_id', $organization_id)->select('id AS value', 'role_name AS label')->get();
            $response = [
                'status' => 1, 'siteArray' => $siteArray, 'currentRoleInfo' => $currentRoleInfo
            ];
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [
                'status' => 0, 'code' => $e->getCode(), 'message' => $e->getMessage(), 'line' => $e->getLine()
            ];
            return response()->json($response);
        }
    }
    // Gửi mật khẩu mới cho user phía người dùng. Done
    public function sendNewPassword(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã vị trí phải là số
                'id.required' => 'Mã người dùng không được để trống',
                'id.integer' => 'Mã người dùng phải là số',
                'id.min' => 'Mã người dùng có giá trị nhỏ nhất là :min',
                'new_email.required' => 'Email không được để trống',
                'new_email.email' => 'Email không hợp lệ'
            ];
            $validArray = [
                'id' => 'required|integer|min:1'
            ];
            if (isset($request->new_email)) {
                $validArray['new_email'] = 'required|email';
            }
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), $validArray, $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            $date = $this->getDateNow();
            $newPassword = str_random(10);
            $hashedPassword = $this->getRandomStringHashed($newPassword);
            $userId = $request->id;
            $object = DefaultModel::findOrFail($userId);
            $object->password = $hashedPassword;
            $object->updated_at = $date;
            $object->updated_by = $request_user->id;
            $object->save();
            // Kiểm tra cấu hình gửi email của hệ thống
            $currentEmailConfig = EmailConfig::tryGetConfigByOrgId($object->organization_id);
            if ($currentEmailConfig === null) {
                $json_error = json_encode(['Email config error']);
                throw new Exception($json_error, 770);
            }
            //
            $emailArray = [];
            $emailArray[0] = $object->email;
            if (isset($request->new_email)) {
                $emailArray[] = $request->new_email;
            }
            foreach ($emailArray as $value) {
                // Gửi email
                $newEmail = new SmtpEmail;
                $newEmail->organization_id = $object->organization_id;
                $newEmail->create_time = $date;
                $newEmail->email_sender_id = $currentEmailConfig->id;
                $newEmail->to_emails = $value;
                $newEmail->subject = "Gửi mật khẩu mới cho tài khoản $object->email tại website bi.acs.vn";
                $newEmail->body = "Hệ thống của chúng tôi xin gửi bạn mật khẩu mới: $newPassword";
                $newEmail->sent = 0;
                $newEmail->save();
            }
            DB::commit();
            $response = [
                'status' => 1
            ];
            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollback();
            $response = [
                'status' => 0, 'message' => $e->getMessage(), 'code' => $e->getCode(), 'line' => $e->getLine()
            ];
            return response()->json($response);
        }
    }
    // end

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
                'organization_id' => 'required|integer|min:1', 'deleted' => 'required|boolean'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            $organization_id = $request->organization_id;
            $deleted = $request->deleted;
            $retrieveData = DefaultModel::where('organization_id', $organization_id)->where('deleted', $deleted)->get();
            $response = [
                'status' => 1, 'retrieveData' => $retrieveData, 'userInfo' => $request_user
            ];
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
