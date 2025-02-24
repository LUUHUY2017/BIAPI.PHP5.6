<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;
use Excel;
use File;

class PerformanceController extends Controller
{
    //Visits
    public function sp_footfall_performance_data_by_site(Request $request)
    {
        try {
            $request_user = $request->user();
            $user_id = $request_user->id;
            $organization_id = $request->organization_id;
            $site_id = $request->site_id;
            $start_time = $request->start_time;
            $end_time = $request->end_time;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $view_by = $request->view_by;
            $operation = $request->operation;
            $items = DB::select("exec sp_general_report_data_in_out_by_site $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date, $end_date, $view_by,  $operation");
            return response()->json($items);
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }

    public function sp_footfall_performance_time_comparison(Request $request)
    {
        try {
            $request_user = $request->user();
            $user_id = $request_user->id;
            $organization_id = $request->organization_id;
            $site_id = $request->site_id;

            $start_time = $request->start_time;
            $end_time = $request->end_time;

            $start_date = $request->start_date;
            $end_date = $request->end_date;

            $view_by = $request->view_by;

            $start_date_compare = $request->start_date_compare;
            $end_date_compare = $request->end_date_compare;
            $operation = $request->operator;
            $items = DB::select("exec sp_general_report_data_in_out_by_site $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date, $end_date, $view_by, $operation");
            $itemsComapare = DB::select("exec sp_general_report_data_in_out_by_site $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date_compare, $end_date_compare, $view_by, $operation");
            return response()->json(array('data' => $items, 'data_compare' => $itemsComapare));
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }

    public function sp_footfall_performance_store_comparison(Request $request)
    {
        try {
            $request_user = $request->user();
            $user_id = $request_user->id;
            $organization_id = $request->organization_id;
            $site_id = $request->site_id;
            $start_time = $request->start_time;
            $end_time = $request->end_time;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $view_by = $request->view_by;
            // $operation = $request->operation;
            $operation = $request->operator;
            $items = DB::select("exec sp_general_report_data_in_out_by_site $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date, $end_date, $view_by, $operation");

            $organization_id_compare = $request->organization_id_compare;
            $site_id_compare = $request->site_id_compare;

            $itemsComapare = DB::select("exec sp_general_report_data_in_out_by_site $user_id, $organization_id_compare, $site_id_compare, $start_time, $end_time, $start_date, $end_date, $view_by, $operation");

            return response()->json(array('data' => $items, 'data_compare' => $itemsComapare));
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }

    public function  sp_footfall_performance_download_file_import(Request $request)
    {
        try {
            $site_code = $request->site_code;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $indexOption = $request->indexOption;
            $start =  Carbon::parse($start_date . '00:00');
            $end =  Carbon::parse($end_date . '23:00');
            $column0 = '';
            $column1 = '';
            $column2 = '';
            $column3 = '';
            $column4 = '';
            $column5 = '';
            $column6 = '';
            $column7 = '';
            $column8 = '';
            $column9 = '';
            $column10 = '';
            $column11 = '';
            $column12 = '';
            $column13 = '';
            foreach ($indexOption as $key => $item) {
                ${'column' . $key} = $item;
            }
            while ($start <= $end) {
                $items1[] = array(
                    'Site_code'    => $site_code,
                    'Start_time'      => $start->format('Y/m/d h:i A'),
                    $column0      =>  null,
                    $column1      =>  null,
                    $column2      =>  null,
                    $column3      =>  null,
                    $column4      =>  null,
                    $column5      =>   null,
                    $column6      =>   null,
                    $column7      =>   null,
                    $column8      =>   null,
                    $column9      =>   null,
                    $column10      =>   null,
                    $column11      =>   null,
                    $column12      =>   null,
                    $column13      =>   null,
                );
                $start =  $start->addHour();
            }
            $name = Carbon::today()->format('d_m_Y') . 'v' . rand(1, 1000);
            Excel::create('FILE_IMPORT_DATA_' . $name, function ($excel)  use ($items1) {
                $title1 = 'Dữ liệu đầu vào';
                $this->get_sheet_second($title1, $excel, $items1);
            })->store('xlsx', public_path('exports'));
            $file_name = 'FILE_IMPORT_DATA_' . $name . '.xlsx';
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
        return response()->json($file_name);
    }

    public function  sp_footfall_performance_insert_data(Request $request)
    {
        $action_result = -1;
        $request_user = $request->user();
        $user_id = $request_user->id;
        $organization_id = $request->organization_id;
        $data = json_decode($request->data);

       //  $jsonString = json_encode($data);
       //  $logFile = 'D:\ACS\TaiLieu\DET\logfile.log';
       //  error_log($jsonString, 3, $logFile);



        $num_to_enter_boo = isset($data->num_to_enter) ? (int) $data->num_to_enter : 0;
        $num_to_exit_boo = isset($data->num_to_exit) ? (int) $data->num_to_exit : 0;
        $avg_time_boo = isset($data->avg_time) ? (int) $data->avg_time : 0;
        $passer_by_boo = isset($data->passer_by) ? (int) $data->passer_by : 0;
        $staff_boo = isset($data->staff) ? (int) $data->staff : 0;
        $staff_traffic_boo = isset($data->staff_traffic) ? (int) $data->staff_traffic : 0;
        $kids_visits_boo = isset($data->kids_visits) ? (int) $data->kids_visits : 0;
        $loyal_visits_boo = isset($data->loyal_visits) ? (int) $data->loyal_visits : 0;
        $loyal_purchased_boo = isset($data->loyal_purchased) ? (int) $data->loyal_purchased : 0;
        $transactions_boo = isset($data->transactions) ? (int) $data->transactions : 0;
        $sales_boo = isset($data->sales) ? (int) $data->sales : 0;
        $items_boo = isset($data->items) ? (int) $data->items : 0;

        $shoppers_boo = isset($data->shoppers) ? (int) $data->shoppers : 0;
        $groups_boo = isset($data->groups) ? (int) $data->groups : 0;
        $shippers_boo = isset($data->shippers) ? (int) $data->shippers : 0;
        $males_boo = isset($data->males) ? (int) $data->males : 0;
        $females_boo = isset($data->females) ? (int) $data->females : 0;
        $childs_boo = isset($data->childs) ? (int) $data->childs : 0;
        $adults_boo = isset($data->adults) ? (int) $data->adults : 0;
        $ages_boo = isset($data->ages) ? (int) $data->ages : 0;


        $counter = 0;
        $counter_fail = 0;
        try {
            if ($request->hasFile('file')) {
                $extension = File::extension($request->file->getClientOriginalName());
                $extension = strtolower($extension);

                if ($extension == "xlsx" || $extension == "xls" || $extension == "csv") {
                    $path = $request->file->getRealPath();
                    $datas = Excel::selectSheetsByIndex(0)->load($path, function ($reader) {
                        $reader->formatDates(true, 'Y-m-d h:i:s');
                        // $reader->ignoreEmpty();
                    })->get()->toArray();
                    if (count($datas) > 0) {
                        foreach ($datas as $key => $value) {
                            if (array_key_exists('site_code', $value) && isset($value['site_code'])  &&  isset($value['start_time'])) {
                                $site_code =  $value['site_code'] ?  trim($value['site_code']) : "";
                                $start_time = "'" . $value['start_time'] . "'";

                                $num_to_enter = array_key_exists('enter', $value) && is_numeric($value['enter']) ? $value['enter'] : 0;
                                $num_to_exit = array_key_exists('exits', $value) && is_numeric($value['exits']) ?  $value['exits'] : 0;
                                $avg_time = array_key_exists('time_spent', $value) && is_numeric($value['time_spent']) ? $value['time_spent']  : 0;
                                $passer_by = array_key_exists('passer_by', $value) && is_numeric($value['passer_by']) ? $value['passer_by'] : 0;
                                $staff_traffic = array_key_exists('staff_traffic', $value) && is_numeric($value['staff_traffic']) ? $value['staff_traffic'] : 0;
                                $staff = array_key_exists('staff', $value) && is_numeric($value['staff']) ? $value['staff'] : 0;
                                $transactions = array_key_exists('transactions', $value) && is_numeric($value['transactions']) ? $value['transactions'] : 0;
                                $sales = array_key_exists('sales', $value) && is_numeric($value['sales']) ? $value['sales'] : 0;
                                $items =  array_key_exists('items', $value) && is_numeric($value['items']) ?  $value['items'] : 0;
                                $kids_visits = array_key_exists('kids_visits', $value) && is_numeric($value['kids_visits']) ? $value['kids_visits']  : 0;
                                $loyal_visits = array_key_exists('loyal_visits', $value) && is_numeric($value['loyal_visits']) ? $value['loyal_visits'] : 0;
                                $loyal_purchased = array_key_exists('loyal_purchased', $value) && is_numeric($value['loyal_purchased']) ? $value['loyal_purchased'] : 0;

                                 $shoppers = array_key_exists('shoppers', $value) && is_numeric($value['shoppers']) ? $value['shoppers'] : 0;
                                 $groups = array_key_exists('groups', $value) && is_numeric($value['groups']) ? $value['groups'] : 0;
                                 $shippers = array_key_exists('shippers', $value) && is_numeric($value['shippers']) ? $value['shippers'] : 0;
                                 $males = array_key_exists('males', $value) && is_numeric($value['males']) ? $value['males'] : 0;
                                 $females = array_key_exists('females', $value) && is_numeric($value['females']) ? $value['females'] : 0;
                                 $adults = array_key_exists('adults', $value) && is_numeric($value['adults']) ? $value['adults'] : 0;
                                 $childs = array_key_exists('childs', $value) && is_numeric($value['childs']) ? $value['childs'] : 0;
                                 $ages = array_key_exists('ages', $value) && is_numeric($value['ages']) ? $value['ages'] : 0;

                                $status = DB::select("
                                        exec sp_general_report_import_data 
                                            $num_to_enter_boo, 
                                            $num_to_exit_boo, 
                                            $avg_time_boo, 
                                            $passer_by_boo, 
                                            $staff_traffic_boo, 
                                            $staff_boo, 
                                            $transactions_boo, 
                                            $sales_boo, 
                                            $items_boo, 
                                            $kids_visits_boo, 
                                            $loyal_visits_boo, 
                                            $loyal_purchased_boo,

                                            $shoppers_boo, 
                                            $groups_boo, 
                                            $shippers_boo, 
                                            $males_boo, 
                                            $females_boo, 
                                            $childs_boo, 
                                            $adults_boo, 
                                            $ages_boo, 

                                            $user_id, 
                                            $organization_id, 
                                            '$site_code', 
                                            $start_time, 
                                            $num_to_enter, 
                                            $num_to_exit, 
                                            $avg_time, 
                                            $passer_by, 
                                            $staff_traffic, 
                                            $staff, 
                                            $transactions, 
                                            $sales, 
                                            $items, 
                                            $kids_visits, 
                                            $loyal_visits, 
                                            $loyal_purchased,

                                            $shoppers,
                                            $groups,
                                            $shippers,
                                            $males,
                                            $females,
                                            $childs,
                                            $adults,
                                            $ages
                                    ");

                                if ($status[0]->result == 0) {
                                    $action_result = 0;
                                } else {
                                    $counter++;
                                    $action_result = 1;
                                }
                            }
                        }
                        return response()->json(array('status' => $action_result, 'counter' => $counter, 'counter_fail' => $counter_fail));
                    }
                }
            }
            return response()->json(array('status' => $action_result));
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }

    public function get_sheet_second(&$title, &$excel, &$items)
    {
        $excel->sheet($title, function ($sheet) use ($items) {
            $sheet->getStyle('A0')->getAlignment()->applyFromArray(array('horizontal' => 'center'));
            $sheet->setWidth(['A' => 18, 'B' => 18, 'C' => 13, 'D' => 13, 'E' => 13, 'F' => 13, 'G' => 13, 'H' => 13, 'I' => 13, 'J' => 13, 'K' => 13, 'L' => 13, 'M' => 13, 'N' => 13,]);
            $sheet->setStyle(array('font' => array('name' => 'Times New Roman', 'size' =>  11)));
            // $sheet->setHeight(array(7 =>  27));
            $sheet->setOrientation('landscape');
            $sheet->fromArray($items, NULL, 'A1', true, true);
        });
    }
}
