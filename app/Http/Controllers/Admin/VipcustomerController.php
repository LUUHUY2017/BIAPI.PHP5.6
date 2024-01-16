<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Validator;
use App\VipInfo as Vipinfo;
use Illuminate\Support\Facades\Log;

class VipcustomerController extends Controller
{
    public $default_table = 'vip_info';
    public $default_img_table = 'vip_img';

    public function retrieve_vip_data(Request $request)
    {
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'organization_id.required' => 'Mã tổ chức không được để trống',
                'organization_id.numeric' => 'Mã tổ chức phải là số',
                'organization_id.min' => 'Mã tổ chức có giá trị nhỏ nhất là :min'
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|numeric|min:1'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $response = [];
            $response['message'] = 1;
            $customerData = DB::table('vip_info')->where('organization_id', $request->organization_id)->orderBy('updated_at', 'DESC')->get();
            foreach ($customerData as $key => $value) {
                $image_path = public_path() . "/images/vipcustomer/$value->avatar";
                $value->image_path = $image_path;
                $default_image = public_path() . "/images/fba/no_image.png";
                if ($value->avatar) {
                    if (file_exists($image_path)) {
                        $value->avatar = base64_encode(file_get_contents($image_path));
                    } else {
                        $value->avatar = base64_encode(file_get_contents($default_image));
                    }
                } else {
                    $value->avatar = base64_encode(file_get_contents($default_image));
                }
            }
            $response['customerData'] = $customerData;
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [];
            $response['message'] = $e->getMessage();
            $response['code'] = $e->getCode();
            return response()->json($response);
        }
    }

    // Tool C# k dùng
    public function insert_vip_customer_api(Request $request)
    {
        DB::beginTransaction();
        $response = 0;
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'organization_id.required' => 'Mã tổ chức không được để trống',
                //
                'vip_name.required' => 'Tên khách hàng không được để trống',
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
                'vip_name' => 'required',
                'phone' => 'required',
                // 'avatar' => 'required',
                'id' => 'required'
            ];
            // if (isset($request->birthday)) {
            //     $validArray['birthday'] = 'required|min:5|max:100';
            // }
            // if (isset($request->birthday)) {
            //     $validArray['gender'] = 'boolean';
            // }
            $requestData = (array) $request->all();
            $validator = Validator::make($requestData, $validArray, $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $request_user = $request->user();
            $date = Carbon::now()->format('Y-m-d H:i:s');
            $vip_info  = Vipinfo::find($request->id);
            if (!$vip_info) {
                $vip_info = new Vipinfo;
                $vip_info->id = $request->id;
            }
            $vip_info->organization_id = $request->organization_id;
            $vip_info->created_by = $request_user->id;
            $vip_info->updated_by = $request_user->id;
            $vip_info->updated_at = $date;
            $vip_info->created_at = $date;
            $vip_info->deleted = 0;
            $vip_info->actived = 1;
            $vip_info->vip_name = $request->vip_name;
            $vip_info->phone = $request->phone;
            $vip_info->address = $request->address;
            if (isset($request->gender))
                $vip_info->gender = $request->gender;
            if (isset($request->ngay_sinh))
                $vip_info->ngay_sinh = $request->ngay_sinh;
            // if (isset($request->avatar)) {
            $avatar = str_replace(" ", "+", $request->avatar);
            $file = base64_decode($avatar);
            if ($file) {
                $extension = $request->phone;
                $img_source_name = time() . '.' . $extension . '.jpg';
                $path = public_path() . '/images/vipcustomer/';
                $upload = file_put_contents($path . $img_source_name, $file);
                $vip_info->avatar = $img_source_name;
            }
            // }


            $vip_info->save();

            $response = 1;
            DB::commit();
            // $response['message'] = 1;
            // $response['insertedData'] = $object;
        } catch (\Exception $e) {
            // $response = [];
            // $response['message'] = $e->getMessage();
            // $response['code'] = $e->getCode();
            // $response['line'] = $e->getLine();
            DB::rollback();
            $response = 0;
        }
        return response()->json($response);
    }

    public function sp_get_vip_customer_organization(Request $request)
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
            $request_user = $request->user();
            $user_id = $request_user->id;
            $org = $request->organization_id;
            $deleted = $request->deleted;
            $date = Carbon::now()->format('Y-m-d H:i:s');
            $retrieveData = DB::select("exec sp_get_vip_customer_organization $org, $user_id, $deleted");
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
                'vip_name.required' => 'Tên khách hàng không được để trống',
                'vip_name.min' => 'Tên khách hàng có ít nhất :min ký tự',
                'vip_name.max' => 'Tên khách hàng có nhiều nhất :max ký tự',
                //
                'phone.required' => 'Số điện thoại không được để trống',
                'phone.numeric' => 'Số điện thoại phải là số',
                'phone.unique' => 'Số điện thoại đã tồn tại',
                //
                'ngay_sinh.required' => 'Ngày sinh không được để trống',
                'ngay_sinh.date' => 'Ngày sinh không hợp lệ',
                //
                'avatar.image' => 'Ảnh đại diện phải là kiểu ảnh jpeg, png hoặc gif',
                'avatar.max' => 'Kích thước ảnh đại diện không vượt quá :max kb',
            ];
            $validArray = [
                'organization_id' => 'required|integer|min:1', 'vip_name' => 'required|min:3|max:100', 'phone' => 'required|numeric|unique:vip_info', 'ngay_sinh' => 'required|date'
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
            $random = mt_rand(10000000, 99999999);
            $request_user = $request->user();
            $date = Carbon::now()->format('Y-m-d');
            $object = new Vipinfo;
            $object->id = $uid;
            $object->created_at = $date;
            $object->created_by = $request_user->id;
            $object->updated_at = $date;
            $object->updated_by = $request_user->id;
            $object->deleted = 0;
            $object->actived = "1";
            $object->vip_name = $request->vip_name;
            $object->organization_id = $request->organization_id;
            $object->vip_code = $random;
            $object->phone = $request->phone;
            $object->gender = $request->gender;
            $object->ngay_sinh = $request->ngay_sinh;
            $object->avatar = 'default.jpg';
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $extension = $file->getClientOriginalName();
                $img_source_name = time() . '.' . $extension;
                $path = public_path() . '/images/vipcustomer';
                $upload = $file->move($path, $img_source_name);
                $object->avatar = $img_source_name;
            }
            $object->save();
            DB::commit();
            $object->gid = DB::table('vip_info')->where('id', $uid)->get()[0]->id;
            $object->username_updated = DB::table('users')->where('id', $object->updated_by)->get()[0]->name;
            $object->date_compare = $object->ngay_sinh;
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
                'id.required' => 'Mã khách hàng không được để trống',
                'id.gui_type' => 'Mã khách hàng không hợp lệ',
                //
                'vip_name.required' => 'Tên khách hàng không được để trống',
                'vip_name.min' => 'Tên khách hàng có ít nhất :min ký tự',
                'vip_name.max' => 'Tên khách hàng có nhiều nhất :max ký tự',
                //
                'phone.required' => 'Số điện thoại không được để trống',
                'phone.numeric' => 'Số điện thoại phải là số',
                'phone.unique' => 'Số điện thoại đã tồn tại',
                //
                'ngay_sinh.required' => 'Ngày sinh không được để trống',
                'ngay_sinh.date' => 'Ngày sinh không hợp lệ',
                //
                'avatar.image' => 'Ảnh đại diện phải là kiểu ảnh jpeg, png hoặc gif',
                'avatar.max' => 'Kích thước ảnh đại diện không vượt quá :max kb',
                //
                'actived.required' => 'Mã kích hoạt không được để trống',
                'actived.boolean' => 'Mã kích hoạt không phù hợp',
            ];
            $validArray = [
                'id' => 'required|gui_type', 'vip_name' => 'required|min:3|max:100', 'ngay_sinh' => 'required|date', 'actived' => 'required|boolean'
            ];
            if (isset($request->phone)) {
                $validArray['phone'] = 'required|numeric|unique:vip_info';
            }
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
            $object = Vipinfo::where('id', $request->id)->first();
            $object->updated_at = $date;
            $object->updated_by = $request_user->id;
            $object->actived = $request->actived;
            $object->vip_name = $request->vip_name;
            if (isset($request->phone)) {
                $object->phone = $request->phone;
            }
            if (isset($request->gender)) {
                $object->gender = $request->gender;
            }
            $object->ngay_sinh = $request->ngay_sinh;
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $extension = $file->getClientOriginalName();
                $img_source_name = time() . '.' . $extension;
                $path = public_path() . '/images/vipcustomer';
                $upload = $file->move($path, $img_source_name);
                $object->avatar = $img_source_name;
            }
            $object->save();
            DB::commit();
            $object->gid = DB::table('vip_info')->where('id', $uid)->get()[0]->id;
            $object->username_updated = DB::table('users')->where('id', $object->updated_by)->get()[0]->name;
            $object->date_compare = $object->ngay_sinh;
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

    //  Xóa + rollback   Tool C# k dùng
    public function delete_vip_customer(Request $request)
    {
        try {
            $updateData = [
                'deleted' => $request->deleted
            ];
            $id = $request->id;
            $request_user = $request->user();
            DB::table($this->default_table)->where('id', $id)->update($updateData);
            // DB::table($this->default_img_table)->where('vip_id', $id)->update($updateData);
            return response()->json(['message' => 1]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    // Tool C#
    public function sp_get_vip_customer(Request $request)
    {
        $request_user = $request->user();
        $user_id = $request_user->id;
        $org = $request->organization_id;
        $deleted = $request->deleted;
        $record = $request->record ? $request->record : 30;
        // $data = DB::select("exec sp_get_vip_customer_organization $org, $user_id, $deleted");
        $data_response = DB::table('vip_info')->where([['organization_id', $org], ['deleted', $deleted]])->orderBy('updated_at', 'DESC')->paginate($record);
        return response()->json(['data_response' => $data_response]);
    }

    public function get_info_with_phone_number(Request $request)
    {
        // $data = DB::table($this->default_table)->where([['phone', $request->phone_number], ['deleted', 0], ['organization_id', $request->organization_id]])->get();
        $data = DB::table($this->default_table)->where([['phone', $request->phone_number], ['organization_id', $request->organization_id]])->get();
        return response()->json(['data' => $data]);
    }

    public function vip_customer_post_database(Request $request)
    {
        DB::beginTransaction();
        $response = 0;
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'organization_id.required' => 'Mã tổ chức không được để trống',
                //
                'vip_name.required' => 'Tên khách hàng không được để trống',
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
                'vip_name' => 'required',
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
                $data  = Vipinfo::where([['phone', $request->phone], ['organization_id', $request->organization_id]])->get();
                if (count($data) > 0) {
                    $response = 2;
                    return response()->json($response);
                }
            }

            $vip_info  = Vipinfo::find($request->id);
            if (!$vip_info) {
                $vip_info = new Vipinfo;
                $vip_info->id = $request->id;
            }
            $vip_info->organization_id = $request->organization_id;
            $vip_info->created_by = $request_user->id;
            $vip_info->updated_by = $request_user->id;
            $vip_info->updated_at = $date;
            $vip_info->created_at = $date;
            $vip_info->deleted = 0;
            $vip_info->actived = 1;
            $vip_info->vip_name = $request->vip_name;
            $vip_info->phone = $request->phone;
            $vip_info->address = $request->address;
            if (isset($request->gender))
                $vip_info->gender = $request->gender;
            if (isset($request->status))
                $vip_info->status = $request->status;
            if (isset($request->ngay_sinh))
                $vip_info->ngay_sinh = $request->ngay_sinh;
            if (isset($request->avatar)) {
                $avatar = str_replace(" ", "+", $request->avatar);
                $file = base64_decode($avatar);
                if ($file) {
                    $extension = $request->phone;
                    $img_source_name = time() . '.' . $extension . '.jpg';
                    $path = public_path() . '/images/vipcustomer/';
                    $upload = file_put_contents($path . $img_source_name, $file);
                    $vip_info->avatar = $img_source_name;
                }
            }

            $vip_info->save();

            $response = 1;
            DB::commit();
            // $response['message'] = 1;
            // $response['insertedData'] = $object;
        } catch (\Exception $e) {
            // $response = [];
            // $response['message'] = $e->getMessage();
            // $response['code'] = $e->getCode();
            // $response['line'] = $e->getLine();
            DB::rollback();
            $response = 0;
        }
        return response()->json($response);
    }
}
