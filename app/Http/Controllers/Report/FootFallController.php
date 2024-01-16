<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DateTime;
use App\Organization;
use App\UserOrganizationLanguage;
use App\Site;
use Excel;
use File;
use Illuminate\Support\Facades\DB;
use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version2X;
use Exception;
use Illuminate\Support\Facades\Validator;

require __DIR__ . '/../../../../vendor/autoload.php';

class FootFallController extends Controller
{
    public function sp_api_get_poc_data_in_out(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'siteCode' => 'required|max:100', 'startDate' => 'required|date', 'endDate' => 'required|date', 'startHour' => 'required|date_format:H:i', 'endHour' => 'required|date_format:H:i'
            ]);
            if ($validator->fails()) {
                $json_error = json_encode($validator->errors()->all());
                throw new Exception($json_error, 770);
            }
            $start_time = Carbon::parse($request->startDate);
            $finish_time = Carbon::parse($request->endDate);
            $result = $start_time->diffInDays($finish_time, false);
            if ($result > 7) {
                throw new Exception("Date compare less than 7 days", 770);
            }
            $response = [];
            $request_user = $request->user();
            $organization_id = $request_user->organization_id;
            $siteCode = '\'' . $request->siteCode . '\'';
            $startDate = '\'' . $request->startDate . '\'';
            $endDate = '\'' . $request->endDate . '\'';
            $startHour = '\'' . $request->startHour . '\'';
            $endHour = '\'' . $request->endHour . '\'';
            $response['dataResponse'] = DB::select("exec sp_api_get_poc_data_in_out_avg_time $organization_id, $siteCode, $startDate, $endDate, $startHour, $endHour");
            $response['status'] = 'Success';
            $response['organization_name'] = DB::table('organizations')->where('id', $organization_id)->get()[0]->organization_name;
            $response['code'] = 1;
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = $e->getCode() == 770 ? $e->getMessage() : 'Fail';
            $response['code'] = $e->getCode();
            $response['dataResponse'] = NULL;
            DB::rollback();
            return response()->json($response);
        }
    }

    //Visits
    public function sp_poc_data_in_out_sum_by_site(Request $request)
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
            $items = DB::select("exec sp_poc_data_in_out_sum_by_site $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date, $end_date, $view_by,  $operation");
            return response()->json($items)->setCallback($request->input('callback'));
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }

    //Customer Daily
    public function sp_report_poc_raw_data_by_day(Request $request)
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
            $items = DB::select("exec sp_report_poc_raw_data_by_day $user_id, $organization_id, $site_id, $start_date,  $end_date, $start_time, $end_time");
            return response()->json($items)->setCallback($request->input('callback'));
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }

    //Customer Monthly
    public function sp_report_poc_raw_data_by_month(Request $request)
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
            $items = DB::select("exec sp_report_poc_raw_data_by_month $user_id, $organization_id, $site_id, $start_date, $end_date, $start_time, $end_time");
            return response()->json($items)->setCallback($request->input('callback'));
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }

    //Customer Yearly
    public function sp_report_poc_raw_data_by_year(Request $request)
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
            $newstr = str_replace("'", '', $end_date);
            $datetimea = new DateTime($newstr);
            $year = $datetimea->format('Y');
            // $year = $request->year;

            $items = DB::select("exec sp_report_poc_raw_data_by_year $user_id, $organization_id, $site_id, $year, $start_time, $end_time");
            return response()->json($items)->setCallback($request->input('callback'));
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }

    // Nghĩa thêm function cho overview
    public function sp_poc_data_in_out_sum_by_site_overview(Request $request)
    {
        try {
            $request_user = $request->user();
            $user_id = $request_user->id;
            $organization_id = $request->organization_id;
            $start_time = $request->start_time;
            $end_time = $request->end_time;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $view_by = $request->view_by;
            $operation = $request->operation;
            $site_id = $request->site_id;
            $items = DB::select("exec sp_general_report_data_in_out_sum_by_store $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date, $end_date, $operation, 1");
            return response()->json(['data' => $items]);
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }

    public function sp_poc_data_in_out_sum_by_site_reporting_store(Request $request)
    {
        try {
            $request_user = $request->user();
            $user_id = $request_user->id;
            $organization_id = $request->organization_id;
            $start_time = $request->start_time;
            $end_time = $request->end_time;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $view_by = $request->view_by;
            $operation = $request->operation;
            $site_id = $request->site_id;
            $select_category = isset($request->select_category) ? $request->select_category : '';
            if ($select_category == 'Group')
                $items = DB::select("exec sp_general_report_heatmap_treemap_coloraxis_sum $user_id, $organization_id, 0, $start_time, $end_time, $start_date, $end_date");
            else
                $items = DB::select("exec sp_general_report_data_in_out_sum_by_store $user_id, $organization_id, 0, $start_time, $end_time, $start_date, $end_date, $operation, 1");
            return response()->json(['data' => $items]);
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }

    public function sp_footfall_heatmap_poc_data_in_out_sum(Request $request)
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

            $index_source = $request->index_source;


            $items = DB::select("exec sp_get_site_by_lication  $user_id, $organization_id, $site_id, 0");
            // return response()->json($items);
            // return;
            $items1 = DB::select("exec sp_footfall_heatmap_poc_data_in_out_sum $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date, $end_date");
            // return response()->json($items1);

            $array1  = [];
            // foreach ($items as $key => $value)
            foreach ($items as $value) {
                $array2  = [];
                foreach ($items1 as $value1) {
                    if ($value->site_name == $value1->site_name) {
                        $val = 0;
                        if ($index_source == "Visits")
                            $val = $value1->num_to_enter;

                        if ($index_source == "Traffic Flow")
                            $val = $value1->traffic;

                        $array3  = [];
                        $array3[$index_source] = $val;

                        $array2[$value1->location_name] =  $array3;
                    }
                }

                $array1[$value->site_name] = $array2;
            }

            return response()->json($array1);
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }

    public function sp_footfall_heatmap_treemap_coloraxis_sum2(Request $request)
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

            $index_source = strtoupper($request->index_source);
            $operation = "SUM";
            $items = DB::select("exec sp_general_report_data_in_out_sum_by_store $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date, $end_date, $operation,0");
            $array1  = [];
            $i = 0;
            foreach ($items as $value) {
                $val = 0;
                if ($index_source === 'PASSERBY')
                    $val = (int) $value->passer_by;
                if ($index_source === 'VISITS')
                    $val = (int) $value->num_to_enter;
                if ($index_source === 'SHOPPERS')
                    $val = (int) $value->shopper_visits;
                if ($index_source === 'TURN IN RATE (%)')
                    $val = (float) $value->turn_in_rate;
                if ($index_source === 'TRAFFIC FLOW')
                    $val = (int) $value->traffic;
                if ($index_source === 'AVG TIME')
                    $val = (float) $value->avg_time;
                if ($index_source === 'KIDS VISITS')
                    $val = (int) $value->kids_visits;
                $array1[$i] = array('name' => $value->site_name, 'value' => $val, 'colorValue' => $i + 1);
                $i++;
            }
            return response()->json($array1);
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }

    public function sp_footfall_heatmap_treemap_coloraxis_sum(Request $request)
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
            $view_by = strtoupper($request->view_by);
            $index_source = strtoupper($request->index_source);
            $language = 1;
            $organization =  Organization::where('id', $organization_id)->get();
            $organizations = json_decode($organization);
            $format_hour = (int) $organizations[0]->time_setting_12h;
            $obj = UserOrganizationLanguage::where([['user_id',  $user_id],['organization_id',  $organization_id]])->get();
            $language = (int) $obj[0]->language_vn;


            $items = DB::select("exec sp_poc_heatmap_sum_by_site $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date, $end_date,$view_by");
            $time_hour = DB::select("select  time_period from fc_get_range_hour_v2(DATEPART(HOUR, $start_time),DATEPART(HOUR, $end_time), $format_hour)");
            if ($view_by == 'DAY')
                $time_day = DB::select("select time_period from fc_get_range_day_v2( $start_date,$end_date,$language)");
            if ($view_by == 'WEEK')
                $time_day = DB::select("select time_period from fc_get_range_week_v2( $start_date,$end_date,$language )");
            if ($view_by == 'DAYOFWEEK')
                $time_day = DB::select("select * from fc_get_day_of_week_lu($language)");
            if ($view_by == 'MONTH')
                $time_day = DB::select("select time_period from fc_get_range_month_v2( $start_date,$end_date,$language )");

            $x_hour = count($time_hour);
            $y_day = count($time_day);
            $rows_items = count($items);
            $array = [];
            $row = -1;
            $column = 0;
            for ($i = 0; $i <  $rows_items; $i++) {
                if ($i % $y_day == 0 && $i != 0)
                    $column++;
                $row++;
                if ($i % $y_day == 0 && $i != 0)
                    $row = 0;

                if ($index_source == 'PASSERBY')
                    $value = [$column, $row, (int) $items[$i]->passer_by];
                if ($index_source == 'KIDS VISITS')
                    $value = [$column, $row, (int) $items[$i]->kids_visits];
                if ($index_source == 'TRAFFIC FLOW')
                    $value = [$column, $row, (int) $items[$i]->traffic];
                if ($index_source == 'TURN IN RATE (%)')
                    $value = [$column, $row, (int) $items[$i]->turn_in_rate];
                if ($index_source == 'VISITORS')
                    $value = [$column, $row, (int) $items[$i]->num_to_enter];
                if ($index_source == 'SHOPPERS')
                    $value = [$column, $row, (int) $items[$i]->shopper_visits];
                array_push($array, $value);
            }
            return response()->json(['time_hour' => $time_hour, 'time_day' => $time_day, 'data' => $array]);
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }

    public function sp_footfall_get_traffic_live(Request $request)
    {
        try {
            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $request_user = $request->user();
            $user_id = $request_user->id;

            $curent_datetime = "'" . date("Y-m-d H:i:s") . "'";
            $yesterday_datetime = "'" . date('Y-m-d H:i:s', strtotime('-1 day', strtotime(date("Y-m-d H:i:s")))) . "'";

            $organization_id = $request->organization_id;
            $site_id = $request->site_id;

            $start_time = $request->start_time;
            // $level = "'Five Minute'"; //$request->level;
            // $level = "'Quarter Hour'";   //  18-06-2019
            $level = "'Half Hour'";
            // $level = "'Hour'";        // tính đúng đắn cao hơn

            $items = DB::select("exec sp_footfall_get_traffic_live $user_id, $organization_id, $site_id, 0, $curent_datetime, $start_time, $level");
            $items1 = DB::select("exec sp_footfall_get_traffic_live $user_id, $organization_id, $site_id, 0, $yesterday_datetime, $start_time, $level");

            $curent =  getdate();

            $ret_val = array('date' => $curent, 'live_data' => $items, 'yesterday_data' => $items1);
            return response()->json($ret_val);
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }


    public function sp_footfall_get_traffic(Request $request)
    {
        try {
            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $request_user = $request->user();
            $user_id = $request_user->id;

            $organization_id = $request->organization_id;
            $site_id = $request->site_id;

            // $start_time = "'08:00'";
            // $end_time = "'22:00'";
            $start_time = $request->start_time;
            $end_time = $request->end_time;

            // $start_date = "'2018-10-01'";
            // $end_date = "'2018-10-10'";
            $start_date = $request->start_date;
            $end_date = $request->end_date;

            // $operator = "'SUM'";
            $operation = $request->operation;
            $level = $request->level;
            $dimension = $request->dimension;

            $items = DB::select("exec sp_footfall_liveview_history $user_id, $organization_id, $site_id, 0, $start_time,  $end_time, $start_date, $end_date, $operation, $level, $dimension");

            $curent =  getdate();
            $ret_val = array('date' => $curent, 'data' => $items);
            return response()->json($ret_val);
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }

    public function sp_footfall_time_comparison(Request $request)
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
            $items = DB::select("exec sp_poc_data_in_out_sum_by_site $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date, $end_date, $view_by, $operation");
            $itemsComapare = DB::select("exec sp_poc_data_in_out_sum_by_site $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date_compare, $end_date_compare, $view_by, $operation");
            return response()->json(array('data' => $items, 'data_compare' => $itemsComapare));
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }

    public function sp_footfall_store_comparison(Request $request)
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
            $items = DB::select("exec sp_poc_data_in_out_sum_by_site $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date, $end_date, $view_by, $operation");

            $organization_id_compare = $request->organization_id_compare;
            $site_id_compare = $request->site_id_compare;

            $itemsComapare = DB::select("exec sp_poc_data_in_out_sum_by_site $user_id, $organization_id_compare, $site_id_compare, $start_time, $end_time, $start_date, $end_date, $view_by, $operation");

            return response()->json(array('data' => $items, 'data_compare' => $itemsComapare));
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }

    public function sp_poc_data_in_out_sum_by_site_and_fba_metrics(Request $request)
    {
        try {
            $request_user = $request->user();
            $user_id = $request_user->id;
            $organization_id = $request->organization_id;
            $site_id = $request->site_id;
            $question_id = (int) $request->question_id;
            $start_time = $request->start_time;
            $end_time = $request->end_time;
            $start_hour = $request->start_hour;
            $end_hour = $request->end_hour;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $view_by = $request->view_by;
            $operation = $request->operation;
            $items = DB::select("exec sp_general_report_data_in_out_by_site $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date, $end_date, $view_by,  $operation");
            $metrics  = DB::select("exec sp_fba_report_metrics_analytics $user_id, $organization_id, $site_id, $question_id, $start_hour, $end_hour, $start_date, $end_date, $view_by");
            return response()->json(['items' => $items, 'metrics' => $metrics])->setCallback($request->input('callback'));
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }

    public function sp_footfall_heatmap_treemap_coloraxis_sum_metrics_boston(Request $request)
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
            $indexOptionSelected1 = strtoupper($request->indexOptionSelected1);
            $indexOptionSelected2 = strtoupper($request->indexOptionSelected2);
            $operation = "SUM";
            $data = DB::select("exec sp_general_report_data_in_out_sum_by_store $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date, $end_date, $operation,0");
            // $parent = DB::select("exec sp_general_report_data_in_out_sum_by_store $user_id, $organization_id,0, $start_time, $end_time, $start_date, $end_date, $operation, 0");
            $count = count($data);
            $array  = [];
            $i = 0;
            $avg_ox = 0;
            $avg_oy = 0;
            $oy = 0;
            $ox = 0;

            foreach ($data as $value) {
                $this->get_avg_oxy($indexOptionSelected2, $avg_ox,  $count, $value);
                $this->get_avg_oxy($indexOptionSelected1, $avg_oy,  $count, $value);
            }
            foreach ($data as $value) {
                $this->get_oxy($indexOptionSelected1, $oy, $value);
                $this->get_oxy($indexOptionSelected2, $ox, $value);
                $array[$i] = array('x' => $ox, 'y' => $oy, 'name' => $value->site_name, 'country' => $value->site_name, 'color' => '#e5853b');
                $i++;
            }
            return response()->json(['chart' => $array, 'avg_ox' => $avg_ox, 'avg_oy' => $avg_oy]);  //, 'color' => $color
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }

    public function sp_poc_data_in_out_sum_by_site_import_data(Request $request)
    {
        try {
            $action_result = -1;
            $request_user = $request->user();
            $user_id = $request_user->id;
            $organization_id = $request->organization_id;
            $site_id = $request->site_id;
            $counter = 0;
            $counter_fail = 0;
            if ($request->hasFile('file')) {
                $extension = File::extension($request->file->getClientOriginalName());
                $extension = strtolower($extension);
                if ($extension == "xlsx" || $extension == "xls" || $extension == "csv") {
                    $path = $request->file->getRealPath();
                    $data = Excel::selectSheetsByIndex(0)->load($path, function ($reader) {
                        // $reader->formatDates(true, 'Y-m-d H:i');
                    })->get()->toArray();
                    // $data = Excel::selectSheetsByIndex(0)->load($path)->get()->toArray();
                    if (count($data) > 0) {
                        foreach ($data as $key => $value) {
                            // if (isset($value['location_code']) && array_key_exists('location_code', $value) && array_key_exists('start_time', $value) && array_key_exists('num_to_enter', $value) && array_key_exists('num_to_exit', $value) && array_key_exists('avg_time', $value) && array_key_exists('passer_by', $value)  && array_key_exists('staff_traffic', $value)) {
                            $location_code =  $value['location_code'] ?  trim($value['location_code']) : "";
                            $start_time = "'" . $value['start_time'] . "'";
                            $num_to_enter = $value['num_to_enter'] ? $value['num_to_enter'] : 0;
                            $num_to_exit =  $value['num_to_exit'] ?  $value['num_to_exit'] : 0;
                            $avg_time =  $value['avg_time'] ?  $value['avg_time']  : 0;
                            $passer_by =  $value['passer_by'] ?  $value['passer_by']  : 0;
                            $staff_traffic =  $value['staff_traffic'] ?  $value['staff_traffic']  : 0;
                            $status = DB::select("exec sp_general_report_import_data   $user_id, $organization_id, $site_id, '$location_code', $start_time, $num_to_enter, $num_to_exit, $avg_time, $passer_by, $staff_traffic");
                            if ($status[0]->result == 0) {
                                $action_result = 0;
                            } else if ($status[0]->result == 1) {
                                $counter++;
                                $action_result = 1;
                            } else {
                                $action_result = -1;
                            }
                        }
                        // }
                        // $counter_fail  = $total_counter - $counter;
                        return response()->json(array('status' => $action_result, 'counter' => $counter, 'counter_fail' => $counter_fail));
                    }
                    $action_result = 0;
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

    public function get_oxy($indexOptionSelected, &$o, &$value)
    {
        if ($indexOptionSelected === 'PASSERBY')
            $o = (int) $value->passer_by;
        if ($indexOptionSelected === 'VISITORS')
            $o = (int) $value->num_to_enter;
        if ($indexOptionSelected === 'SHOPPERS')
            $o = (int) $value->shopper_visits;
        if ($indexOptionSelected === 'TURN IN RATE (%)')
            $o = (float) $value->turn_in_rate;
        if ($indexOptionSelected === 'TRAFFIC FLOW')
            $o = (int) $value->traffic;
        if ($indexOptionSelected === 'AVG TIME')
            $o = (float) $value->avg_time;
        if ($indexOptionSelected === 'KIDS VISITORS')
            $o = (int) $value->kids_visits;
        if ($indexOptionSelected === 'CONVERSION RATE (%)')
            $o = (float) $value->conversion;

        if ($indexOptionSelected === 'AVG ITEMS')
            $o = (float) $value->avg_item;
        if ($indexOptionSelected === 'SALES YIELD')
            $o = (int) $value->sales_yield;
        if ($indexOptionSelected === 'MEMBER VISITORS (%)')
            $o = (float) $value->loyal_visits;
        if ($indexOptionSelected === 'MEMBER TRANSACTIONS (%)')
            $o = (float) $value->loyal_transactions;
        if ($indexOptionSelected === 'MEMBER CONVERSION RATE (%)')
            $o = (float) $value->loyal_conversion;
        if ($indexOptionSelected === 'MISSED SALES OPPORTUNITY (%)')
            $o = (int) $value->missed_sales;
        if ($indexOptionSelected === 'CX INDEX (%)')
            $o = (float) $value->cx_index;
        if ($indexOptionSelected === 'NPS INDEX (%)')
            $o = (float) $value->nps_index;
        if ($indexOptionSelected === 'TRANSACTIONS')
            $o = (int) $value->transactions;
        if ($indexOptionSelected === 'SALES')
            $o = (int) $value->sales;
        if ($indexOptionSelected === 'ATV')
            $o = (int) $value->atv;
        if ($indexOptionSelected === 'SALES HOURS')
            $o = (float) $value->sales_hour;
        if ($indexOptionSelected === 'SHOPPERS ON SALES HOUR')
            $o = (float) $value->shopper_on_s_h;
        if ($indexOptionSelected === 'SALES ON SALES HOUR')
            $o = (float) $value->sales_on_s_h;
        if ($indexOptionSelected === 'MISSED MEMBER RATE')
            $o = $value->loyal_conversion > 0 ? (float) (100 - $value->loyal_conversion) : 0;
    }

    function  get_avg_oxy(&$indexOptionSelected, &$oo, &$count, &$value1)
    {
        if ($indexOptionSelected === 'PASSERBY') {
            $oo  += (int) ($value1->passer_by / $count);
        } else if ($indexOptionSelected === 'VISITORS') {
            $oo  += (int) ($value1->num_to_enter / $count);
        } else if ($indexOptionSelected === 'SHOPPERS') {
            $oo += (int) ($value1->shopper_visits / $count);
        } else if ($indexOptionSelected === 'TURN IN RATE') {
            $oo += number_format((((float) $value1->turn_in_rate / $count)), 2, '.', '');
        } else if ($indexOptionSelected === 'TRAFFIC FLOW') {
            $oo += (int) ($value1->traffic / $count);
        } else if ($indexOptionSelected === 'AVG TIME') {
            $oo += number_format((((float) $value1->avg_time / $count)), 2, '.', '');
        } else if ($indexOptionSelected === 'KIDS VISITORS') {
            $oo += (int) ($value1->kids_visits / $count);
        } else if ($indexOptionSelected === 'Avg ITEMS') {
            $oo += number_format((((float) $value1->avg_item / $count)), 2, '.', '');
        } else if ($indexOptionSelected === 'SALES') {
            $oo += number_format((((float) $value1->sales / $count)), 0, '.', '');
        } else if ($indexOptionSelected === 'SALES YIELD') {
            $oo += number_format((((float) $value1->sales_yield / $count)), 0, '.', '');
        } else if ($indexOptionSelected === 'MEMBER VISITORS') {
            $oo += number_format((((float) $value1->loyal_visits / $count)), 2, '.', '');
        } else if ($indexOptionSelected === 'MEMBER TRANSACTIONS') {
            $oo += number_format((((float) $value1->loyal_transactions / $count)), 2, '.', '');
        } else if ($indexOptionSelected === 'MEMBER CONVERSION RATE') {
            $oo += number_format((((float) $value1->loyal_conversion / $count)), 2, '.', '');
        } else if ($indexOptionSelected === 'MISSED SALES OPPORTUNITY') {
            $oo += number_format((((float) $value1->missed_sales / $count)), 2, '.', '');
        } else if ($indexOptionSelected === 'CX INDEX') {
            $oo += number_format((((float) $value1->cx_index / $count)), 2, '.', '');
        } else if ($indexOptionSelected === 'NPS INDEX') {
            $oo += number_format((((float) $value1->nps_index / $count)), 2, '.', '');
        } else if ($indexOptionSelected === 'TRANSACTIONS') {
            $oo += (int) ($value1->transactions / $count);
        } else if ($indexOptionSelected === 'CONVERSION RATE') {
            $oo += number_format((((float) $value1->conversion / $count)), 2, '.', '');
        } else if ($indexOptionSelected === 'ATV') {
            $oo += number_format((((float) $value1->atv / $count)), 0, '.', '');
        } else if ($indexOptionSelected === 'SALES HOURS') {
            $oo += number_format((((float) $value1->sales_hour / $count)), 2, '.', '');
        } else if ($indexOptionSelected === 'SHOPPERS ON SALES HOUR') {
            $oo += number_format((((float) $value1->shopper_on_s_h / $count)), 2, '.', '');
        } else if ($indexOptionSelected === 'SALES ON SALES HOUR') {
            $oo += number_format((((float) $value1->sales_on_s_h / $count)), 2, '.', '');
        } else if ($indexOptionSelected === 'MISSED MEMBER RATE') {
            $oo += $value1->loyal_conversion > 0 ? number_format((((float) (100 - $value1->loyal_conversion) / $count)), 2, '.', '') : 0;
        }
    }
}
