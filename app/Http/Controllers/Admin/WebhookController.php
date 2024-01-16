<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Validator;
use App\Webhook;
use App\Site;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
class WebhookController extends Controller
{

    public function send_to_callback_url(Request $request) {
        try {
            $current_date = Carbon::now()->format('Y-m-d');
            Log::useFiles(base_path() . '/dailyLog/'. $current_date .'-webhook.log', 'info');
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|min:1'
                , 'data' => 'required'
            ]);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $organization_id = $request->organization_id;
            $webhook = Webhook::where('organization_id', $organization_id)->first();
            $data = $request->data;
            if($webhook) {
                $client = new Client([
                    'headers' => [ 'Content-Type' => 'application/json' ]
                    , 'timeout' => 5
                    , 'http_errors' => false
                ]);
                $response = $client->post($webhook->callback_url, [
                    'json' => (array) $data
                ]);
                // $response = $client->request('POST', $webhook->callback_url, [
                //     'form_params' => (array) $data
                // ]);
                return response()->json(['url' => $webhook->callback_url, 'status' => 1, 'reason' => $response->getReasonPhrase(), 'body' => (string)$response->getBody()], $response->getStatusCode());
            } else {
                throw new Exception("The webhook is not exist", 770);
            }
        } catch (\Exception $e) {
            $response = [];
            $response['message'] = $e->getMessage();
            $response['code'] = $e->getCode();
            $response['line'] = $e->getLine();
            return response()->json($response, 404);
        }
    }
    public function sp_get_web_hook(Request $request) {
        $request_user = $request->user();
        $user_id = $request_user->id;
        $org = $request->organization_id;
        $site_id = $request->site_id;
        $module_id = $request->module_id;
        $deleted = $request->deleted;
        $data = DB::select("exec sp_get_web_hook $org, $user_id, $site_id, $module_id, $deleted");
        return response()->json(['webhookArray' => $data]);
    }

    public function post_add(Request $request) {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'organization_id.required' => 'Mã tổ chức không được để trống',
                'organization_id.numeric' => 'Mã tổ chức phải là số',
                'organization_id.min' => 'Mã tổ chức có giá trị nhỏ nhất là :min',
                // Mã địa điểm phải là số
                'site_id.required' => 'Mã site không được để trống',
                'site_id.numeric' => 'Mã site phải là số',
                'site_id.min' => 'Mã site có giá trị nhỏ nhất là :min',
                // Trường tên địa điểm
                'callback_url.required' => 'Địa chỉ không được để trống',
                'callback_url.min' => 'Địa chỉ phải có ít nhất :min kí tự',
                'callback_url.max' => 'Địa chỉ có nhiều nhất :max kí tự',
                'callback_url.url' => 'Địa chỉ có phải là kiểu url',
                // Trường mã địa điểm
                'location_code.required' => 'Mã địa điểm không được để trống',
                'location_code.min' => 'Mã địa điểm phải có ít nhất :min kí tự',
                'location_code.max' => 'Mã địa điểm có nhiều nhất :max kí tự',
                // Trường mã địa điểm
                'module_id.required' => 'Module không được để trống',
                'module_id.min' => 'Module phải có giá trị ít nhất là :min',
                'module_id.max' => 'Module phải có giá trị nhiều nhất là :max',
                // Trường mã địa điểm
                'webhook_name.min' => 'Tên webhook phải có ít nhất :min kí tự',
                'webhook_name.max' => 'Tên webhook phải có nhiều nhất :max kí tự'
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|numeric|min:1'
                , 'site_id' => 'required|numeric|min:0'
                , 'callback_url' => 'required|min:5|max:200|url'
                , 'module_id' => 'required|min:1|max:5'
                , 'webhook_name' => 'sometimes|min:3|max:200'
            ], $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            $access_token = $request_user->createToken('Webhook')->accessToken;
            $date = Carbon::now()->format('Y-m-d H:i:s');
            $object = new Webhook;
            $object->organization_id = $request->organization_id;
            $object->site_id = $request->site_id;
            $callback_url = trim($request->callback_url);
            $object->callback_url = $callback_url;
            if(isset($request->webhook_name)) {
                $object->webhook_name = $request->webhook_name;
            }
            $object->module_id = $request->module_id;
            $object->access_token = $access_token;
            $object->expire_date = Carbon::now()->addDays(180);
            $object->created_at = $date;
            $object->updated_at = $date;
            $object->created_by = $request_user->id;
            $object->updated_by = $request_user->id;
            $object->actived = 1;
            $object->deleted = 0;
            $object->save();
            DB::commit();
            // $site = Site::find($request->site_id);
            // $object->site_name = $site->site_name;
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

    public function post_edit(Request $request) {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'organization_id.required' => 'Mã tổ chức không được để trống',
                'organization_id.integer' => 'Mã tổ chức phải là số',
                'organization_id.min' => 'Mã tổ chức có giá trị nhỏ nhất là :min',
                // Mã tổ chức phải là số
                'id.required' => 'Mã webhook không được để trống',
                'id.integer' => 'Mã webhook phải là số',
                'id.min' => 'Mã webhook có giá trị nhỏ nhất là :min',
                // Mã địa điểm phải là số
                'site_id.required' => 'Mã site không được để trống',
                'site_id.numeric' => 'Mã site phải là số',
                'site_id.min' => 'Mã site có giá trị nhỏ nhất là :min',
                // Trường tên địa điểm
                'callback_url.required' => 'Địa chỉ không được để trống',
                'callback_url.min' => 'Địa chỉ phải có ít nhất :min kí tự',
                'callback_url.max' => 'Địa chỉ có nhiều nhất :max kí tự',
                'callback_url.url' => 'Địa chỉ có phải là kiểu url',
                // Trường mã địa điểm
                'location_code.required' => 'Mã địa điểm không được để trống',
                'location_code.min' => 'Mã địa điểm phải có ít nhất :min kí tự',
                'location_code.max' => 'Mã địa điểm có nhiều nhất :max kí tự',
                // Trường mã địa điểm
                'module_id.required' => 'Module không được để trống',
                'module_id.min' => 'Module phải có giá trị ít nhất là :min',
                'module_id.integer' => 'Module phải là số',
                'module_id.max' => 'Module phải có giá trị nhiều nhất là :max',
                // Trường mã địa điểm
                'webhook_name.min' => 'Tên webhook phải có ít nhất :min kí tự',
                'webhook_name.max' => 'Tên webhook phải có nhiều nhất :max kí tự'
            ];
            $validArray = [
                'module_id' => 'required|integer|min:1|max:5'
                , 'organization_id' => 'required|integer|min:1'
            ];
            if(isset($request->callback_url)) {
                $validArray['callback_url'] = 'min:5|max:200|url';
                $callback_url = trim($request->callback_url);
            }
            if(isset($request->id)) {
                $validArray['id'] = 'integer|min:1';
            }
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), $validArray , $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            $date = Carbon::now()->format('Y-m-d H:i:s');
            $object = Webhook::find($request->id);
            $response = [];
            if($object) {
                $object->callback_url = $callback_url;
                $object->save();
                $response['updatedData'] = $object;
            } else {
                $access_token = str_random(20);
                $newObject = new Webhook;
                $newObject->created_at = $date;
                $newObject->created_by = $request_user->id;
                $newObject->updated_by = $request_user->id;
                $newObject->updated_at = $date;
                $newObject->actived = 1;
                $newObject->deleted = 0;
                $newObject->module_id = $request->module_id;
                $newObject->organization_id = $request->organization_id;
                $newObject->access_token = $access_token;
                $newObject->expire_date = Carbon::now()->addDays(180);
                $newObject->callback_url = isset($callback_url) ? $callback_url : NULL;
                $newObject->site_id = 0;
                $newObject->save();
                $response['updatedData'] = $newObject;
            }
            DB::commit();
            $response['message'] = 1;
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

    public function soft_delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã vị trí phải là số
                'id.required' => 'Mã webhook không được để trống',
                'id.numeric' => 'Mã webhook phải là số',
                'id.min' => 'Mã webhook có giá trị nhỏ nhất là :min',
                // Mã vị trí phải là số
                'deleted.required' => 'Mã deleted không được để trống',
                'deleted.numeric' => 'Mã deleted phải là số',
                'deleted.between' => 'The :attribute must be between :min - :max.',
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|min:1'
                , 'deleted' => 'required|numeric|between:0,1'
            ], $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $id = $request->id;
            $object = Webhook::find($id);
            if($request->deleted == 1) {
                $object->actived = 1;
                $object->deleted = 0;
            } else if($request->deleted == 0) { // Nếu là đánh dấu xóa
                $object->actived = 0;
                $object->deleted = 1;
            }
            $object->save();
            DB::commit();
            return response()->json(['message' => 1]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    public function delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã vị trí phải là số
                'id.required' => 'Mã webhook không được để trống',
                'id.numeric' => 'Mã webhook phải là số',
                'id.min' => 'Mã webhook có giá trị nhỏ nhất là :min',
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|min:1'
            ], $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $id = $request->id;
            DB::table('web_hooks')->where('id', $id)->delete();
            DB::commit();
            return response()->json(['message' => 1]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()]);
        }
    }
}
