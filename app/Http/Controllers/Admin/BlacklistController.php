<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Validator;
use App\Blacklist;

class BlacklistController extends Controller
{

    public function sp_get_black_list_organization(Request $request)
    {
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'organization_id.required' => 'Mã tổ chức không được để trống',
                'organization_id.integer' => 'Mã tổ chức phải là số',
                'organization_id.min' => 'Mã tổ chức có giá trị nhỏ nhất là :min',
            ];
            $validArray = [
                'organization_id' => 'required|integer|min:1', 'deleted' => 'required|boolean'
            ];
            $validator = Validator::make($request->all(), $validArray, $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $date = Carbon::now()->format('Y-m-d H:i:s');
            $request_user = $request->user();
            $user_id = $request_user->id;
            $org = $request->organization_id;
            $deleted = $request->deleted;
            $retrieveData = DB::select("exec sp_get_black_list_organization $org, $user_id, $deleted");
            return response()->json(['retrieveData' => $retrieveData, 'recordDate' => $date]);
        } catch (\Exception $e) {
            $response = [];
            $response['message'] = $e->getMessage();
            $response['code'] = $e->getCode();
            $response['line'] = $e->getLine();
            DB::rollback();
            return response()->json($response, 404);
        }
    }

    public function insert(Request $request)
    {
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'organization_id.required' => 'Mã tổ chức không được để trống',
                'organization_id.integer' => 'Mã tổ chức phải là số',
                'organization_id.min' => 'Mã tổ chức có giá trị nhỏ nhất là :min',
                //
                'blacklist_name.required' => 'Tên khách hàng không được để trống',
                'blacklist_name.min' => 'Tên khách hàng có ít nhất :min ký tự',
                'blacklist_name.max' => 'Tên khách hàng có nhiều nhất :max ký tự',
                //
                'avatar.image' => 'Ảnh đại diện phải là kiểu ảnh jpeg, png hoặc gif',
                'avatar.max' => 'Kích thước ảnh đại diện không vượt quá :max kb',
            ];
            $validArray = [
                'organization_id' => 'required|integer|min:1', 'blacklist_name' => 'required|min:3|max:100'
            ];
            if ($request->hasFile('avatar')) {
                $validArray['avatar'] = 'image|mimes:jpeg,png,jpg,gif|max:2048';
            }
            $validator = Validator::make($request->all(), $validArray, $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $uid = DB::select("SELECT NEWID() AS uid")[0]->uid;
            $request_user = $request->user();
            $date = Carbon::now()->format('Y-m-d');
            $object = new Blacklist;
            $object->id = $uid;
            $object->created_at = $date;
            $object->created_by = $request_user->id;
            $object->updated_at = $date;
            $object->updated_by = $request_user->id;
            $object->deleted = 0;
            $object->actived = "1";
            $object->blacklist_name = $request->blacklist_name;
            $object->organization_id = $request->organization_id;
            $object->avatar = 'default.jpg';
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $extension = $file->getClientOriginalName();
                $img_source_name = time() . '.' . $extension;
                $path = public_path() . '/images/blacklist';
                $upload = $file->move($path, $img_source_name);
                $object->avatar = $img_source_name;
            }
            $object->save();
            DB::commit();
            $object->gid = DB::table('black_list')->where('id', $uid)->get()[0]->id;
            $object->username_updated = DB::table('users')->where('id', $object->updated_by)->get()[0]->name;
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

    public function update(Request $request)
    {
        try {
            $error_messages = [
                //
                'id.required' => 'Mã blacklist không được để trống',
                'id.gui_type' => 'Mã blacklist không hợp lệ',
                //
                'blacklist_name.required' => 'Tên khách hàng không được để trống',
                'blacklist_name.min' => 'Tên khách hàng có ít nhất :min ký tự',
                'blacklist_name.max' => 'Tên khách hàng có nhiều nhất :max ký tự',
                //
                'avatar.image' => 'Ảnh đại diện phải là kiểu ảnh jpeg, png hoặc gif',
                'avatar.max' => 'Kích thước ảnh đại diện không vượt quá :max kb',
                //
                'actived.required' => 'Mã kích hoạt không được để trống',
                'actived.boolean' => 'Mã kích hoạt không phù hợp',
            ];
            $validArray = [
                'id' => 'required|gui_type', 'blacklist_name' => 'required|min:3|max:100', 'actived' => 'required|boolean'
            ];
            if ($request->hasFile('avatar')) {
                $validArray['avatar'] = 'image|mimes:jpeg,png,jpg,gif|max:2048';
            }
            $validator = Validator::make($request->all(), $validArray, $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            $uid = $request->id;
            $date = Carbon::now()->format('Y-m-d');
            $object = Blacklist::where('id', $request->id)->first();
            $object->updated_at = $date;
            $object->updated_by = $request_user->id;
            $object->actived = $request->actived;
            $object->blacklist_name = $request->blacklist_name;
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $extension = $file->getClientOriginalName();
                $img_source_name = time() . '.' . $extension;
                $path = public_path() . '/images/blacklist';
                $upload = $file->move($path, $img_source_name);
                $object->avatar = $img_source_name;
            }
            $object->save();
            DB::commit();
            $object->gid = DB::table('black_list')->where('id', $uid)->get()[0]->id;
            $object->username_updated = DB::table('users')->where('id', $object->updated_by)->get()[0]->name;
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
    public function black_list_user_get(Request $request)
    {
        $request_user = $request->user();
        $user_id = $request_user->id;
        $org = $request->organization_id;
        $site_id = $request->site_id;
        $deleted = $request->deleted;
        $record = $request->record ? $request->record : 30;
        $data_response = DB::table('black_list')->where([['organization_id', $org], ['deleted', $deleted]])->orderBy('updated_at', 'DESC')->paginate($record);
        return response()->json(['data_response' => $data_response]);
    }

    public function get_info_with_phone_number(Request $request)
    {
        // $data = DB::table($this->default_table)->where([['phone', $request->phone_number], ['deleted', 0], ['organization_id', $request->organization_id]])->get();
        $data = DB::table('black_list')->where([['phone', $request->phone_number], ['organization_id', $request->organization_id]])->get();
        return response()->json(['data' => $data]);
    }

    public function black_list_post_database(Request $request)
    {
        DB::beginTransaction();
        $response = 0;
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'organization_id.required' => 'Mã tổ chức không được để trống',
                //
                'blacklist_name.required' => 'Tên khách hàng không được để trống',
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
                'blacklist_name' => 'required',
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
                $data  = Blacklist::where([['phone', $request->phone], ['organization_id', $request->organization_id]])->get();
                if (count($data) > 0) {
                    $response = 2;
                    return response()->json($response);
                }
            }

            $black_list  = Blacklist::find($request->id);
            if (!$black_list) {
                $black_list = new Blacklist;
                $black_list->id = $request->id;
            }
            $black_list->organization_id = $request->organization_id;
            $black_list->created_by = $request_user->id;
            $black_list->updated_by = $request_user->id;
            $black_list->updated_at = $date;
            $black_list->created_at = $date;
            $black_list->deleted = 0;
            $black_list->actived = 1;
            $black_list->blacklist_name = $request->blacklist_name;
            $black_list->phone = $request->phone;
            $black_list->address = $request->address;
            if (isset($request->gender))
                $black_list->gender = $request->gender;
            if (isset($request->status))
                $black_list->status = $request->status;
            if (isset($request->ngay_sinh))
                $black_list->ngay_sinh = $request->ngay_sinh;
            if (isset($request->avatar)) {
                $avatar = str_replace(" ", "+", $request->avatar);
                $file = base64_decode($avatar);
                if ($file) {
                    $extension = $request->phone;
                    $img_source_name = time() . '.' . $extension . '.jpg';
                    $path = public_path() . '/images/blacklist/';
                    $upload = file_put_contents($path . $img_source_name, $file);
                    $black_list->avatar = $img_source_name;
                }
            }

            $black_list->save();

            $response = 1;
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            $response = 0;
        }
        return response()->json($response);
    }
}
