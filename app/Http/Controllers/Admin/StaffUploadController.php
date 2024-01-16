<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Validator;
use App\StaffInfo;

class StaffUploadController extends Controller
{
    public function sp_get_staff_in_site(Request $request)
    {
        $error_messages = [
            // Mã tổ chức phải là số
            'organization_id.required' => 'Mã tổ chức không được để trống',
            'organization_id.integer' => 'Mã tổ chức phải là số',
            'organization_id.min' => 'Mã tổ chức có giá trị nhỏ nhất là :min',
            //
            'site_id.required' => 'Mã miền không được để trống',
            'site_id.integer' => 'Mã miền phải là số',
            'site_id.min' => 'Mã miền có giá trị nhỏ nhất là :min',
            //
            'deleted.required' => 'Mã deleted không được để trống',
            'deleted.boolean' => 'Mã deleted chỉ bao gồm 0 hoặc 1'
        ];
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|min:1', 'site_id' => 'required|integer|min:0', 'deleted' => 'required|boolean'
        ], $error_messages);
        if ($validator->fails()) {
            $json_error = json_encode($validator->errors()->all());
            return response()->json(['message' => $json_error], 404);
        }
        $request_user = $request->user();
        $user_id = $request_user->id;
        $date = Carbon::now()->format('Y-m-d H:i:s');
        $org = $request->organization_id;
        $site_id = $request->site_id;
        $deleted = $request->deleted;
        $data = DB::select("exec sp_get_staff_in_site $org, $user_id ,$deleted, $site_id"); //
        return response()->json(['retrieveData' => $data, 'recordDate' => $date]);
    }

    public function staff_upload_insert_staff(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'organization_id.required' => 'Mã tổ chức không được để trống',
                'organization_id.integer' => 'Mã tổ chức phải là số',
                'organization_id.min' => 'Mã tổ chức có giá trị nhỏ nhất là :min',
                //
                'site_id.required' => 'Mã miền không được để trống',
                'site_id.integer' => 'Mã miền phải là số',
                'site_id.min' => 'Mã miền có giá trị nhỏ nhất là :min',
                //
                'staff_name.required' => 'Tên nhân viên không được để trống',
                'staff_name.min' => 'Tên nhân viên có ít nhất :min ký tự',
                'staff_name.max' => 'Tên nhân viên có ít nhất :min ký tự'
            ];
            $validArray = [
                'organization_id' => 'required|integer|min:1', 'site_id' => 'required|integer|min:0', 'staff_name' => 'required|min:3|max:100'
            ];
            if ($request->hasFile('staff_avatar')) {
                $validArray['staff_avatar'] = 'image|mimes:jpeg,png,jpg,gif|max:2048';
            }
            $validator = Validator::make($request->all(), $validArray, $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $uid = DB::select("SELECT NEWID() AS uid")[0]->uid;
            $request_user = $request->user();
            $date = Carbon::now()->format('Y-m-d H:i:s');
            $object = new StaffInfo;
            $object->id = $uid;
            $object->created_at = $date;
            $object->created_by = $request_user->id;
            $object->updated_by = $request_user->id;
            $object->updated_at = $date;
            $object->staff_name = $request->staff_name;
            $object->organization_id = $request->organization_id;
            $object->site_id = $request->site_id;
            $object->staff_avatar = 'default.jpg';
            if ($request->hasFile('staff_avatar')) {
                $file = $request->file('staff_avatar');
                $extension = $file->getClientOriginalName();
                $img_source_name = time() . '.' . $extension;
                $path = public_path() . '/images/staff_info';
                $upload = $file->move($path, $img_source_name);
                $object->staff_avatar = $img_source_name;
            }
            $object->save();
            DB::commit();
            $site_name = DB::table('sites')->where('id', $object->site_id)->get()[0]->site_name;
            $object->site_name = $site_name;
            $object->gid = DB::table('staffs_info')->where('id', $uid)->get()[0]->id;
            $response = [];
            $response['message'] = 1;
            $response['insertedData'] = $object;
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [];
            $response['message'] = $e->getMessage();
            $response['code'] = $e->getCode();
            $response['line'] = $e->getLine();
            DB::rollback();
            return response()->json($response);
        }
    }

    public function staff_upload_update_staff(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'id.required' => 'Mã tổ chức không được để trống',
                'id.gui_type' => 'Mã tổ chức phải là kiểu GUID',
                //
                'staff_name.required' => 'Tên nhân viên không được để trống',
                'staff_name.min' => 'Tên nhân viên có ít nhất :min ký tự',
                'staff_name.max' => 'Tên nhân viên có ít nhất :min ký tự'
            ];
            $validArray = [
                'id' => 'required|gui_type', 'staff_name' => 'required|min:3|max:100'
            ];
            if ($request->hasFile('staff_avatar')) {
                $validArray['staff_avatar'] = 'image|mimes:jpeg,png,jpg,gif|max:2048';
            }
            $validator = Validator::make($request->all(), $validArray, $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            $uid = $request->id;
            $date = Carbon::now()->format('Y-m-d H:i:s');
            $object = StaffInfo::where('id', $request->id)->first();
            $object->updated_by = $request_user->id;
            $object->updated_at = $date;
            $object->staff_name = $request->staff_name;
            if ($request->hasFile('staff_avatar')) {
                $file = $request->file('staff_avatar');
                $extension = $file->getClientOriginalName();
                $img_source_name = time() . '.' . $extension;
                $path = public_path() . '/images/staff_info';
                $upload = $file->move($path, $img_source_name);
                $object->staff_avatar = $img_source_name;
            }
            $object->save();
            DB::commit();
            $site_name = DB::table('sites')->where('id', $object->site_id)->get()[0]->site_name;
            $object->site_name = $site_name;
            $object->gid = DB::table('staffs_info')->where('id', $uid)->get()[0]->id;
            $response = [];
            $response['message'] = 1;
            $response['updatedData'] = $object;
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [];
            $response['message'] = $e->getMessage();
            $response['code'] = $e->getCode();
            $response['line'] = $e->getLine();
            DB::rollback();
            return response()->json($response);
        }
    }
    // Tool C#
    public function staff_user_get(Request $request)
    {
        $request_user = $request->user();
        $user_id = $request_user->id;
        $org = $request->organization_id;
        $site_id = $request->site_id;
        $deleted = $request->deleted;
        $record = $request->record ? $request->record : 30;
        $data_response = DB::table('staffs_info')->where([['organization_id', $org], ['site_id', $site_id], ['deleted', $deleted]])->orderBy('updated_at', 'DESC')->paginate($record);
        return response()->json(['data_response' => $data_response]);
    }

    public function get_info_with_phone_number(Request $request)
    {
        // $data = DB::table($this->default_table)->where([['phone', $request->phone_number], ['deleted', 0], ['organization_id', $request->organization_id]])->get();
        $data = DB::table('staffs_info')->where([['phone', $request->phone_number], ['site_id',  $request->site_id], ['organization_id', $request->organization_id]])->get();
        return response()->json(['data' => $data]);
    }

    public function staff_post_database(Request $request)
    {
        DB::beginTransaction();
        $response = 0;
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'organization_id.required' => 'Mã tổ chức không được để trống',
                'site_id.required' => 'Mã địa điểm không được để trống',
                //
                'staff_name.required' => 'Tên khách hàng không được để trống',
                //
                'phone.required' => 'Số điện thoại không được để trống',
                //
                'avatar.required' => 'Ảnh đại diện không được để trống',

                'id.required' => 'Id không được để trống',
                'id.unique' => 'Id đã tồn tại',
                // 'birthday.required' => 'Độ tuổi không được để trống',
                // //
                // 'gender.required' => 'Giới tính không được để trống',
                // 'gender.boolean' => 'Giới tính chỉ bao gồm 0 hoặc 1'
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validArray = [
                'organization_id' => 'required',
                'site_id' => 'required',
                'staff_name' => 'required',
                'phone' => 'required',
                // 'avatar' => 'required',
                'id' => 'required'
            ];

            $requestData = (array) $request->all();
            $validator = Validator::make($requestData, $validArray, $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }

            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $request_user = $request->user();
            $date = Carbon::now()->format('Y-m-d H:i:s');

            if ($request->crud == "INSERT") {
                $data  = StaffInfo::where([['phone', $request->phone], ['site_id', $request->site_id], ['organization_id', $request->organization_id]])->get();
                if (count($data) > 0) {
                    $response = 2;
                    return response()->json($response);
                }
            }

            $staff_info  = StaffInfo::find($request->id);
            if (!$staff_info) {
                $staff_info = new StaffInfo;
                $staff_info->id = $request->id;
            }
            $staff_info->organization_id = $request->organization_id;
            $staff_info->site_id = $request->site_id;
            $staff_info->created_by = $request_user->id;
            $staff_info->updated_by = $request_user->id;
            $staff_info->updated_at = $date;
            $staff_info->created_at = $date;
            $staff_info->deleted = 0;
            $staff_info->actived = 1;
            $staff_info->staff_name = $request->staff_name;
            $staff_info->phone = $request->phone;
            $staff_info->address = $request->address;
            if (isset($request->gender))
                $staff_info->gender = $request->gender;
            if (isset($request->status))
                $staff_info->status = $request->status;
            if (isset($request->ngay_sinh))
                $staff_info->ngay_sinh = $request->ngay_sinh;
            if (isset($request->avatar)) {
                $avatar = str_replace(" ", "+", $request->avatar);
                $file = base64_decode($avatar);
                if ($file) {
                    $extension = $request->phone;
                    $img_source_name = time() . '.' . $extension . '.jpg';
                    $path = public_path() . '/images/staff_info/';
                    $upload = file_put_contents($path . $img_source_name, $file);
                    $staff_info->staff_avatar = $img_source_name;
                }
            }

            $staff_info->save();

            $response = 1;
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            $response = 0;
        }
        return response()->json($response);
    }
}
