<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\Message;
use App\Http\Controllers\ErrorHandleController as Error;
class MessageController extends Controller
{
    public function get_message_with_user(Request $request) {
        try {
            $request_user = $request->user();
            $user_id = (int)$request_user->id;
            $org_id = (int)$request_user->organization_id;
            $data = DB::table('messages')->where('user_id', $user_id)->where('organization_id', $org_id)->orderBy('actived', 'asc')->take(15)->get();
            return response()->json(['message_array' => $data]);
        }
        catch (\Exception $e) {
            return response()->json(['message' => 0]);
        }
    }

    public function update_status_message(Request $request) {
        DB::beginTransaction();
        try {
            $id = $request->message_id;
            $object = Message::find($id);
            $object->actived = 1;
            $object->save();
            DB::commit();
            return response()->json(['message' => 1]);
        }
        catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e]);
        }
    }

    public function update_status_message_delete(Request $request) {
        DB::beginTransaction();
        try {
            $id = $request->message_id;
            $object = Message::find($id);
            $object->delete();
            DB::commit();
            return response()->json(['message' => 1]);
        }
        catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e]);
        }
    }

    public function update_status_message_truncates(Request $request) {
        DB::beginTransaction();
        try {
            $request_user = $request->user();
            $user_id = (int)$request_user->id;
            DB::table('messages')->where('user_id', $user_id)->delete();
            DB::commit();
            return response()->json(['message' => 1]);
        }
        catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e]);
        }
    }

    public function fba_tablet_connect_notification(Request $request) {
        DB::beginTransaction();
        try {
            $serial_number = $request->id;
            $mesage_object = json_decode($request->message_object);
            $data = DB::select("exec sp_get_user_with_site_in_role $serial_number");
            $terminal_array = DB::table('fba_tablets')->join('locations', 'fba_tablets.location_id', '=', 'locations.id')->join('sites', 'locations.site_id', '=', 'sites.id')->join('organizations', 'fba_tablets.organization_id', '=', 'organizations.id')->select('fba_tablets.tablet_name', 'sites.id', 'sites.site_name', 'organizations.organization_name')->where('fba_tablets.imei', $serial_number)->get();
            $response = [];
            $message_array = [];
            if (count($terminal_array) > 0) {
                $response['tablet_info'] = $terminal_array[0];
                $tablet_name = $terminal_array[0]->tablet_name;
                foreach ($data as $key => $value) {
                    $object = new Message();
                    $object->user_id = $value->user_id;
                    $object->message_content = "Thiết bị <b>$tablet_name</b> vừa kết nối trong hệ thống Trải Nghiệm khách hàng";
                    $object->message_type = $mesage_object->id;
                    $object->icon = $mesage_object->icon;
                    $object->actived = 0;
                    $object->organization_id = $value->organization_id;
                    if(isset($mesage_object->link_access)) {
                        $object->link_access = $mesage_object->link_access;
                    }
                    $object->save();
                    $message_array[$key] = $object;
                }
            }
            $response['message_object'] = $message_array;
            DB::commit();
            return response()->json($response);
        }
        catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => Error::get_error_message($e)]);
        }
    }

    public function fba_tablet_disconnect_notification(Request $request) {
        DB::beginTransaction();
        try {
            $serial_number = $request->id;
            $mesage_object = json_decode($request->message_object);
            $data = DB::select("exec sp_get_user_with_site_in_role $serial_number");
            $terminal_name = DB::table('fba_tablets')->where('imei', $request->id)->select('tablet_name')->get();
            $user_array = [];
            $message_array = [];
            $tablet_name = $terminal_name[0]->tablet_name;
            foreach ($data as $key => $value) {
                $object = new Message();
                $object->user_id = $value->user_id;
                $object->message_content = "Thiết bị <b>$tablet_name</b> vừa ngắt kết nối trong hệ thống Trải Nghiệm khách hàng";
                $object->message_type = $mesage_object->id;
                $object->icon = $mesage_object->icon;
                $object->actived = 0;
                $object->organization_id = $value->organization_id;
                if(isset($mesage_object->link_access)) {
                    $object->link_access = $mesage_object->link_access;
                }
                $object->save();
                $message_array[$key] = $object;
            }
            DB::commit();
            return response()->json(['message_object' => $message_array]);
        }
        catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => Error::get_error_message($e)]);
        }
    }
}
