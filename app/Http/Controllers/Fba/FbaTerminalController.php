<?php

namespace App\Http\Controllers\Fba;

use Illuminate\Http\Request;
use App\Organization;
use App\FbaTerminal;
use App\FbaTerminalResponse;
use Carbon\Carbon;
use DateTime;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class FbaTerminalController extends Controller
{
    // Xử lý báo cáo thiết bị
    public function fba_get_terminals(Request $request)
    {
        $request_user = $request->user();
        $user_id = $request_user->id;
        $organization_id = $request->organization_id;
        $tablets1 = DB::select("exec sp_fba_get_terminals $user_id, $organization_id");
        return response()->json($tablets1);
    }

    public function fba_get_terminals_insert(Request $request)
    {
        DB::beginTransaction();
        $action_result = 0;
        try {
            $data = json_decode($request->data);
            $request_user = $request->user();
            $serial_number = $data->serial_number;
            $tablet = FbaTerminal::where('serial_number', $serial_number)->first();
            if (!$tablet) {
                DB::table('fba_terminals')->insert([
                    'organization_id' => $data->organization_id,
                    'location_id'     => $data->location_id,
                    'serial_number'   => $serial_number,
                ]);
            } else {
                DB::table('fba_terminals')->where('serial_number', $serial_number)->update([
                    'organization_id' => $data->organization_id,
                    'location_id'     => $data->location_id,
                    'created_by'      => $request_user->id,
                ]);
            }
            $action_result = 1;
            DB::commit();
        } catch (\Exception $e) {
            $action_result = 0;
            DB::rollback();
        }
        return response()->json(array('status' => $action_result));
    }

    public function fba_get_terminals_update(Request $request)
    {
        DB::beginTransaction();
        $action_result = 0;
        try {
            $data = json_decode($request->data);
            DB::table("fba_terminals")->where('serial_number', $data->serial_number)
                ->update([
                    'organization_id' => $data->organization_id,
                    'location_id'     => $data->location_id,
                ]);
            $action_result = 1;
            DB::commit();
        } catch (\Exception $e) {
            $action_result = 0;
            DB::rollback();
        }
        return response()->json(array('status' => $action_result));
    }

    public function fba_get_terminals_delete(Request $request)
    {
        DB::beginTransaction();
        $action_result = 0;
        try {
            DB::table("fba_terminals")->where('serial_number', $request->serial_number)
                ->delete();
            $action_result = 1;
            DB::commit();
        } catch (\Exception $e) {
            $action_result = 0;
            DB::rollback();
        }
        return response()->json(array('status' => $action_result));
    }

    // API nhận data từ thiết bị
    public function fba_terminal_get_info(Request $request)
    {
        DB::beginTransaction();
        $action_result = 0;
        try {
            // $now = Carbon::now();
            // $time_now= $now->toDateTimeString();                      // 1975-12-25 14:15:16
            $token = $request->token;
            $token_en =  base64_decode($token);
            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $time_now = date('Y-m-d H:i:s');
            // kiểm tra token giải mã đúng mới thực hiện
            if ($token_en === "acssolution") {
                $net = strtolower($request->net);
                if ($request->has('SN')) {
                    $terminal = FbaTerminal::where('serial_number', $request->SN)->first();
                    if (!$terminal) {
                        $terminal = new FbaTerminal;
                        $terminal->serial_number = $request->SN;
                        $terminal->pin = $request->pin;
                        $terminal->power = $request->power;
                        if ($net === 'gprs' || $net === 'zigbee' || $net === 'lora') {
                            $terminal->network = $request->net;
                        }
                        $terminal->latitude = $request->lo['lat'];
                        $terminal->longitude = $request->lo['lon'];
                        $terminal->organization_id  = $request->organization;
                        $terminal->storage_capacity = $request->storage;
                        $terminal->storage_free = $request->stor_free;
                        $terminal->wave = $request->w;
                        $terminal->app_version = $request->version;
                        $terminal->start_time = $request->t;
                        $terminal->recerviced_time = $time_now;
                        $terminal->save();
                    }
                    if (count($terminal) > 0) {
                        if ($net === 'gprs' || $net === 'zigbee' || $net === 'lora') {
                            $terminal = FbaTerminal::where('serial_number', $request->SN)->update([
                                'pin' => $request->pin,
                                'power' => $request->power,
                                'network' => $request->net,
                                // 'organization_id' => $request->organization,
                                'storage_capacity' => $request->storage,
                                'storage_free' => $request->stor_free,
                                'wave' => $request->w,
                                'app_version' => $request->version,
                                'latitude' => $request->lo['lat'],
                                'longitude' => $request->lo['lon'],
                                'start_time' => $request->t,
                                'recerviced_time' => $time_now
                            ]);
                        } else {
                            $action_result = 0;
                            DB::rollback();
                            return response()->json(array('status' => $action_result));
                        }
                    }
                    $action_result = 1;
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            $action_result = 0;
            DB::rollback();
        }
        return response()->json(array('status' => $action_result));
    }

    // API nhận sự thay đổi nguồn
    public function fba_terminal_change_power(Request $request)
    {
        DB::beginTransaction();
        $action_result = 0;
        try {
            $token = $request->token;
            $token_en =  base64_decode($token);
            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $time_now = date('Y-m-d H:i:s');
            if ($token_en === "acssolution") {
                $net = strtolower($request->net);
                if ($request->has('SN')) {
                    // Theo dõi thời lượng Pin của thiết bị
                    $update_array = ['updated_at' => $time_now, 'created_at' => $time_now, 'serial_number' => $request->SN, 'pin' => $request->pin, 'power' => $request->power, 'start_time' => $request->t, 'recerviced_time' => $time_now];
                    DB::table('fba_terminal_follow_pin')->insert($update_array);
                    // Thay đổi nguồn
                    FbaTerminal::where('serial_number', $request->SN)->update([
                        'power' => $request->power,
                        'pin' => $request->pin,
                        'start_time' => $request->t,
                        'recerviced_time' => $time_now
                    ]);
                    $action_result = 1;
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            $action_result = 0;
            DB::rollback();
        }
        return response()->json(array('status' => $action_result));
    }

    public function fba_terminal_get_data(Request $request)
    {
        DB::beginTransaction();
        $action_result = 0;
        try {
            $token = $request->token;
            $token_en =  base64_decode($token);
            date_default_timezone_set('Asia/Ho_Chi_Minh');
            // kiểm tra token giải mã đúng mới thực hiện
            if ($token_en === "acssolution") {
                $time_now = date('Y-m-d H:i:s');
                $tablet = new FbaTerminalResponse;
                $tablet->serial_number = $request->SN;
                $block = (int)$request->b ? (int)$request->b : 0;
                $tablet->interval =  $block;
                $data = $request->item;
                $end_time  = date($request->t);
                $start_time = $request->t ?  date('Y-m-d H:i:s', strtotime(-$block . ' minute', strtotime($end_time))) : "";
                $very_positive = $data[0]['r'] ? $data[0]['r'] : 0;
                $positive = $data[1]['r'] ? $data[1]['r'] : 0;
                $negative = $data[2]['r'] ? $data[2]['r'] : 0;
                $very_negative =  $data[3]['r'] ?  $data[3]['r'] : 0;
                $tablet->very_positive = $very_positive;
                $tablet->positive = $positive;
                $tablet->negative = $negative;
                $tablet->very_negative = $very_negative;
                $tablet->start_time =  $start_time;
                $tablet->end_time =  $end_time;
                $tablet->recerviced_time = $time_now;
                $tablet->save();
                $action_result = 1;
            }
            DB::commit();
        } catch (\Exception $e) {
            $action_result = 0;
            DB::rollback();
        }
        return response()->json(array('status' => $action_result));
    }

    public function set_configuration(Request $request)
    {
        $token = $request->token;
        $token_en =  base64_decode($token);
        $data =  DB::select('SELECT ft.block FROM  fba_terminals_block ft');
        $block =  $token_en === "acssolution" ? (int)$data[0]->block : "";
        return response()->json(['block' => $block]);
    }

    public function set_token(Request $request)
    {
        $key = $request->key;
        $value =  base64_decode($key);
        $token = $value === "phanhien" ?  base64_encode('acssolution') : "";
        return response()->json(['token' => $token]);
    }

    public function set_time(Request $request)
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $t = date('Y-m-d H:i:s');
        return response()->json(['t' => $t]);
    }
}
