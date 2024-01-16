<?php

namespace App\Http\Controllers\Admin;

use App\Organization;
use App\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\User;
use Image;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\SmtpEmail;
use Error;
use ElephantIO\Client, ElephantIO\Engine\SocketIO\Version2X, ElephantIO\Exception\ServerConnectionFailureException;

require __DIR__ . '/../../../../vendor/autoload.php';

class OrganizationController extends Controller
{
    public function get_organization_for_login(Request $request)
    {
        $default = 'logo_acs_small.jpg';
        $object = Organization::where('subdomain_name', $request->name)->first();
        $logo = null;
        if (isset($object)) {
            $logo = $object->company_logo;
        }
        $image = $this->get_image($logo, $default);
        return response()->json(['data' => $image]);
    }
    // Hàm này nghĩa sửa ngày 5-5-2019
    private function get_image($image, $default = 'no_image.png')
    {
        $folder = public_path() . '/images/fba/';
        // return base64_encode(file_get_contents($folder.$app->company_logo));
        if ($image) {
            if (file_exists($folder . $image)) {
                return base64_encode(file_get_contents($folder . $image));
            } else {
                return base64_encode(file_get_contents($folder . $default));
            }
        } else {
            return base64_encode(file_get_contents($folder . $default));
        }
    }
    public function getData(Request $request)
    {
        try {
            $request_user = $request->user();
            $organization_id = (int) $request_user->organization_id;
            $lever = (int) $request_user->lever;
            $organization = new Organization();
            $columnArray = ["*"];
            $retrieveData = $organization->tryGetAllDataCrud($columnArray);
            //
            $isSuperAdmin = $this->isSuperAdmin($request_user);
            $isOrgAdmin = $this->isOrgAdmin($request_user);
            //
            foreach ($retrieveData as $value) {
                $image = $value->company_logo != null ? $value->company_logo : 'no_image.png';
                $value->company_logo = asset("images/fba/$image");
            }
            $indexArray = $isSuperAdmin ? DB::select("SELECT id AS item_id, index_name AS item_label FROM index_module") : [];

            $canUpdateData = $isSuperAdmin || $isOrgAdmin ? true : false;
            $response = [
                'status' => 1, 'isSuperAdmin' => $isSuperAdmin, 'retrieveData' => $retrieveData, 'canUpdateData' => $canUpdateData, 'indexArray' => $indexArray
            ];
            return response()->json($response);
        } catch (Exception $e) {
            $response = [
                'status' => 0, 'message' => $e->getMessage()
            ];
            return response()->json($response);
        }
    }
    public function insert(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = json_decode($request->data);
            $error_messages = [
                // Mã tổ chức phải là số
                'organization_name.required' => 'Tên tổ chức không được để trống',
                'organization_name.min' => 'Tên tổ chức có ít nhất :min kí tự',
                'organization_name.max' => 'Tên tổ có nhiều nhất :max kí tự',
                // Trường tên viết tắt
                'organization_shortname.required' => 'Tên viết tắt không được để trống',
                'organization_shortname.min' => 'Tên viết tắt phải có ít nhất :min kí tự',
                'organization_shortname.max' => 'Tên viết tắt có nhiều nhất :max kí tự',
                // Trường mã tổ chức
                'organization_code.required' => 'Mã công ty không được để trống',
                'organization_code.min' => 'Mã công ty phải có ít nhất :min kí tự',
                'organization_code.max' => 'Mã công ty có nhiều nhất :max kí tự',
                // Trường Email
                'email.required' => 'Email quản trị không được để trống',
                'email.min' => 'Email quản trị phải có ít nhất :min kí tự',
                'email.max' => 'Email quản trị có nhiều nhất :max kí tự',
                'email.email' => 'Email quản trị không hợp lệ',
                'email.unique' => 'Email đã tồn tại',
                // Trường mật khẩu
                'user_password.required' => 'Mật khẩu không được để trống',
                'user_password.min' => 'Mật khẩu phải có ít nhất :min kí tự',
                'user_password.max' => 'Mật khẩu có nhiều nhất :max kí tự',
                'subdomain_name.required' => 'Tên miền không được để trống',
                'subdomain_name.min' => 'Tên miền phải có ít nhất :min kí tự',
                'indexList.required' => 'Mục chỉ số không được để trống'
            ];
            // $newRequest = new Request([$data]);
            // |regex:/^[\pL\s\-]+$/u
            $validatorArray = [
                'organization_name' => 'required|min:3|max:300', 'organization_shortname' => 'required|min:3|max:300', 'organization_code' => 'required|min:3|max:300|unique:organizations', 'email' => 'required|email|min:10|max:300|unique:users', 'user_password' => 'required|min:5|max:300', 'subdomain_name' => 'required|min:3', 'indexList' => 'required'
            ];
            $validator = Validator::make((array) $data, $validatorArray, $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $date = $this->getDateNow();
            $request_user = $request->user();
            $org = new Organization;
            $org->created_by = $request_user->id;
            $org->created_at = $date;
            $org->updated_by = $request_user->id;
            $org->updated_at = $date;
            $org->organization_name = trim($data->organization_name);
            $org->organization_shortname = trim($data->organization_shortname);
            $org->organization_code = trim($this->stripUnicode($data->organization_code));
            $org->subdomain_name = trim($data->subdomain_name);
            $org->actived = $data->actived;
            $org->deleted = 0;
            $org->time_setting_12h =$data->time_setting_12h;
            $org->save_session =$data->save_session;
            if (isset($data->organization_description)) {
                $org->organization_description = trim($data->organization_description);
            }
            if ($request->hasFile('company_logo')) {
                $file = $request->file('company_logo');
                $path = public_path('/images/fba');
                $company_logo_name = $this->saveImageFile($file, $path);
                $org->company_logo = $company_logo_name;
            }
            $org->save();
            if (isset($org->company_logo)) {
                $org->company_logo = asset("/images/fba/$org->company_logo");
            }
            // thêm app setting. không dùng nhưng vẫn thêm vào.
            $fba_setting =  DB::table('fba_appication_settings')->where('organization_id', 0)->first();
            DB::table('fba_appication_settings')->insert([
                'organization_id' => $org->id,
                'created_at' => $date,
                'created_by' => $request_user->id,
                'updated_at' => $date,
                'updated_by' => $request_user->id,
                'actived' => 1, 'deleted' => 0,
                'company_logo' => $company_logo_name ? $company_logo_name : '',
                'reason_title' => $fba_setting->reason_title,
                'application_logo' => $fba_setting->application_logo,
                'finish_message' => $fba_setting->finish_message,
                'finish_message2' => $fba_setting->finish_message2,
                'reason_other_title' => $fba_setting->reason_other_title,
                'reason_other_highligt' => $fba_setting->reason_other_highligt,
                'btn_cancel' => $fba_setting->btn_cancel,
                'btn_send' => $fba_setting->btn_send,
                'customer_info_title' => $fba_setting->customer_info_title,
                'customer_info_name' => $fba_setting->customer_info_name,
                'customer_info_phone' => $fba_setting->customer_info_phone,
                'customer_info_email' => $fba_setting->customer_info_email,
                'customer_info_name_require' => $fba_setting->customer_info_name_require,
                'customer_info_phone_require' => $fba_setting->customer_info_phone_require,
                'customer_info_email_require' => $fba_setting->customer_info_email_require,
                'customer_info_any_require' => $fba_setting->customer_info_any_require,
                'actived_cancel' => $fba_setting->actived_cancel
            ]);

            // thêm thông báo tự động
            // $notification_arr = DB::table('fba_notifications')->where('organization_id', 0)->get();
            // if(count($notification_arr) > 0) {
            //     DB::table('fba_notifications')->insert([
            //         'organization_id' => $org->id
            //         , 'location_id' => 0
            //         , 'parameters' => $notification_arr[0]->parameters
            //         , 'created_at' => $date
            //         , 'created_by' => $request_user->id
            //         , 'updated_at' => $date
            //         , 'updated_by' => $request_user->id
            //         , 'actived' => 1
            //         , 'deleted' => 0
            //     ]);
            // }
            //  thêm tự động 3 roles mặc định chưa gán quyền
            $last_role = DB::select("SELECT TOP 1 role_order FROM roles ORDER BY role_order DESC");
            $role_order = $last_role[0]->role_order + 1;
            $role_array = DB::table('roles')->where('organization_id', 0)->get();
            // list of module
            $indexList = $data->indexList;
            foreach ($role_array as $key => $value) {
                $roles = new Role();
                $roles->organization_id = $org->id;
                $roles->role_name = $value->role_name;
                $roles->role_description = $value->role_description;
                $roles->actived = 1;
                $roles->deleted = 0;
                $roles->created_by = $request_user->id;
                $roles->updated_by = $request_user->id;
                $roles->created_at = $date;
                $roles->updated_at = $date;
                $roles->role_type = $key;
                $roles->role_order = $role_order;
                $roles->save();
                // sau khi thêm 3 role mặc định sẽ gán quyền trên page cho 3 role dó
                if ($roles->role_type == 0) {
                    // thêm các chỉ số
                    foreach ($indexList as $indexValue) {
                        DB::table('role_index')->insert([
                            'organization_id' => $roles->organization_id, 'role_id' => $roles->id, 'index_id' => $indexValue->item_id, 'created_at' => $date, 'expire_date' => Carbon::now()->addDays(30)
                        ]);
                    }
                    // thêm nguời dùng quản trị tổ chức dó
                    $user = new User();
                    $user->organization_id = $roles->organization_id;
                    $user->created_by = $request_user->id;
                    $user->updated_by = $request_user->id;
                    $user->created_at = $date;
                    $user->updated_at = $date;
                    $user->name = 'NEW ADMIN';
                    $user->email = trim($data->email);
                    $user->password = bcrypt($data->user_password);
                    $user->token_email = $this->randomMd5String();
                    $user->token_email_expired_time = Carbon::now()->addDay(1);
                    $user->actived = $data->actived;
                    $user->deleted = 0;
                    $user->save();
                    $subDomain = isset($data->subdomain_name) ? $data->subdomain_name : 'http://localhost:4200';
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
                    // gán người sử dụng vào roles
                    DB::table('role_user')->insert([
                        'role_id' => $roles->id, 'user_id' => $user->id, 'organization_id' => $roles->organization_id
                    ]);
                }
            }
            DB::commit();
            // thêm mới thành công thì gửi lại cho client hiển thị
            $response = [];
            $response['status'] = 1;
            $response['insertedData'] = $org;
            $response['newEmail'] = isset($newEmail) ? $newEmail : null;
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
    public function updateRole(Request $request)
    {
        DB::beginTransaction();
        try {
            $request_user = $request->user();
            $date = $this->getDateNow();
            $error_messages = [
                // Mã tổ chức phải là số
                'id.required' => 'Mã tổ chức không được để trống',
                'id.min' => 'Mã tổ chức có ít nhất :min kí tự',
                'id.integer' => 'Mã tổ không phù hợp',
                'indexList.required' => 'Chỉ số không được để trống'
            ];
            // $newRequest = new Request([$data]);
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|min:1', 'indexList' => 'required'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $id = $request->id;
            $org = Organization::findOrFail($id);
            $isSuperAdmin = $this->isSuperAdmin($request_user);
            if (!$isSuperAdmin) {
                $json_error = json_encode(['Bạn không có quyền thực hiện chức năng này']);
                throw new Exception($json_error, 770);
            }
            // Lấy lại role mặc định với role type = 0 của hệ thống
            $roleOrgArray = DB::select("SELECT TOP 1 fc.*, ri.expire_date FROM fc_get_parent_role ($org->id) fc INNER JOIN role_index ri ON fc.id = ri.role_id WHERE fc.role_type = 0");
            if (count($roleOrgArray) === 0) {
                $json_error = json_encode(['Tổ chức chưa có phân nhóm quyền mặc định']);
                throw new Exception($json_error, 770);
            }

            // duyệt từng role
            $defaultRole = $roleOrgArray[0];
            DB::table('role_index')->where('organization_id', $org->id)->where('role_id', $defaultRole->id)->delete();
            // duyệt mảng
            foreach ($request->indexList as $indexValue) {
                // bắt đầu thêm mới lại module
                DB::table('role_index')->insert([
                    'organization_id' => $org->id, 'role_id' => $defaultRole->id, 'index_id' => $indexValue['item_id'], 'created_at' => $date, 'expire_date' => $defaultRole->expire_date // giữ nguyên ngày
                ]);
            }
            DB::commit();
            // thêm mới thành công thì gửi lại cho client hiển thị
            $response = [];
            $response['status'] = 1;
            $response['updatedData'] = $org;
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
            $request_user = $request->user();
            $date = $this->getDateNow();
            $data = json_decode($request->data);
            $error_messages = [
                // Mã tổ chức phải là số
                'organization_name.required' => 'Tên tổ chức không được để trống',
                'organization_name.min' => 'Tên tổ chức có ít nhất :min kí tự',
                'organization_name.max' => 'Tên tổ có nhiều nhất :max kí tự',
                // Trường tên viết tắt
                'organization_shortname.required' => 'Tên viết tắt không được để trống',
                'organization_shortname.min' => 'Tên viết tắt phải có ít nhất :min kí tự',
                'organization_shortname.max' => 'Tên viết tắt có nhiều nhất :max kí tự',
                // Trường mã tổ chức
                'organization_code.required' => 'Mã công ty không được để trống',
                'organization_code.min' => 'Mã công ty phải có ít nhất :min kí tự',
                'organization_code.max' => 'Mã công ty có nhiều nhất :max kí tự',
                'organization_code.unique' => 'Mã công ty đã tồn tại',
                //
                'organization_description.min' => 'Tóm tắt có ít nhất :min kí tự',
                'organization_description.max' => 'Tóm tắt có nhiều nhất :min kí tự',
                //
                'actived.required' => 'Mã kích hoạt không được để trống',
                'actived.boolean' => 'Mã kích hoạt không phù hợp',
                'subdomain_name.required' => 'Tên miền không được để trống'
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validArray = [
                'organization_name' => 'required|min:3|max:300', 'organization_shortname' => 'required|min:3|max:300'
            ];
            if (isset($data->actived)) {
                $validArray['actived'] = 'required|boolean';
            }
            if (isset($data->subdomain_name)) {
                $validArray['subdomain_name'] = 'required|min:3|max:300';
            }
            if (isset($data->organization_code)) {
                $validArray['organization_code'] = 'required|min:3|max:300';
            }
            $validator = Validator::make((array) $data, $validArray, $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $id = $data->id;
            $org = Organization::findOrFail($id);
            $org->updated_by = $request_user->id;
            $org->updated_at = $date;
            $org->organization_shortname = trim($data->organization_shortname);
            $org->organization_name = trim($data->organization_name);
            $org->time_setting_12h =$data->time_setting_12h;
            $org->save_session =$data->save_session;
            if (isset($data->organization_description)) {
                $org->organization_description = trim($data->organization_description);
            }
            if (isset($data->organization_code)) {
                $org->organization_code = trim($this->stripUnicode($data->organization_code));
            }
            // Nếu tồn tại ảnh
            if ($request->hasFile('company_logo')) {
                $file = $request->file('company_logo');
                $path = public_path('/images/fba');
                $org->company_logo = $this->saveImageFile($file, $path);
            }
            $isSuperAdmin = $this->isSuperAdmin($request_user);
            if ($isSuperAdmin) {
                if (isset($data->subdomain_name)) {
                    $org->subdomain_name = $data->subdomain_name;
                }
                if (isset($data->actived)) {
                    $org->actived = $data->actived;
                    $procResult = DB::select("EXEC sp_update_all_from_organization_id $org->id, $org->actived");
                }
            }
            $org->save();
            if (isset($org->company_logo)) {
                $org->company_logo = asset("images/fba/$org->company_logo");
            }
            DB::commit();

            try {
                $token_type = 'Bearer';
                $url_socket = env('URL_SOCKET');
                $access_token = $request->token;
                $socketClient =  new Client(new Version2X($url_socket));
                $socketClient->initialize();
                $socketClient->emit('fba_tablet_reload_data', ['organization_id' => $org->id, 'tocken_type' => $token_type, 'access_token' => $access_token]);
                $socketClient->close();
                $response['socket'] = 'OK';
            } catch (ServerConnectionFailureException $e) {
                $response['socket'] = $e;
            }
            // thêm mới thành công thì gửi lại cho client hiển thị
            $response = [];
            $response['status'] = 1;
            $response['updatedData'] = $org;
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['socket'] = '';
            $response['message'] = $e->getMessage();
            $response['code'] = $e->getCode();
            $response['line'] = $e->getLine();
            DB::rollback();
            return response()->json($response);
        }
    }
    public function getRole(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|min:1'
            ]);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $orgId = $request->id;
            // Lấy thông tin tổ chức
            $orgInfo = Organization::findOrFail($orgId);
            $orgCurrentIndex = Organization::tryGetCurrentRole($orgId);
            // $orgCurrentIndex = DB::select("SELECT id AS item_id, index_name AS item_label FROM fc_get_page_module_v2($id) ORDER BY group_name ASC");
            $response = [
                'status' => 1, 'orgCurrentIndex' => $orgCurrentIndex
            ];
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [
                'status' => 0, 'message' => $e->getMessage(), 'line' => $e->getLine(), 'code' => $e->getCode()
            ];
            return response()->json($response);
        }
    }


    // public function delete(Request $request)
    // {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'id' => 'required|integer|min:1'
    //         ]);
    //         if ($validator->fails()) {
    //             $json_error = json_encode($validator->errors()->all());
    //             throw new Exception($json_error, 770);
    //         }
    //         $id = $request->id;
    //         $procResult = DB::select("EXEC sp_delete_all_from_organization_id $id");
    //         if ($procResult[0]->result == 0) {
    //             throw new Exception("Error Processing Procedure", 770);
    //         }
    //         $response = [
    //             'status' => 1
    //         ];
    //         return response()->json($response);
    //     } catch (\Exception $e) {
    //         $response = [
    //             'status' => 0
    //             , 'message' => $e->getMessage()
    //             , 'line' => $e->getLine()
    //             , 'code' => $e->getCode()
    //         ];
    //         return response()->json($response);
    //     }
    // }
}
