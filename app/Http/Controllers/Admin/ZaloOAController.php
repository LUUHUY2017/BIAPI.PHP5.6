<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Carbon\Carbon;
use Exception;
use App\OAZalo as DefaultModel;
use App\EventAccount;
use Illuminate\Support\Facades\Validator;
class ZaloOAController extends Controller
{
    public function get_zalo_follower_selected(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'event_code' => 'required|without_space'
                , 'site_id' => 'required|integer|min:1'
            ]);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $event_code = $request->event_code;
            $eventInfo = DB::table('events')->where('event_code', $event_code)->first();
            if ($eventInfo == null) {
                throw new Exception('event not found', 770);
            }
            $site_id = $request->site_id;
            $event_id = $eventInfo->id;
            $siteInfo = DB::table('sites')->where('actived', 1)->where('deleted', 0)->where('id', $site_id)->first();
            if ($siteInfo == null) {
                throw new Exception('organization not found', 770);
            }
            $organization_id = $siteInfo->organization_id;
            $response = [];
            $response['official_account_array'] = DB::select("SELECT o.id, o.official_account, o.secret FROM oa_zalo o INNER JOIN event_account ea ON ea.account_id = o.id
                INNER JOIN events e ON ea.event_id = e.id
                WHERE e.id = $event_id AND o.organization_id = $organization_id");
            $response['follower_array'] = DB::select("exec sp_get_zalo_follower_api $event_id, $site_id");
            // $response['webhook_array'] = DB::table('web_hooks')->where('organization_id', $organization_id)->get();
            return response()->json($response);
        }
        catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
    public function getData(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|min:1'
                , 'deleted' => 'required|boolean'
            ]);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            $user_id = $request_user->id;
            $orgId = $request->organization_id;
            $deleted = $request->deleted;
            $retrieveData = DefaultModel::where('organization_id', $orgId)->first();
            $retrieveData->qrcode_image = asset("/images/$retrieveData->qrcode_image");
            $response = [
                'status' => 1
                , 'retrieveData' => $retrieveData
            ];
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [
                'status' => 0
                , 'message' => $e->getMessage()
                , 'code' => $e->getCode()
            ];
            return response()->json($response);
        }
    }

    public function update(Request $request) {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã tổ chức phải là số
                'id.required' => 'Mã tổ chức không được để trống',
                'id.numeric' => 'Mã tổ chức phải là số',
                'id.min' => 'Mã tổ chức nhỏ nhất phải là :min',
                // Trường tên tài khoản
                'display_name.required' => 'Tên hiển thị không được để trống',
                'display_name.without_space' => 'Tên hiển thị chỉ bao gồm kí tự A-Z, 0-9, gạch ngang và gạch dưới',
                'display_name.min' => 'Tên hiển thị phải có ít nhất :min kí tự',
                'display_name.max' => 'Tên hiển thị có nhiều nhất :max kí tự',
                //
                'official_account.required' => 'Tên tài khoản không được để trống',
                'official_account.without_space' => 'Tên tài khoản chỉ bao gồm kí tự A-Z, 0-9, gạch ngang và gạch dưới',
                'official_account.min' => 'Tên tài khoản phải có ít nhất :min kí tự',
                'official_account.max' => 'Tên tài khoản có nhiều nhất :max kí tự',
                // Trường mật khẩu
                'secret.required' => 'Mật khẩu không được để trống',
                'secret.without_space' => 'Mật khẩu chỉ bao gồm kí tự A-Z, 0-9, gạch ngang và gạch dưới',
                'secret.min' => 'Mật khẩu phải có ít nhất :min kí tự',
                'secret.max' => 'Mật khẩu có nhiều nhất :max kí tự',
                'same' => 'Mật khẩu nhập vào không trùng khớp',
                // Trường account_type
                'invite_code.required' => 'Cú pháp mời zalo không được để trống',
                'invite_code.unique' => 'Cú pháp mời zalo đã tồn tại',
                'invite_code.min' => 'Cú pháp mời zalo phải có ít nhất là :min',
                'invite_code.max' => 'Cú pháp mời zalo phải có nhiều nhất là :max',
                'invite_code.without_space' => 'Cú pháp mời zalo không bao gồm khoảng trắng',
            ];
            $validatorArray = [
                'display_name' => 'required|min:3|max:50'
                , 'official_account' => 'required|without_space|min:3|max:50'
                , 'secret' => 'required|without_space|min:3|max:50'
                , 'organization_id' => 'required|integer|min:1'
            ];
            if (isset($request->invite_code)) {
                $validatorArray['invite_code'] = 'required|without_space|min:3|max:15|unique:oa_zalo';
            }
            $validator = Validator::make($request->all(), $validatorArray, $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $date = $this->getDateNow();
            $request_user = $request->user();
            $organization_id = $request->organization_id;
            $object = DefaultModel::where('organization_id', $organization_id)->first();
            if($object == null) {
                $object = new DefaultModel();
                $object->organization_id = $organization_id;
                $object->created_at = $date;
                $object->created_by = $request_user->id;
            }
            $object->display_name = $request->display_name;
            $object->official_account = $request->official_account;
            $object->secret = $request->secret;
            if (isset($request->invite_code)) {
                $object->invite_code = $request->invite_code;
            }
            if ($request->hasFile('qrcode_image')) {
                $file = $request->file('qrcode_image');
                $path = public_path('/images/');
                $object->qrcode_image = $this->saveImageFile($file, $path);
            }
            $object->actived = $request->actived;
            $object->updated_at = $date;
            $object->updated_by = $request_user->id;
            $object->save();
            DB::commit();
            $response = [];
            $response['status'] = 1;
            $response['updatedData'] = $object;
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

    public function eventGetData(Request $request) {
        try {
            $message = [
                'id.required' => 'Mã tài khoản không được để trống',
                'id.numeric' => 'Mã tài khoản phải là số',
                'id.min' => 'Mã tài khoản nhỏ nhất phải là :min',
                // Mã delete
                'deleted.required' => 'Mã deleted không được để trống',
                'deleted.numeric' => 'Mã deleted phải là số',
                'deleted.between' => 'Mã deleted chỉ là :min hoặc :max',
            ];
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|min:1'
            ], $message);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $response = [];
            $organization_id = $request->organization_id;
            $account = DefaultModel::where('organization_id', $organization_id)->first();
            if ($account == null) {
                $json_error = json_encode(['Không tìm thấy OA zalo tại tổ chức']);
                throw new Exception($json_error, 770);
            }
            $response['retrieveData'] = EventAccount::tryGetAllAccountZalo($organization_id);
            $response['eventArray'] = DB::select("SELECT e.id, e.event_name, e.event_code FROM events e");
            $response['accountInfo'] = $account;
            $response['status'] = 1;
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [
                'status' => 0
                , 'message' => $e->getMessage()
                , 'code' => $e->getCode()
            ];
            return response()->json($response);
        }
    }

    public function eventGetFollower(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|min:1'
                , 'account_id' => 'required|integer|min:1'
                , 'event_id' => 'required|integer|min:1'
            ]);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $account_id = $request->account_id;
            $event_id = $request->event_id;
            $organization_id = $request->organization_id;
            $response = [];
            $response['zaloFollowerArray'] = DB::select("SELECT z.id, z.display_name, z.gender FROM zalo_follower z LEFT JOIN fc_get_followed_zalo_oa($event_id, $account_id) fc ON z.id = fc.id WHERE fc.id IS NULL AND z.deleted = 0 AND z.actived = 1 AND z.organization_id = $organization_id");
            $response['followedArray'] = DB::select("SELECT id, display_name, gender FROM fc_get_followed_zalo_oa($event_id, $account_id)");
            $response['status'] = 1;
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [
                'status' => 0
                , 'message' => $e->getMessage()
                , 'code' => $e->getCode()
                , 'line' => $e->getLine()
            ];
            return response()->json($response);
        }
    }


    public function updateEventAndFollower(Request $request) {
        DB::beginTransaction();
        try {
            $message = [
                'event_id.required' => 'Mã sự kiện không được để trống',
                'event_id.numeric' => 'Mã sự kiện phải là số',
                'event_id.min' => 'Mã sự kiện nhỏ nhất phải là :min',
                // Mã delete
                'oa_id.required' => 'Mã account_id không được để trống',
                'oa_id.numeric' => 'Mã account_id phải là số',
                'oa_id.min' => 'Mã account_id có giá trị nhỏ nhất là :min',
            ];
            $validator = Validator::make($request->all(), [
                'oa_id' => 'required|integer|min:1'
                , 'event_id' => 'required|integer|min:1'
                , 'follower_array' => 'array'
            ], $message);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $request_user = $request->user();
            $event_id = $request->event_id;
            $oa_id = $request->oa_id;
            $follower_array = $request->follower_array;
            $date = $this->getDateNow();
            $current_event = EventAccount::where('event_id', $event_id)->where('account_id', $oa_id)->first();
            // Nếu đã có event gắn với official account
            if($current_event != null) {
                // Xóa hết các follower cũ đi
                $current_id = $current_event->id;
                DB::table('event_account_follower')->where('event_account_id', $current_id)->delete();
                // Thêm lại các follower
                if(count($follower_array) > 0) {
                    foreach ($follower_array as $value) {
                        $insertedData = [
                            'event_account_id' => $current_id
                            , 'follower_id' => $value['id']
                            , 'created_at' => $date
                            , 'updated_at' => $date
                            , 'created_by' => $request_user->id
                            , 'updated_by' => $request_user->id
                            , 'deleted' => 0
                            , 'actived' => 1
                        ];
                        DB::table('event_account_follower')->insert($insertedData);
                    }
                } else {
                    EventAccount::where('event_id', $event_id)->where('account_id', $oa_id)->delete();
                }
            } else { // Nếu chưa có event thì tạo event_account
                $event_account = new EventAccount;
                $event_account->created_at = $date;
                $event_account->updated_at = $date;
                $event_account->created_by = $request_user->id;
                $event_account->updated_by = $request_user->id;
                $event_account->event_id = $event_id;
                $event_account->account_id = $oa_id;
                $event_account->save();
                // Sau đó gắn với event_account
                if(count($follower_array) > 0) {
                    foreach ($follower_array as $value) {
                        $insertedData = [
                            'event_account_id' => $event_account->id
                            , 'follower_id' => $value['id']
                            , 'created_at' => $date
                            , 'updated_at' => $date
                            , 'created_by' => $request_user->id
                            , 'updated_by' => $request_user->id
                            , 'deleted' => 0
                            , 'actived' => 1
                        ];
                        DB::table('event_account_follower')->insert($insertedData);
                    }
                }
                // Nếu không có người follow thì chỉ đăng kí sự kiện thôi
            }
            DB::commit();
            $response = [];
            $response['status'] = 1;
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

    public function eventAccFollowerDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            $error_messages = [
                // Mã vị trí phải là số
                'eaf_id.required' => 'Mã vị trí không được để trống',
                'eaf_id.numeric' => 'Mã vị trí phải là số',
                'eaf_id.min' => 'Mã vị trí có giá trị nhỏ nhất là :min'
            ];
            // |regex:/^[\pL\s\-]+$/u
            $validator = Validator::make($request->all(), [
                'eaf_id' => 'required|integer|min:1'
            ], $error_messages);
            if($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $eaf_id = $request->eaf_id;
            DB::table('event_account_follower')->where('id', $eaf_id)->delete();
            DB::commit();
            return response()->json(['status' => 1]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 0, 'message' => $e->getMessage()]);
        }
    }
    // function xử lý chức năng người dùng nhập mã đăng ký
    public function zalo_save_user_info(Request $request) {
        DB::beginTransaction();
        try {
            $user_info = $request->user_info;
            $invite_code = $user_info['message_content'];
            $oa_zalo = DB::table('oa_zalo')->where('invite_code', $invite_code)->get();
            if(count($oa_zalo) === 0) {
                throw new Exception('Invite code is wrong', 770);
            }
            $organization_id = $oa_zalo[0]->organization_id;
            $oa_id = $oa_zalo[0]->id;
            $date = Carbon::now()->format('Y-m-d H:i:s');
            $object = DB::table('zalo_follower')->where('user_id', $user_info['userId'])->first();
            $response = [];
            if($object == null) {
                $insertedData = [
                    'user_id' => $user_info['userId']
                    , 'user_id_by_app' => $user_info['userIdByApp']
                    , 'organization_id' => $organization_id
                    , 'oa_id' => $oa_id
                    , 'display_name' => $user_info['displayName']
                    , 'gender' => $user_info['userGender']
                    , 'avatar' => $user_info['avatar']
                    , 'birthday' => date("Y-m-d H:i:s", $user_info['birthDate'])
                    , 'share_info' => $user_info['sharedInfo']
                    , 'msg_signin_id' => $user_info['message_id']
                    // , 'site_id' => 0
                    , 'created_at' => $date
                    , 'updated_at' => $date
                    , 'actived' => 1
                    , 'deleted' => 0
                ];
                DB::table('zalo_follower')->insert($insertedData);
                $response['message'] = 1;
            } else {
                $response['message'] = 0;
            }
            DB::commit();
            return response()->json($response);
        }
        catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
