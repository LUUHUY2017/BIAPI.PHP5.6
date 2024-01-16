<?php

namespace App\Http\Controllers\Fba;

use App\FbaAppicationSetting as DefaultModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\FbaTablet;
use File;
use Image;
use Illuminate\Support\Facades\Validator;
use Exception;
use Carbon\Carbon;
// use ElephantIO\Client;
// use ElephantIO\Engine\SocketIO\Version1X;
// require __DIR__ . '/../../../../vendor/autoload.php';
use ElephantIO\Client, ElephantIO\Engine\SocketIO\Version2X, ElephantIO\Exception\ServerConnectionFailureException;

require __DIR__ . '/../../../../vendor/autoload.php';

class FbaAppicationSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData(Request $request)
    {
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'deleted.required' => 'Mã deleted không được để trống',
                'deleted.integer' => 'Mã deleted phải là số',
                'deleted.min' => 'Mã deleted nhỏ nhất phải là :min'
            ];
            $validator = Validator::make($request->all(), [
                'deleted' => 'required|boolean'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            // $request_user = $request->user();
            // $deleted = $request->deleted;
            // $user_id =  $request_user->id;
            // $retrieveData = DB::select("exec sp_fba_appication_settings $user_id, $deleted");
            // foreach ($retrieveData as $key => $value) {
            //     $value->application_logo = asset("images/fba/$value->application_logo");
            //     $value->company_logo = asset("images/fba/$value->company_logo");
            // }

            $request_user = $request->user();
            $user_id = $request_user->id;
            $user_lever = (int) $request_user->lever;
            $organization_id = 0;
            if ($request_user->organization_id)
                $organization_id = (int) $request_user->organization_id;
            $deleted = $request->deleted;
            $retrieveData = DB::select("exec sp_fba_appication_settings_use $user_id, $organization_id, $deleted");
            if ($user_lever == 0) {
                $data2 = DB::select("select N'Cấu hình mặc định' as organization_name ,* from fba_appication_settings ft  where ft.organization_id = 0 and ft.deleted =0");
                $default =  $data2[0];
                array_push($retrieveData, $default);
            }
            foreach ($retrieveData as $key => $value) {
                $value->application_logo = asset("images/fba/$value->application_logo");
                $value->company_logo = asset("images/fba/$value->company_logo");
            }
            $response = [];
            $response['status'] = 1;
            $response['retrieveData'] = $retrieveData;
            $response['isSuperAdmin'] = $this->isSuperAdmin($request_user);
            return response()->json($response);
        } catch (Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }
    public function insert(Request $request)
    {
        try {
            $data = json_decode($request->data);
            $error_messages = [
                // Mã tổ chức phải là số
                'deleted.required' => 'Mã tổ chức không được để trống',
                'deleted.integer' => 'Mã tổ chức phải là số',
                'deleted.min' => 'Mã tổ chức nhỏ nhất phải là :min'
            ];
            $validator = Validator::make($request->all(), [
                'deleted' => 'required|boolean'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $date = $this->getDateNow();
            $request_user = $request->user();
            $user_id =  $request_user->id;
            $organization_id = $request_user->organization_id;
            $object = new DefaultModel();
            $object->organization_id = $request->organization_id;
            if ($request->hasFile('company_logo')) {
                $file = $request->file('company_logo');
                $extension = $file->getClientOriginalName();
                $company_logo_name = time() . '.' . $extension;
                $path = public_path('/images/fba');
                $upload = $file->move($path, $company_logo_name);
                // Nghĩa thêm đoạn này để resize ảnh
                // $thumbnailpath = $path . "$extension";
                // Image::make($thumbnailpath)->resize(125, 125)->save($thumbnailpath);
                // end nghĩa
            }
            $response = [];
            $response['status'] = 1;
            $response['retrieveData'] = $object;
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
        $organization_id = 6;
        DB::beginTransaction();
        try {

            if ($request->hasFile('application_logo')) {
                $file = $request->file('application_logo');
                $extension = $file->getClientOriginalName();
                $application_logo_name = time() . '.' . $extension;
                $path = public_path('/images/fba');
                $upload = $file->move($path, $application_logo_name);
                // Nghĩa thêm đoạn này để resize ảnh
                // $thumbnailpath = $path . "$extension";
                // Image::make($thumbnailpath)->resize(125, 125)->save($thumbnailpath);
                // end nghĩa
            }

            $customer_info_name_require = 1;
            $customer_info_phone_require = 1;
            $customer_info_email_require = 1;
            $any_require = (int) $data->customer_info_any_require;
            if ($any_require === 0) {
                $customer_info_name_require = $data->customer_info_name_require;
                $customer_info_email_require = $data->customer_info_email_require;
                $customer_info_phone_require = $data->customer_info_phone_require;
            }
            if ($data->organization_id) {
                $organization_id = $data->organization_id;
            }
            $object = DB::table('fba_appication_settings')->insert([
                'organization_id' => $organization_id,
                'company_logo' => $company_logo_name,
                'application_logo' => $application_logo_name,
                'reason_title' => $data->reason_title,
                'finish_message' => $data->finish_message,
                'finish_message2' => $data->finish_message2,
                'reason_other_title' => $data->reason_other_title,
                'reason_other_highligt' => $data->reason_other_highligt,
                'btn_cancel' => $data->btn_cancel,
                'btn_send' => $data->btn_send,
                'customer_info_title' => $data->customer_info_title,
                'customer_info_name' => $data->customer_info_name,
                'customer_info_phone' => $data->customer_info_phone,
                'customer_info_email' => $data->customer_info_email,
                'customer_info_name_require' => $customer_info_name_require,
                'customer_info_phone_require' => $customer_info_phone_require,
                'customer_info_email_require' => $customer_info_email_require,
                'customer_info_any_require' => $data->customer_info_any_require,
                'actived_cancel' => $data->actived_cancel
            ]);
            DB::commit();
            return response()->json(['message' => 1]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 0]);
        }
    }
    public function getUpdate(Request $request)
    {
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'deleted.required' => 'Mã tổ chức không được để trống',
                'deleted.integer' => 'Mã tổ chức phải là số',
                'deleted.min' => 'Mã tổ chức nhỏ nhất phải là :min'
            ];
            $validator = Validator::make($request->all(), [
                'deleted' => 'required|boolean', 'id' => 'required|integer|min:1'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }

            $request_user = $request->user();
            $user_id =  $request_user->id;
            $id = $request->id;
            $fba_appication_settings = DB::select('select organization_id from fba_appication_settings WHERE id=' . $id);
            $organization_id = $fba_appication_settings[0]->organization_id;
            if ($request_user->organization_id)
                $organization_id = (int) $request_user->organization_id;
            $deleted = $request->deleted;
            if ($organization_id != 0)
                $retrieveData = DB::select("exec sp_fba_appication_settings_use $user_id, $organization_id, $deleted, $id");
            else
                $retrieveData = DB::select("select N'Cấu hình mặc định' as organization_name ,* from fba_appication_settings ft  where ft.organization_id = 0 and ft.deleted =0");

            $response['status'] = 1;
            $response['retrieveData'] = $retrieveData[0];
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            $response['code'] = $e->getLine();
            return response()->json($response);
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'id.required' => 'Mã tổ chức không được để trống',
                'id.integer' => 'Mã tổ chức phải là số',
                'id.min' => 'Mã tổ chức nhỏ nhất phải là :min',
                'form_data.required' => 'Data không được để trống'
            ];
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|min:1',
                'form_data' => 'required'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $data = json_decode($request->form_data);
            $object = DefaultModel::findOrFail($data->id);
            $object->reason_title = $data->reason_title;
            $object->finish_message = $data->finish_message;
            $object->finish_message2 = $data->finish_message2;
            $object->reason_other_title = $data->reason_other_title;
            $object->reason_other_highligt = $data->reason_other_highligt;
            $object->btn_cancel = $data->btn_cancel;
            $object->btn_send = $data->btn_send;
            $object->customer_info_title = $data->customer_info_title;
            $object->customer_info_name = $data->customer_info_name;
            $object->customer_info_phone = $data->customer_info_phone;
            $object->customer_info_email = $data->customer_info_email;
            $object->customer_info_any_require = $data->customer_info_any_require;
            $object->actived_cancel = $data->actived_cancel;
            $any_require = (int) $data->customer_info_any_require;
            if ($any_require === 0) {
                $object->customer_info_name_require = $data->customer_info_name_require;
                $object->customer_info_phone_require = $data->customer_info_phone_require;
                $object->customer_info_email_require = $data->customer_info_email_require;
            }
            $urlFile = public_path('/images/fba/');
            if ($request->hasFile('application_logo')) {
                $file = $request->file('application_logo');
                $object->application_logo = $this->saveImageFile($file, $urlFile);
            }
            if ($request->hasFile('company_logo')) {
                $file = $request->file('company_logo');
                $object->company_logo = $this->saveImageFile($file, $urlFile);
            }
            $object->save();
            DB::commit();
            // if ($object == 1) {
            //     $token_type = 'Bearer';
            //     $url_socket = env('URL_SOCKET');
            //     $socketClient =  new Client(new Version2X($url_socket));
            //     $socketClient->initialize();
            //     $socketClient->emit('fba_tablet_reload_data', ['organization_id' => $data->organization_id, 'tocken_type' => $token_type, 'access_token' => $request->access_token]);
            //     $socketClient->close();
            // }
            $response = [
                'status' => 1, 'insertedData' => $object
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

    public function delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                'id.required' => 'Mã danh mục không được để trống', 'id.min' => 'Mã danh mục có giá trị nhỏ nhất là :min', 'id.integer' => 'Mã danh mục phải là giá trị số'
            ];
            $request_user = $request->user();
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|min:1'
            ], $error_messages);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $id = $request->id;
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
            DB::rollback();
            return response()->json($response);
        }
    }
    public function post_update($id, Request $request)
    {
        // lấy tên ảnh cũ
        $old_record = DB::select('select application_logo from fba_appication_settings WHERE id=' . $id);
        $application_logo_name = $old_record[0]->application_logo;
        $company_logo_url = null;
        $application_logo_url = null;
        DB::beginTransaction();
        try {

            if ($request->hasFile('application_logo')) {
                $application_logo_url = public_path() . "/images/fba/" . $application_logo_name;
                $file = $request->file('application_logo');
                $extension = $file->getClientOriginalName();
                $application_logo_name =  time() . '.' . $extension;
                $path = public_path() . '/images/fba';
                $upload = $file->move($path, $application_logo_name);
            }
            $data = json_decode($request->data);

            $organization_id = (int) $data->organization_id;
            if ($organization_id != 0) {
                $company_logo = DB::select('select company_logo from organizations WHERE id=' . $organization_id);
                $company_logo_name = $company_logo[0]->company_logo;

                if ($request->hasFile('company_logo')) {
                    $company_logo_url = public_path() . "/images/fba/" . $company_logo_name;
                    $file = $request->file('company_logo');
                    $extension = $file->getClientOriginalName();
                    $company_logo_name =  time() . '.' . $extension;
                    $path = public_path() . '/images/fba';
                    $upload = $file->move($path, $company_logo_name);
                    // $thumbnailpath = $path . "/$extension";
                    // Image::make($thumbnailpath)->resize(125, 125)->save($thumbnailpath);
                    DB::table('organizations')->where('id', $organization_id)->update(['company_logo' =>  $company_logo_name]);
                }
            } else {
                $company_logo = DB::select('select * from fba_appication_settings  WHERE organization_id = 0 AND deleted = 0');
                $company_logo_name = $company_logo[0]->company_logo;

                if ($request->hasFile('company_logo')) {
                    $company_logo_url = public_path() . "/images/fba/" . $company_logo_name;
                    $file = $request->file('company_logo');
                    $extension = $file->getClientOriginalName();
                    $company_logo_name = time() . '.' . $extension;
                    $path = public_path() . '/images/fba';
                    // $thumbnailpath = $path . "/$extension";
                    // Image::make($thumbnailpath)->resize(125, 125)->save($thumbnailpath);
                    $upload = $file->move($path, $company_logo_name);
                }
            }
            $update_array = array(
                'company_logo' => $company_logo_name,
                'application_logo' => $application_logo_name,
                'reason_title' => $data->reason_title,
                'finish_message' => $data->finish_message,
                'finish_message2' => $data->finish_message2,
                'reason_other_title' => $data->reason_other_title,
                'reason_other_highligt' => $data->reason_other_highligt,
                'btn_cancel' => $data->btn_cancel,
                'btn_send' => $data->btn_send,
                'customer_info_title' => $data->customer_info_title,
                'customer_info_name' => $data->customer_info_name,
                'customer_info_phone' => $data->customer_info_phone,
                'customer_info_email' => $data->customer_info_email,
                'customer_info_any_require' => $data->customer_info_any_require,
                'actived_cancel' => $data->actived_cancel
            );
            $any_require = (int) $data->customer_info_any_require;
            if ($any_require === 0) {
                $update_array['customer_info_name_require'] = $data->customer_info_name_require;
                $update_array['customer_info_phone_require'] = $data->customer_info_phone_require;
                $update_array['customer_info_email_require'] = $data->customer_info_email_require;
            }
            $object = DB::table('fba_appication_settings')->where('id', $id)->update($update_array);
            DB::commit();
            if ($object == 1) {
                try {
                    $token_type = 'Bearer';
                    $url_socket = env('URL_SOCKET');
                    $socketClient =  new Client(new Version2X($url_socket));
                    $socketClient->initialize();
                    $socketClient->emit('fba_tablet_reload_data', ['organization_id' => $data->organization_id, 'tocken_type' => $token_type, 'access_token' => $request->access_token]);
                    $socketClient->close();
                    $socket = 'OK';
                } catch (ServerConnectionFailureException $e) {
                    $socket = $e;
                }
            }
            return response()->json(['message' => $object, 'socket' => $socket]);
        } catch (\Throwable $e) {
            DB::rollback();
            return response()->json(['message' => 0]);
        }
    }
    // Bỏ
    public function get_for_tablet(Request $request)
    {
        $request_user = $request->user();

        $user_id = 0;
        if ($request_user != null && $request_user->lever > 0) {
            $user_id = $request_user->id;
        }

        $apps = DB::select("exec sp_fba_application_setting_for_tablet_bo $user_id");

        $folder = public_path('/images/fba/');

        $app = $apps[0];
        $app_seting = array(
            'id' => $app->id, 'organization_id' => (int) $app->organization_id, 'company_logo' => base64_encode(file_get_contents($folder . $app->company_logo)), 'application_logo' => base64_encode(file_get_contents($folder . $app->application_logo))

            // , 'popup_time_out' =>  (int)$app->popup_time_out // Thoi gian timeout của các màn hình
            // , 'reason_time_out' =>  (int)$app->reason_time_out
            // , 'reason_other_time_out' =>  (int)$app->reason_other_time_out
            // , 'customer_info_time_out' =>  (int)$app->customer_info_time_out
            // , 'finish_time_out' =>  (int)$app->finish_time_out

            , 'login_title' => $app->login_title, 'login_txt_username' => $app->login_txt_username, 'login_txt_password' => $app->login_txt_password, 'login_btn_signin' => $app->login_btn_signin, 'finish_message' => $app->finish_message, 'reason_title' => $app->reason_title, 'reason_other_title' => $app->reason_other_title, 'btn_cancel' => $app->btn_cancel, 'btn_send' => $app->btn_send, 'customer_info_title' => $app->customer_info_title, 'customer_info_name' => $app->customer_info_name, 'customer_info_phone' => $app->customer_info_phone, 'customer_info_email' => $app->customer_info_email

        );

        return response()->json($app_seting);
    }
}
