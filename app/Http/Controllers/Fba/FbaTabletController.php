<?php

namespace App\Http\Controllers\Fba;

use App\FbaTablet;
use App\Organization;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
// use ElephantIO\Client;
// use ElephantIO\Engine\SocketIO\Version1X;

// require __DIR__ . '/../../../../vendor/autoload.php';

use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version2X;

require __DIR__ . '/../../../../vendor/autoload.php';

class FbaTabletController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\FbaTablet  $fbaTablet
     * @return \Illuminate\Http\Response
     */
    public function show(FbaTablet $fbaTablet)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\FbaTablet  $fbaTablet
     * @return \Illuminate\Http\Response
     */
    public function edit(FbaTablet $fbaTablet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\FbaTablet  $fbaTablet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FbaTablet $fbaTablet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\FbaTablet  $fbaTablet
     * @return \Illuminate\Http\Response
     */
    public function destroy(FbaTablet $fbaTablet)
    {
        //
    }

    public function update_status(Request $request)
    {
        $action_result = 0;
        $organization_id = 0;
        $location_id = 0;
        try {
            if ($request->has('data')) {
                $data = $request->data;
                $serial_number = $data['serial_number'];
                $tablet = FbaTablet::where('serial_number', $serial_number)->first();
                if (!$tablet) {
                    $tablet = new FbaTablet;
                    $tablet->organization_id =  $organization_id;
                    $tablet->location_id = $location_id;
                    $tablet->serial_number = $serial_number;
                    $tablet->imei = $serial_number;
                    $tablet->actived = 0;
                }
                $tablet->pin = $data['pin'];
                $tablet->network = $data['network'];
                $tablet->storage_capacity = $data['storage_capacity'];
                $tablet->storage_free = $data['storage_free'];
                $tablet->os = $data['os'];
                $tablet->charge = $data['charge'];

                $tablet->question_id_check = $data['question_id_check'];
                $tablet->question_name_check = $data['question_name_check'];
                $tablet->organization_id_check = $data['organization_id_check'];
                $tablet->organization_name_check = $data['organization_name_check'];
                $tablet->location_id_check = $data['location_id_check'];

                $tablet->location_name_check = $data['location_name_check'];

                $tablet->latitude = $data['latitude'];
                $tablet->longitude = $data['longitude'];

                $monitor = 2;
                if ((int) $data['pin'] < 30 ||  (int) $data['charge'] == 0 )
                    $monitor = 1;
                $tablet->monitor =  $monitor;

                if (array_key_exists('app_version', $data))
                    $tablet->app_version = $data['app_version'];

                $tablet->save();

                $action_result = 1;
            }
        } catch (\Exception $e) {
            $action_result = 0;
        }
        return response()->json(array('status' => $action_result));
    }
    public function terminal_update_status(Request $request)
    {
        if ($request->has('serial_number')) {
            return response()->json(array('serial_number' => $request->serial_number, 'status' => 'success'));
        }
        return response()->json(array('serial_number' => 'unknow', 'status' => 'failse'));
    }
    public function fba_tablet_get_info(Request $request)
    {
        if ($request->has('serial_number')) {
            $serial_number = strval($request->serial_number);
            $serial_number1 = "'" . $serial_number . "'";
            $items = DB::select("exec sp_get_tablet_details_by_serial_number $serial_number1");
            if ($items)
                return response()->json($items[0]);
        }
        return response()->json(array(
            "organization_id" => "0",
            "organization_code" => "",
            "organization_shortname" => "",
            "organization_name" => "",
            "organization_description" => "",
            "site_id" => "0",
            "site_name" => "",
            "site_code" => "",
            "site_shortname" => "",
            "site_description" => "",
            "location_name" => "",
            "location_id" => "1",
            "serial_number" =>  $serial_number,
            "imei" =>  $serial_number,
            "data_version" => "1"
        ));
    }
    // Huy 26/12/2018
    // Lấy thiết bị để quản trị theo tổ chức, và thiết bị mới ở bên dưới
    public function fba_get_tablet(Request $request)
    {
        $request_user = $request->user();
        $u_or_id = $request_user->organization_id;
        $user_id = $request_user->id;
        $organization_id = $request->organization_id;
        $actived = $request->actived;
        $tablets1 = DB::select("exec sp_fba_get_tablets $user_id, $organization_id,$actived");
        return response()->json($tablets1);
    }
    // Lấy thiết  bị theo màn hình giám sát
    public function fba_get_tablet_follow(Request $request)
    {
        $request_user = $request->user();
        $user_id = $request_user->id;
        $organization_id = $request->organization_id;
        $actived = $request->actived;
        $tablets = DB::select("exec sp_fba_get_tablets $user_id, $organization_id, $actived");
        return response()->json($tablets);
    }

    // thêm mới thiết bị
    public function insert_tablet(Request $request)
    {
        DB::beginTransaction();
        $action_result = 0;
        try {
            $data = json_decode($request->data);
            $request_user = $request->user();
            $serial_number = $data->serial_number;
            $tablet = FbaTablet::where('serial_number', $serial_number)->first();
            if (!$tablet) {
                $tablets = DB::table('fba_tablets')->insert([
                    'organization_id' => $data->organization_id,
                    'location_id'     => $data->location_id,
                    'tablet_name'     => $data->tablet_name,
                    'created_by'      => $request_user->id,
                    'serial_number'   => $serial_number,
                    'imei'            => $serial_number,
                    'actived'         => 1,
                ]);
            } else {
                $tablets = DB::table('fba_tablets')->where('serial_number', $serial_number)->update([
                    'organization_id' => $data->organization_id,
                    'location_id'     => $data->location_id,
                    'tablet_name'     => $data->tablet_name,
                    'created_by'      => $request_user->id,
                    'actived'         => 1,
                ]);
            }
            $action_result = 1;
            DB::commit();
        } catch (\Exception $e) {
            $action_result = 0;
            DB::rollback();
        }
        return response()->json(['status' => $action_result]);
    }
    // cập nhật thiết bị
    public function update_tablet(Request $request)
    {

        DB::beginTransaction();
        $action_result = 0;
        try {
            $request_user = $request->user();
            $data = json_decode($request->data);
            $id = $data->id;
            $or = $data->organization_id;
            $serial_number =  $data->serial_number;
            // Thay đổi thông tin mobie trực tiếp khi thay đổi câu hỏi
            $tablet = DB::table('fba_tablets')->where('serial_number', $id)->update([
                'organization_id' => $data->organization_id,
                'location_id'     => $data->location_id,
                'tablet_name'     => $data->tablet_name,
                'created_by'      => $request_user->id,
                'serial_number'   => $data->serial_number,
                'imei'            => $data->serial_number,
                'actived'         => 1,
            ]);
            $access_token = $request->token;
            $tocken_type  = 'Bearer';
            $url_socket = env('URL_SOCKET');
            $socketClient =  new Client(new Version2X($url_socket));
            $socketClient->initialize();
            $socketClient->emit('fba_table_reload_data_single', ['serial_number' => $data->serial_number, 'tocken_type' => $tocken_type, 'access_token' => $access_token]); // string array
            $socketClient->close();

            $action_result = 1;
            DB::commit();
        } catch (\Exception $e) {
            $action_result = 0;
            DB::rollback();
        }
        return response()->json(['status' => $action_result]);
    }
    // Cập nhật trạng thái kết nối thiết bị
    public function update_tablet_monitor(Request $request)
    {
        DB::beginTransaction();
        $action_result = 0;
        try {
            $serial_number = $request->serial_number;
            $monitor = $request->monitor;
            $time_now = date('Y-m-d H:i:s');
            $tablet = DB::table('fba_tablets')->where('serial_number', $serial_number)->update([
                'monitor' =>  $monitor, 'recerviced_time' => $time_now
            ]);
            $action_result = 1;
            DB::commit();
        } catch (\Exception $e) {
            $action_result = 0;
            DB::rollback();
        }
        return response()->json(['status' => $action_result]);
    }
    // Xóa thiết bị
    public function delete_tablet(Request $request)
    {
        DB::beginTransaction();
        $action_result = 0;
        try {
            $request_user = $request->user();
            $id = $request->id;
            // $tablet = DB::table('fba_tablets')->where('serial_number',$id)->update(['deleted' => 1]);
            $tablet = DB::table('fba_tablets')->where('serial_number', $id)->delete();
            $action_result = 1;
            DB::commit();
        } catch (\Exception $e) {
            $action_result = 0;
            DB::rollback();
        }
        return response()->json(['status' => $action_result]);
    }
}
