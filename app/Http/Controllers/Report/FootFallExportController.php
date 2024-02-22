<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DateTime;
use App\Organization;
use App\Site;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class FootFallExportController extends Controller
{

    // Xuất excel cho visits, và metrics comparison
    public function sp_poc_data_in_out_sum_by_site_export_excel(Request $request)
    {
        try {
            $request_user = $request->user();
            $user_id = $request_user != null ? $request_user->id : null;
            if (isset($request->user_id)) {
                $user_id = $request->user_id;
            }
            $organization_id = $request->organization_id;
            $site_id = $request->site_id;
            $start_time = $request->start_time;
            $end_time = $request->end_time;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $view_by = $request->view_by;
            $operation = $request->operation;
            // // 'sum'
            $export = isset($request->export) ? $request->export : ''; // 'xuhuong', 'sum'
            $indexOption  = [];
            if (isset($request->traffic_index)) {
                $traffic_index =  $request->traffic_index;
                foreach ($traffic_index as $item) {
                    array_push($indexOption, $item['value']);
                }
            }

            $items = DB::select("exec sp_poc_data_in_out_sum_by_site $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date, $end_date, $view_by,  $operation");
            $index_not_list = DB::select("select * from  fc_get_index_module_not_org( $organization_id)");

            foreach ($index_not_list as $item) {
                $index_not[]   = $item->index_name;
            }
            // $index_not  =  ["Kids Visits", "PasserBy", "Traffic Flow"];
            $row = count($items);
            $organization =  Organization::find($organization_id);
            $org_name =  $organization->organization_name;
            if ($site_id  != 0) {
                $site = Site::find($site_id);
                $org_name  = $site->site_name;
            }
            // Lấy tổng đánh giá để tính phần trăm
            $passer_by = 0;
            $total_num_to_enter = 0;
            $total_num_to_exit = 0;
            $shopper_visits = 0;
            $kids_visits = 0;
            $turn_in_rate = 0;
            $total_traffic = 0;
            $total_avg_time = 0;
            $i = 0;
            foreach ($items as $item) {
                $passer_by  += (int) $item->passer_by;
                $total_num_to_enter  += (int) $item->num_to_enter;
                $total_num_to_exit  += (int) $item->num_to_exit;
                $kids_visits  += (int) $item->kids_visits;
                $shopper_visits  += (int) $item->shopper_visits;
                $turn_in_rate    += (float) $item->turn_in_rate;
                $total_traffic       += (int) $item->traffic;
                $total_avg_time      += (float) $item->avg_time;
                if (intval($item->avg_time) > 0)
                    $i++;
            }
            $total_avg_time = ($i) > 0 ? (float) number_format(($total_avg_time / ($i)), 2, '.', '') : 0;
            $turn_in_rate = ($row) > 0 ? (float) number_format(($turn_in_rate / ($row)), 2, '.', '') : 0;
            if ($export === 'xuhuong') {
                $passer_by = ($row) > 0 ? (int) number_format(($passer_by / ($row)), 2, '.', '') : 0;
                $total_num_to_enter = ($row) > 0 ? (int) number_format(($total_num_to_enter / ($row)), 2, '.', '') : 0;
                $total_num_to_exit = ($row) > 0 ? (int) number_format(($total_num_to_exit / ($row)), 2, '.', '') : 0;
                $kids_visits = ($row) > 0 ? (int) number_format(($kids_visits / ($row)), 2, '.', '') : 0;
                $total_traffic = ($row) > 0 ? (int) number_format(($total_traffic / ($row)), 2, '.', '') : 0;
                $shopper_visits = ($row) > 0 ? (int) number_format(($shopper_visits / ($row)), 2, '.', '') : 0;
            }

            // Dòng tổng excel
            $items2[] = $items1[] = array(
                'Date/Time'           =>  '',
                'PasserBy'            => $passer_by,
                'Visitors'            => $total_num_to_enter,
                'Exits'               => $total_num_to_exit,
                'Shoppers'            => $shopper_visits,
                'Turn in rate (%)'     => $turn_in_rate . ' %',
                'Traffic Flow'        => $total_traffic,
                'Kids Visits'         => $kids_visits,
                'Avg Time'      => $this->changeSecondsToformatTime($total_avg_time * 60),
            );
            foreach ($items as $item) {
                $items1[] = array(
                    'Date/Time'           => $item->time_period,
                    'PasserBy'            => (int) $item->passer_by,
                    'Visitors'              => (int) $item->num_to_enter,
                    'Exits'              => (int) $item->num_to_exit,
                    'Shoppers'      => (int) $item->shopper_visits,
                    'Turn in rate (%)'     => (float) $item->turn_in_rate . ' %',
                    'Traffic Flow'        => (int) $item->traffic,
                    'Kids Visits'         => (int) $item->kids_visits,
                    'Avg Time'      => $this->changeSecondsToformatTime((float) $item->avg_time * 60),
                );
                $num_to_enter_per = ($item->num_to_enter) > 0 ? (float) number_format((($item->num_to_enter / ($total_num_to_enter)) * 100), 2, '.', '') : 0;
                $num_to_exit_per = ($item->num_to_exit) > 0 ? (float) number_format((($item->num_to_exit / ($total_num_to_exit)) * 100), 2, '.', '') : 0;
                $kids_visits_per = ($item->kids_visits) > 0 ? (float) number_format((($item->kids_visits / ($kids_visits)) * 100), 2, '.', '') : 0;
                $shopper_visits_per = ($item->shopper_visits) > 0 ? (float) number_format((($item->shopper_visits / ($shopper_visits)) * 100), 2, '.', '') : 0;
                $passer_by_per = ($item->passer_by) > 0 ? (float) number_format((($item->passer_by / ($passer_by)) * 100), 2, '.', '') : 0;
                $traffic_per     = ($item->traffic) > 0 ? (float) number_format((($item->traffic / ($total_traffic)) * 100), 2, '.', '') : 0;
                $items2[] = array(
                    'Date/Time'           => $item->time_period,
                    'PasserBy'            => (float) $passer_by_per . ' %',
                    'Visitors'              => (float) $num_to_enter_per . ' %',
                    'Exits'              => (float) $num_to_exit_per . ' %',
                    'Shoppers'      => (float) $shopper_visits_per . ' %',
                    'Turn in rate (%)'    => (float) $item->turn_in_rate . ' %',
                    'Traffic Flow'        => (float) $traffic_per . ' %',
                    'Kids Visits'         => (float) $kids_visits_per . ' %',
                    'Avg Time'      => $this->changeSecondsToformatTime((float) $item->avg_time * 60),
                );
            }

            $value_header =  $this->get_value_header_poc_in_out($start_date, $end_date, $start_time, $end_time, $org_name);
            $name = Carbon::today()->format('d_m_Y') . 'v' . rand(1, 1000);

            foreach ($items1 as $key => $value) {
                foreach ($index_not as $value2) {
                    unset($items1[$key][$value2]);
                    unset($items2[$key][$value2]);
                }
            }

            Excel::create('FOOTFALL_VISITS_' . $name, function ($excel) use ($export, $items1, $items2, $value_header) {
                $header = array(
                    'dong1' => 'The results of measuring the number of visitors in and out at the point: ' . $value_header['value1'] . ' ',
                    'dong2' => 'The results of measuring the number of visitors in and out at the point: ' . $value_header['value1'] . ' ',
                    'dong3' => 'Locations: ' . $value_header['value1'] . '',
                    'dong4' => 'Time:  ' . $value_header['value2'] . '  ',
                    'dong5' => 'Date:  ' . $value_header['value3'] . '  ',
                );
                $excel->setCreator('ACS')->setCompany('ACS Solution');
                $title1 = 'Amount';
                $title2 = 'Percent';
                $this->get_sheet_second($title1, $excel, $sheet, $header, $items1);
                if ($export === 'sosanh') {
                    $this->get_sheet_second($title2, $excel, $sheet, $header, $items2);
                }
            })->store('xls', public_path('exports'));
            $file_name = 'FOOTFALL_VISITS_' . $name . '.xls';
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
        return response()->json($file_name);
    }

    // Xuất excel phần so sánh 2 cửa hàng
    public function sp_footfall_store_comparison_export_excel(Request $request)
    {
        try {
            $request_user = $request->user();
            $user_id = $request_user != null ?  $request_user->id : null;
            if (isset($request->user_id))
                $user_id = $request->user_id;
            $organization_id = $request->organization_id;
            $site_id = $request->site_id;
            $organization_id_compare = $request->organization_id_compare;
            $site_id_compare = $request->site_id_compare;
            $start_time = $request->start_time;
            $end_time = $request->end_time;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $view_by = $request->view_by;
            $indexOptionSelected = $request->indexOptionSelected;  // Visits và Traffic;
            $operation = $request->operation;
            $export = $request->export;

            $items = DB::select("exec sp_poc_data_in_out_sum_by_site $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date, $end_date, $view_by, $operation");
            $itemsComapare = DB::select("exec sp_poc_data_in_out_sum_by_site $user_id, $organization_id_compare, $site_id_compare, $start_time, $end_time, $start_date, $end_date, $view_by, $operation");

            $index_not_list = DB::select("select * from  fc_get_index_module_not_org( $organization_id)");
            foreach ($index_not_list as $item) {
                $index_not[]   = $item->index_name;
            }
            // $index_not  =  ["Kids Visits", "PasserBy", "Traffic Flow"];

            $row = count($items);
            $organization1 =  Organization::find($organization_id);
            $organization2 =  Organization::find($organization_id_compare);
            $org_name1 =  $organization1->organization_name;
            $org_name2 =  $organization2->organization_name;

            if ($site_id  != 0) {
                $site1 = Site::find($site_id);
                $org_name1  = $site1->site_name;
            }
            if ($site_id_compare  != 0) {
                $site2 = Site::find($site_id_compare);
                $org_name2  = $site2->site_name;
            }
            $passer_by1 = 0;
            $total_num_to_enter1 = 0;
            $total_num_to_exit1 = 0;
            $shopper_visits1 = 0;
            $kids_visits1 = 0;
            $turn_in_rate1 = 0;
            $total_traffic1 = 0;
            $total_avg_time1 = 0;
            $passer_by2 = 0;
            $total_num_to_enter2 = 0;
            $total_num_to_exit2 = 0;
            $shopper_visits2 = 0;
            $kids_visits2 = 0;
            $turn_in_rate2 = 0;
            $total_traffic2 = 0;
            $total_avg_time2 = 0;
            $i = 0;
            $i_compared = 0;
            foreach ($items as $key1 => $item) {
                $passer_by1  += (int) $item->passer_by;
                $total_num_to_enter1  += (int) $item->num_to_enter;
                $total_num_to_exit1  += (int) $item->num_to_exit;
                $kids_visits1  += (int) $item->kids_visits;
                $shopper_visits1  += (int) $item->shopper_visits;
                $turn_in_rate1  += (float) $item->turn_in_rate;
                $total_traffic1       += (int) $item->traffic;
                $total_avg_time1      += (float) $item->avg_time;

                $passer_by2  += (int) $itemsComapare[$key1]->passer_by;
                $total_num_to_enter2  += (int) $itemsComapare[$key1]->num_to_enter;
                $total_num_to_exit2  += (int) $itemsComapare[$key1]->num_to_exit;
                $kids_visits2  += (int) $itemsComapare[$key1]->kids_visits;
                $shopper_visits2  += (int) $itemsComapare[$key1]->shopper_visits;
                $turn_in_rate2  += (float) $itemsComapare[$key1]->turn_in_rate;
                $total_traffic2       += (int) $itemsComapare[$key1]->traffic;
                $total_avg_time2      += (float) $itemsComapare[$key1]->avg_time;
                if (intval($item->avg_time) > 0)
                    $i++;
                if (intval($itemsComapare[$key1]->avg_time) > 0)
                    $i_compared++;
            }

            $turn_in_rate1 = ($row) > 0 ? (float) number_format(($turn_in_rate1 / ($row)), 2, '.', '') : 0;
            $turn_in_rate2 = ($row) > 0 ? (float) number_format(($turn_in_rate2 / ($row)), 2, '.', '') : 0;
            $total_avg_time1 = ($i) > 0 ? (float) number_format(($total_avg_time1 / ($i)), 2, '.', '') : 0;
            $total_avg_time2 = ($i_compared) > 0 ? (float) number_format(($total_avg_time2 / ($i_compared)), 2, '.', '') : 0;
            if ($export === 'xuhuong') {
                $passer_by1 = ($row) > 0 ? (int) number_format(($passer_by1 / ($row)), 2, '.', '') : 0;
                $total_num_to_enter1 = ($row) > 0 ? (int) number_format(($total_num_to_enter1 / ($row)), 2, '.', '') : 0;
                $total_num_to_exit1 = ($row) > 0 ? (int) number_format(($total_num_to_enter1 / ($row)), 2, '.', '') : 0;
                $kids_visits1 = ($row) > 0 ? (int) number_format(($kids_visits1 / ($row)), 2, '.', '') : 0;
                $total_traffic1 = ($row) > 0 ? (int) number_format(($total_traffic1 / ($row)), 2, '.', '') : 0;
                $shopper_visits1 = ($row) > 0 ? (int) number_format(($shopper_visits1 / ($row)), 2, '.', '') : 0;
                $passer_by2 = ($row) > 0 ? (int) number_format(($passer_by2 / ($row)), 2, '.', '') : 0;
                $total_num_to_enter2 = ($row) > 0 ? (int) number_format(($total_num_to_enter2 / ($row)), 2, '.', '') : 0;
                $kids_visits2 = ($row) > 0 ? (int) number_format(($kids_visits2 / ($row)), 2, '.', '') : 0;
                $total_traffic2 = ($row) > 0 ? (int) number_format(($total_traffic2 / ($row)), 2, '.', '') : 0;
                $shopper_visits2 = ($row) > 0 ? (int) number_format(($shopper_visits2 / ($row)), 2, '.', '') : 0;
            }
            $this->get_column_excel($visits, $org_name1, $org_name2, $total_num_to_enter1, $total_num_to_enter2);
            $this->get_column_excel($exit, $org_name1, $org_name2, $total_num_to_exit1, $total_num_to_exit2);
            $this->get_column_excel($traffic, $org_name1, $org_name2, $total_traffic1, $total_traffic2);
            $this->get_column_excel($shopper, $org_name1, $org_name2, $shopper_visits1, $shopper_visits2);
            $this->get_column_excel($passer, $org_name1, $org_name2, $passer_by1, $passer_by2);
            $this->get_column_excel($kids, $org_name1, $org_name2, $kids_visits1, $kids_visits2);
            $this->get_column_excel($turn_rate, $org_name1, $org_name2, $turn_in_rate1, $turn_in_rate2);
            $this->get_column_excel($avg_time, $org_name1, $org_name2, $total_avg_time1, $total_avg_time2, true);
            foreach ($items as $key1 => $item) {
                $this->get_more_column_excel($org_name1, $org_name2, $item,  $itemsComapare,  $key1,  $visits, $traffic,$exits, $shopper,  $passer,   $kids,    $turn_rate,  $avg_time);
            }

            $name = Carbon::today()->format('d_m_Y') . 'v' . rand(1, 1000);
            $value_header =  $this->get_value_header_store($start_date, $end_date, $start_time, $end_time, $org_name1, $org_name2);
            Excel::create('FOOTFALL_STORE_COMPARISON_' . $name, function ($excel) use ($visits, $traffic,$exits, $passer, $shopper, $turn_rate, $kids, $avg_time, $value_header, $index_not) {
                $header = array(
                    'dong1' => 'The results of measuring the number of visitors in and out at the point: ' . $value_header['value1'] . ' ',
                    'dong2' => 'Time:  ' . $value_header['value2'] . '  ',
                    'dong3' => 'Date:  ' . $value_header['value3'] . '  ',
                );
                $excel->setCreator('ACS')->setCompany('ACS Solution');
                $Passer_title = 'PasserBy';
                $visits_title = 'Visitors';
                $exits_title = 'Exits';
                $shopper_title = 'Shoppers';
                $turn_rate_title = 'Turn in rate (%)';
                $kids_title = 'Kids Visits';
                $avg_time_title = 'Avg Time';
                $traffic_title = 'Traffic Flow';
                if (!in_array('PasserBy', $index_not, true))
                    $this->get_sheet_first($Passer_title, $excel, $sheet, $header, $passer);
                if (!in_array('Visitors', $index_not, true))
                    $this->get_sheet_first($visits_title, $excel, $sheet, $header, $visits);
                if (!in_array('Exits', $index_not, true))
                     $this->get_sheet_first($exits_title, $excel, $sheet, $header, $exits);
                if (!in_array('Shoppers', $index_not, true))
                    $this->get_sheet_first($shopper_title, $excel, $sheet, $header, $shopper);
                if (!in_array('Turn in rate (%)', $index_not, true))
                    $this->get_sheet_first($turn_rate_title, $excel, $sheet, $header, $turn_rate);
                if (!in_array('Kids Visits', $index_not, true))
                    $this->get_sheet_first($kids_title, $excel, $sheet, $header, $kids);
                if (!in_array('Avg Time', $index_not, true))
                    $this->get_sheet_first($avg_time_title, $excel, $sheet, $header, $avg_time);
                if (!in_array('Traffic Flow', $index_not, true))
                    $this->get_sheet_first($traffic_title, $excel, $sheet, $header, $traffic);
            })->store('xls', public_path('exports'));

            $file_name = 'FOOTFALL_STORE_COMPARISON_' . $name . '.xls';
        } catch (\Exception $exception) {
            return 'none';
        }
        return response()->json($file_name);
    }

    // Xuất excel só sánh thời gian
    public function sp_footfall_time_comparison_export_excel(Request $request)
    {
        try {
            $request_user = $request->user();
            $user_id = $request_user != null ?  $request_user->id : null;
            if (isset($request->user_id))
                $user_id = $request->user_id;
            $organization_id = $request->organization_id;
            $site_id = $request->site_id;
            $start_time = $request->start_time;
            $end_time = $request->end_time;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $view_by = $request->view_by;
            $start_date_compare = $request->start_date_compare;
            $end_date_compare = $request->end_date_compare;
            $operation = 'sum';

            $items = DB::select("exec sp_poc_data_in_out_sum_by_site $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date, $end_date, $view_by, $operation");
            $itemsComapare = DB::select("exec sp_poc_data_in_out_sum_by_site $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date_compare, $end_date_compare, $view_by, $operation");

            $index_not_list = DB::select("select * from  fc_get_index_module_not_org( $organization_id)");
            foreach ($index_not_list as $item) {
                $index_not[]   = $item->index_name;
            }
            // $index_not  =  ["Kids Visits", "PasserBy", "Traffic Flow"];

            $row1 = count($items);
            $row2 = count($itemsComapare);
            $organization =  Organization::find($organization_id);
            $org_name =  $organization->organization_name;
            if ($site_id  != 0) {
                $site = Site::find($site_id);
                $org_name  = $site->site_name;
            }
            $passer_by1 = 0;
            $total_num_to_enter1 = 0;
            $total_num_to_exit1 = 0;
            $shopper_visits1 = 0;
            $kids_visits1 = 0;
            $turn_in_rate1 = 0;
            $total_traffic1 = 0;
            $total_avg_time1 = 0;
            $passer_by2 = 0;
            $total_num_to_enter2 = 0;
            $total_num_to_exit2 = 0;
            $shopper_visits2 = 0;
            $kids_visits2 = 0;
            $turn_in_rate2 = 0;
            $total_traffic2 = 0;
            $total_avg_time2 = 0;
            $i = 0;
            foreach ($items as $item) {
                $passer_by1  += (int) $item->passer_by;
                $total_num_to_enter1  += (int) $item->num_to_enter;
                $total_num_to_exit1  += (int) $item->num_to_exit;
                $kids_visits1  += (int) $item->kids_visits;
                $shopper_visits1  += (int) $item->shopper_visits;
                $turn_in_rate1  += (float) $item->turn_in_rate;
                $total_traffic1       += (int) $item->traffic;
                $total_avg_time1      +=    (float) $item->avg_time;
                if (intval($item->avg_time) > 0)
                    $i++;
            }
            $i_compared = 0;
            foreach ($itemsComapare as $item) {
                $passer_by2  += (int) $item->passer_by;
                $total_num_to_enter2  += (int) $item->num_to_enter;
                $total_num_to_exit2  += (int) $item->num_to_exit;
                $kids_visits2  += (int) $item->kids_visits;
                $shopper_visits2  += (int) $item->shopper_visits;
                $turn_in_rate2  += (float) $item->turn_in_rate;
                $total_traffic2       += (int) $item->traffic;
                $total_avg_time2      += (float) $item->avg_time;
                if (intval($item->avg_time) > 0)
                    $i_compared++;
            }
            $value_header =  $this->get_value_header_time($start_date, $end_date, $start_date_compare, $end_date_compare, $start_time, $end_time, $org_name);


            $total_avg_time1 = ($i) > 0 ? (float) number_format(($total_avg_time1 / ($i)), 2, '.', '') : 0;
            $total_avg_time2 = ($i_compared) > 0 ? (float) number_format(($total_avg_time2 / ($i_compared)), 2, '.', '') : 0;
            $turn_in_rate1 = ($row1) > 0 ? (float) number_format(($turn_in_rate1 / ($row1)), 2, '.', '') : 0;
            $turn_in_rate2 = ($row2) > 0 ? (float) number_format(($turn_in_rate2 / ($row2)), 2, '.', '') : 0;
            $visits[] = array(
                'Date/Time'   => ' ',
                $value_header['value3']    => $total_num_to_enter1,
                $value_header['value4']   => $total_num_to_enter2,
                'Difference'  => $total_num_to_enter1 >= $total_num_to_enter2  ? ($total_num_to_enter1 - $total_num_to_enter2) : ($total_num_to_enter2 - $total_num_to_enter1)
            );  
            
            $exits[] = array(
                'Date/Time'   => ' ',
                $value_header['value3']    => $total_num_to_exit1,
                $value_header['value4']   => $total_num_to_exit2,
                'Difference'  => $total_num_to_exit1 >= $total_num_to_exit2  ? ($total_num_to_exit1 - $total_num_to_exit2) : ($total_num_to_exit2 - $total_num_to_exit1)
            );

            $traffic[] = array(
                'Date/Time' => ' ',
                $value_header['value3']    => $total_traffic1,
                $value_header['value4']    => $total_traffic2,
                'Difference'  => $total_traffic1 >= $total_traffic2 ? ($total_traffic1 - $total_traffic2) : ($total_traffic2 - $total_traffic1)
            );
            $shopper[] = array(
                'Date/Time' => ' ',
                $value_header['value3']    => $shopper_visits1,
                $value_header['value4']    => $shopper_visits2,
                'Difference'  => $shopper_visits1 >= $shopper_visits2 ? ($shopper_visits1 - $shopper_visits2) : ($shopper_visits2 - $shopper_visits1)
            );
            $passer[] = array(
                'Date/Time' => ' ',
                $value_header['value3']    => $passer_by1,
                $value_header['value4']    => $passer_by2,
                'Difference'  => $passer_by1 >= $passer_by2 ? ($passer_by1 - $passer_by2) : ($passer_by2 - $passer_by1)
            );
            $kids[] = array(
                'Date/Time' => ' ',
                $value_header['value3']    => $kids_visits1,
                $value_header['value4']    => $kids_visits2,
                'Difference'  => $kids_visits1 >= $kids_visits2 ? ($kids_visits1 - $kids_visits2) : ($kids_visits2 - $kids_visits1)
            );
            $turn_rate[] = array(
                'Date/Time' => ' ',
                $value_header['value3']    => $turn_in_rate1,
                $value_header['value4']    => $turn_in_rate2,
                'Difference'  => $turn_in_rate1 >= $turn_in_rate2 ? ($turn_in_rate1 - $turn_in_rate2) : ($turn_in_rate2 - $turn_in_rate1)
            );
            $avg_time[] = array(
                'Date/Time' => ' ',
                $value_header['value3']    => $this->changeSecondsToformatTime($total_avg_time1 * 60),
                $value_header['value4']    =>  $this->changeSecondsToformatTime($total_avg_time2 * 60),
                'Difference'  => $total_avg_time1 >= $total_avg_time2 ? ($this->changeSecondsToformatTime(($total_avg_time1 - $total_avg_time2) * 60)) : ($this->changeSecondsToformatTime(($total_avg_time2 - $total_avg_time1) * 60))
            );
            if ($row1 >= $row2) {
                foreach ($items as $key1 => $item) {
                    $visits[] = array(
                        'Date/Time'       =>  $item->time_period . ((isset($itemsComapare[$key1]) && $view_by != "Hour") ? (' / ' . $itemsComapare[$key1]->time_period) : ''),
                        $value_header['value3']    =>   (int) $item->num_to_enter,
                        $value_header['value4']    => (isset($itemsComapare[$key1]) ? (int) $itemsComapare[$key1]->num_to_enter : 0),
                        'Difference'      => (isset($itemsComapare[$key1]) ?   $this->tinh_chenh_lech($item->num_to_enter, $itemsComapare[$key1]->num_to_enter) : 0)
                    );

                    $exits[] = array(
                         'Date/Time'       =>  $item->time_period . ((isset($itemsComapare[$key1]) && $view_by != "Hour") ? (' / ' . $itemsComapare[$key1]->time_period) : ''),
                         $value_header['value3']    =>   (int) $item->num_to_exit,
                         $value_header['value4']    => (isset($itemsComapare[$key1]) ? (int) $itemsComapare[$key1]->num_to_exit : 0),
                         'Difference'      => (isset($itemsComapare[$key1]) ?   $this->tinh_chenh_lech($item->num_to_exit, $itemsComapare[$key1]->num_to_exit) : 0)
                     );


                    $traffic[] = array(
                        'Date/Time'   =>   $item->time_period . ((isset($itemsComapare[$key1]) && $view_by != "Hour") ? (' / ' . $itemsComapare[$key1]->time_period) : ''),
                        $value_header['value3']    => (int) $item->traffic,
                        $value_header['value4']    => (isset($itemsComapare[$key1])  ? (int) $itemsComapare[$key1]->traffic : 0),
                        'Difference'  => (isset($itemsComapare[$key1]) ?   $this->tinh_chenh_lech($item->traffic, $itemsComapare[$key1]->traffic) : 0)
                    );
                    $shopper[] = array(
                        'Date/Time'   =>   $item->time_period . ((isset($itemsComapare[$key1]) && $view_by != "Hour") ? (' / ' . $itemsComapare[$key1]->time_period) : ''),
                        $value_header['value3']    => (int) $item->shopper_visits,
                        $value_header['value4']    => (isset($itemsComapare[$key1])  ? (int) $itemsComapare[$key1]->shopper_visits : 0),
                        'Difference'  => (isset($itemsComapare[$key1]) ?   $this->tinh_chenh_lech($item->shopper_visits, $itemsComapare[$key1]->shopper_visits) : 0)
                    );
                    $passer[] = array(
                        'Date/Time'   =>   $item->time_period . ((isset($itemsComapare[$key1]) && $view_by != "Hour") ? (' / ' . $itemsComapare[$key1]->time_period) : ''),
                        $value_header['value3']    => (int) $item->passer_by,
                        $value_header['value4']    => (isset($itemsComapare[$key1])  ? (int) $itemsComapare[$key1]->passer_by : 0),
                        'Difference'  => (isset($itemsComapare[$key1]) ?   $this->tinh_chenh_lech($item->passer_by, $itemsComapare[$key1]->passer_by) : 0)
                    );
                    $kids[] = array(
                        'Date/Time'   =>   $item->time_period . ((isset($itemsComapare[$key1]) && $view_by != "Hour") ? (' / ' . $itemsComapare[$key1]->time_period) : ''),
                        $value_header['value3']    => (int) $item->kids_visits,
                        $value_header['value4']    => (isset($itemsComapare[$key1])  ?  (int) $itemsComapare[$key1]->kids_visits : 0),
                        'Difference'  => (isset($itemsComapare[$key1]) ?   $this->tinh_chenh_lech($item->kids_visits, $itemsComapare[$key1]->kids_visits) : 0)
                    );
                    $turn_rate[] = array(
                        'Date/Time'   =>   $item->time_period . ((isset($itemsComapare[$key1]) && $view_by != "Hour") ? (' / ' . $itemsComapare[$key1]->time_period) : ''),
                        $value_header['value3']    => (float) $item->turn_in_rate,
                        $value_header['value4']    => (isset($itemsComapare[$key1])  ?  (float) $itemsComapare[$key1]->turn_in_rate : 0),
                        'Difference'  => (isset($itemsComapare[$key1]) ?   $this->tinh_chenh_lech($item->turn_in_rate, $itemsComapare[$key1]->turn_in_rate) : 0)
                    );
                    $avg_time[] = array(
                        'Date/Time'   =>   $item->time_period . ((isset($itemsComapare[$key1]) && $view_by != "Hour") ? (' / ' . $itemsComapare[$key1]->time_period) : ''),
                        $value_header['value3']    => $this->changeSecondsToformatTime((float) $item->avg_time * 60),
                        $value_header['value4']    => (isset($itemsComapare[$key1])  ? $this->changeSecondsToformatTime((float)  $itemsComapare[$key1]->avg_time * 60) : ''),
                        'Difference'  => (isset($itemsComapare[$key1]) ?  $this->changeSecondsToformatTime($this->tinh_chenh_lech($item->avg_time, $itemsComapare[$key1]->avg_time) * 60)  : '00:00:00'),
                    );
                }
            }
            if ($row2 > $row1) {
                foreach ($itemsComapare as $key1 => $item) {
                    $visits[] = array(
                        'Date/Time'       =>  $item->time_period . (isset($items[$key1]) ? (' / ' . $items[$key1]->time_period) : ''),
                        $value_header['value4']    => (isset($items[$key1]) ? (int) $items[$key1]->num_to_enter : 0),
                        $value_header['value3']    =>   (int) $item->num_to_enter,
                        'Difference'      => (isset($items[$key1]) ?   $this->tinh_chenh_lech($item->num_to_enter, $items[$key1]->num_to_enter) : 0)
                    );
                     $exits[] = array(
                         'Date/Time'       =>  $item->time_period . (isset($items[$key1]) ? (' / ' . $items[$key1]->time_period) : ''),
                         $value_header['value4']    => (isset($items[$key1]) ? (int) $items[$key1]->num_to_exit : 0),
                         $value_header['value3']    =>   (int) $item->num_to_exit,
                         'Difference'      => (isset($items[$key1]) ?   $this->tinh_chenh_lech($item->num_to_exit, $items[$key1]->num_to_exit) : 0)
                     );

                    $traffic[] = array(
                        'Date/Time'   =>  $item->time_period . (isset($items[$key1]) ? (' / ' . $items[$key1]->time_period) : ''),
                        $value_header['value4']    => (isset($items[$key1])  ? (int) $items[$key1]->traffic : 0),
                        $value_header['value3']    => (int) $item->traffic,
                        'Difference'  => (isset($items[$key1]) ?   $this->tinh_chenh_lech($item->traffic, $items[$key1]->traffic) : 0)
                    );
                    $shopper[] = array(
                        'Date/Time'   =>  $item->time_period . (isset($items[$key1]) ? (' / ' . $items[$key1]->time_period) : ''),
                        $value_header['value4']    => (isset($items[$key1])  ? (int) $items[$key1]->shopper_visits : 0),
                        $value_header['value3']    => (int) $item->shopper_visits,
                        'Difference'  => (isset($items[$key1]) ?   $this->tinh_chenh_lech($item->shopper_visits, $items[$key1]->shopper_visits) : 0)
                    );
                    $passer[] = array(
                        'Date/Time'   =>  $item->time_period . (isset($items[$key1]) ? (' / ' . $items[$key1]->time_period) : ''),
                        $value_header['value4']    => (isset($items[$key1])  ? (int) $items[$key1]->passer_by : 0),
                        $value_header['value3']    => (int) $item->passer_by,
                        'Difference'  => (isset($items[$key1]) ?   $this->tinh_chenh_lech($item->passer_by, $items[$key1]->passer_by) : 0)
                    );
                    $kids[] = array(
                        'Date/Time'   =>  $item->time_period . (isset($items[$key1]) ? (' / ' . $items[$key1]->time_period) : ''),
                        $value_header['value4']    => (isset($items[$key1])  ?  (int) $items[$key1]->kids_visits : 0),
                        $value_header['value3']    => (int) $item->kids_visits,
                        'Difference'  => (isset($items[$key1]) ?   $this->tinh_chenh_lech($item->kids_visits, $items[$key1]->kids_visits) : 0)
                    );
                    $turn_rate[] = array(
                        'Date/Time'   =>  $item->time_period . (isset($items[$key1]) ? (' / ' . $items[$key1]->time_period) : ''),
                        $value_header['value4']    => (isset($items[$key1])  ?  (float) $items[$key1]->turn_in_rate : 0),
                        $value_header['value3']    => (float) $item->turn_in_rate,
                        'Difference'  => (isset($items[$key1]) ?   $this->tinh_chenh_lech($item->turn_in_rate, $items[$key1]->turn_in_rate) : 0)
                    );
                    $avg_time[] = array(
                        'Date/Time'   =>  $item->time_period . (isset($items[$key1]) ? (' / ' . $items[$key1]->time_period) : ''),
                        $value_header['value4']    => (isset($items[$key1])  ?  $this->changeSecondsToformatTime((float) $items[$key1]->avg_time * 60) : ''),
                        $value_header['value3']    =>   $this->changeSecondsToformatTime((float) $item->avg_time * 60),
                        'Difference'  => (isset($items[$key1]) ?  $this->changeSecondsToformatTime(($this->tinh_chenh_lech($item->avg_time, $items[$key1]->avg_time)) * 60) : '00:00:00'),
                    );
                }
            }

            $name = Carbon::today()->format('d_m_Y') . 'v' . rand(1, 1000);
            Excel::create('FOOTFALL_TIME_COMPARISON_' . $name, function ($excel) use ($visits, $traffic,$exits, $passer, $shopper, $turn_rate, $kids, $avg_time, $value_header, $index_not) {
                $header = array(
                    'dong1' => 'The results of measuring the number of visitors in and out at the point: ' . $value_header['value1'] . ' ',
                    'dong2' => 'Time:  ' . $value_header['value2'] . '  ',
                    'dong3' => 'Date:  (' . $value_header['value3'] . ')  compare to  (' . $value_header['value4'] . ')'
                );
                // Tiêu đề ngoài file
                $excel->setCreator('ACS')->setCompany('ACS Solution');

                $Passer_title = 'PasserBy';
                $visits_title = 'Visitors';
                $exits_title = 'Exits';
                $shopper_title = 'Shoppers';
                $turn_rate_title = 'Turn in rate (%)';
                $kids_title = 'Kids Visits';
                $avg_time_title = 'Avg Time';
                $traffic_title = 'Traffic Flow';

                if (!in_array('PasserBy', $index_not, true))
                    $this->get_sheet_first($Passer_title, $excel, $sheet, $header, $passer);
                if (!in_array('Visitors', $index_not, true))
                    $this->get_sheet_first($visits_title, $excel, $sheet, $header, $visits); 
                if (!in_array('Exits', $index_not, true))
                    $this->get_sheet_first($exits_title, $excel, $sheet, $header, $exits);
                if (!in_array('Shoppers', $index_not, true))
                    $this->get_sheet_first($shopper_title, $excel, $sheet, $header, $shopper);
                if (!in_array('Turn in rate (%)', $index_not, true))
                    $this->get_sheet_first($turn_rate_title, $excel, $sheet, $header, $turn_rate);
                if (!in_array('Kids Visits', $index_not, true))
                    $this->get_sheet_first($kids_title, $excel, $sheet, $header, $kids);
                if (!in_array('Avg Time', $index_not, true))
                    $this->get_sheet_first($avg_time_title, $excel, $sheet, $header, $avg_time);
                if (!in_array('Traffic Flow', $index_not, true))
                    $this->get_sheet_first($traffic_title, $excel, $sheet, $header, $traffic);
            })->store('xls', public_path('exports'));

            $file_name = 'FOOTFALL_TIME_COMPARISON_' . $name . '.xls';
        } catch (\Exception $exception) {
            return 'none';
        }
        return response()->json($file_name);
    }

    // Xuất excel heatmap
    public function sp_footfall_heatmap_treemap_coloraxis_sum_export_excel(Request $request)
    {
        try {
            $request_user = $request->user();
            $user_id = $request_user != null ?  $request_user->id : null;
            if (isset($request->user_id))
                $user_id = $request->user_id;
            $organization_id = $request->organization_id;
            $site_id = $request->site_id;
            $start_time = $request->start_time;
            $end_time = $request->end_time;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $operation = "SUM";
            $items = DB::select("exec sp_general_report_data_in_out_sum_by_store $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date, $end_date, $operation,0");

            $index_not_list = DB::select("select * from  fc_get_index_module_not_org( $organization_id)");
            foreach ($index_not_list as $item) {
                $index_not[]   = $item->index_name;
            }
            // $index_not  =  ["Kids Visits", "PasserBy", "Traffic Flow"];

            $row = count($items);
            $organization =  Organization::find($organization_id);
            $org_name =  $organization->organization_name;
            if ($site_id  != 0) {
                $site = Site::find($site_id);
                $org_name  = $site->site_name;
            }
            $passer_by = 0;
            $total_num_to_enter = 0;
            $total_num_to_exit = 0;
            $shopper_visits = 0;
            $kids_visits = 0;
            $turn_in_rate = 0;
            $total_traffic = 0;
            $total_avg_time = 0;
            $i = 0;
            foreach ($items as $item) {
                $passer_by  += (int) $item->passer_by;
                $total_num_to_enter  += (int) $item->num_to_enter;
                $total_num_to_exit  += (int) $item->num_to_exit;
                $kids_visits  += (int) $item->kids_visits;
                $shopper_visits  += (int) $item->shopper_visits;
                $turn_in_rate  += (float) $item->turn_in_rate;
                $total_traffic       += (int) $item->traffic;
                $total_avg_time      += (float) $item->avg_time;
                if (intval($item->avg_time) > 0)
                    $i++;
            }
            $turn_in_rate = ($row) > 0 ? (float) number_format(($turn_in_rate / $row), 2, '.', '') : 0;
            $total_avg_time = ($row) > 0 ? (float) number_format(($total_avg_time / $i), 2, '.', '') : 0;
            $items1[] = array(
                'Locations'            =>  '',
                'PasserBy'            => $passer_by,
                'Visitors'              => $total_num_to_enter,
                'Exits'              => $total_num_to_exit,
                'Shoppers'      => $shopper_visits,
                'Turn in rate (%)'    => $turn_in_rate,
                'Traffic Flow'        => $total_traffic,
                'Kids Visits'         => $kids_visits,
                'Avg Time'      =>  $this->changeSecondsToformatTime($total_avg_time * 60),
            );
            foreach ($items as $item) {
                $items1[] = array(
                    'Locations'           => $item->site_name,
                    'PasserBy'            => (int) $item->passer_by,
                    'Visitors'              => (int) $item->num_to_enter,
                    'Exits'              => (int) $item->num_to_exit,
                    'Shoppers'      => (int) $item->shopper_visits,
                    'Turn in rate (%)'    => (float) $item->turn_in_rate,
                    'Traffic Flow'        => (float) $item->traffic,
                    'Kids Visits'         => (int) $item->kids_visits,
                    'Avg Time'      => $this->changeSecondsToformatTime((float) $item->avg_time * 60),
                );
            }
            $value_header =  $this->get_value_header_poc_in_out($start_date, $end_date, $start_time, $end_time, $org_name);
            $name = Carbon::today()->format('d_m_Y') . 'v' . rand(1, 1000);

            foreach ($items1 as $key => $value) {
                foreach ($index_not as $value2) {
                    unset($items1[$key][$value2]);
                }
            }

            Excel::create('FOOTFALL_SITES_' . $name, function ($excel) use ($items1, $value_header) {
                $header = array(
                    'dong1' => 'The results of measuring the number of visitors in and out at the point: ' . $value_header['value1'] . ' ',
                    'dong2' => 'The results of measuring the number of visitors in and out at the point: ' . $value_header['value1'] . ' ',
                    'dong3' => 'Locations: ' . $value_header['value1'] . '',
                    'dong4' => 'Time:  ' . $value_header['value2'] . '  ',
                    'dong5' => 'Date:  ' . $value_header['value3'] . '  ',
                );
                $excel->setCreator('ACS')->setCompany('ACS Solution');

                $title1 = 'Locations';
                $this->get_sheet_second($title1, $excel, $sheet, $header, $items1);
            })->store('xls', public_path('exports'));
            $file_name = 'FOOTFALL_SITES_' . $name . '.xls';
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
        return response()->json($file_name);
    }

    // Xuất excel báo cáo các cửa hàng
    public function sp_footfall_heatmap_treemap_coloraxis_sum_reporting_export_excel(Request $request)
    {
        try {
            $request_user = $request->user();
            $user_id = $request_user != null ?  $request_user->id : null;
            if (isset($request->user_id))
                $user_id = $request->user_id;
            $organization_id = $request->organization_id;
            // $items = isset($request->data) ? $request->data : [];
            $site_id = $request->site_id;
            $start_time = $request->start_time;
            $end_time = $request->end_time;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $operation = "SUM";
            // Nghĩa sửa từ đây
            $items = [];
            foreach ($site_id as $value) {
                $currentData = DB::select("exec sp_general_report_data_in_out_sum_by_store $user_id, $organization_id, $value, $start_time, $end_time, $start_date, $end_date, $operation,0");
                $items = array_merge($items, $currentData);
            }
            // if (isset($request->data)) {
            //     $items = $request->data;
            // } else {
            //     $items = [];
            //     foreach ($site_id as $value) {
            //         $currentData = DB::select("exec sp_general_report_data_in_out_sum_by_store $user_id, $organization_id, $value, $start_time, $end_time, $start_date, $end_date, $operation,0");
            //         $items = array_merge($items, $currentData);
            //     }
            // }
            // end

            $exists_array    = array();
            foreach ($items as $element) {
                if (!in_array($element, $exists_array)) {
                    $exists_array[]    = $element ;
                }
            }

            $row = count($exists_array);

            $index_not_list = DB::select("select * from  fc_get_index_module_not_org( $organization_id)");
            foreach ($index_not_list as $item) {
                $index_not[]   = $item->index_name;
            }
            // $index_not  =  ["Kids Visits", "PasserBy", "Traffic Flow"];

            $organization =  Organization::find($organization_id);
            $org_name =  $organization->organization_name;
            if ($site_id  != 0 && $row == 1) {
                $site = Site::find($site_id[0]);
                $org_name  = $site->site_name;
            }
            $passer_by = 0;
            $total_num_to_enter = 0;
            $total_num_to_exit = 0;
            $shopper_visits = 0;
            $kids_visits = 0;
            $turn_in_rate = 0;
            $total_traffic = 0;
            $total_avg_time = 0;
            $i = 0;
            foreach ($exists_array as $item) {
                //
                $newItem = $item;
                if (is_object($item)) {
                    $newItem = (array) $item;
                }
                //
                $passer_by  += (int) $newItem['passer_by'];
                $total_num_to_enter  += (int) $newItem['num_to_enter'];
                $total_num_to_exit  += (int) $newItem['num_to_exit'];
                $kids_visits  += (int) $newItem['kids_visits'];
                $shopper_visits  += (int) $newItem['shopper_visits'];
                $turn_in_rate  += (float) $newItem['turn_in_rate'];
                $total_traffic       += (int) $newItem['traffic'];
                $total_avg_time      += (float) $newItem['avg_time'];
                if (intval($newItem['avg_time']) > 0)
                    $i++;
            }
            $turn_in_rate = ($row) > 0 ? (float) number_format(($turn_in_rate / $row), 2, '.', '') : 0;
            if ($i > 0)
                $total_avg_time = ($row) > 0 ? (float) number_format(($total_avg_time / $i), 2, '.', '') : 0;
            $items1[] = array(
                'Locations'           =>  '',
                'PasserBy'            => $passer_by,
                'Visitors'            => $total_num_to_enter,
                'Exits'               => $total_num_to_exit,
                'Shoppers'            => $shopper_visits,
                'Turn in rate (%)'    => $turn_in_rate,
                'Traffic Flow'        => $total_traffic,
                'Kids Visits'         => $kids_visits,
                'Avg Time'            =>  $this->changeSecondsToformatTime($total_avg_time * 60),
            );
            foreach ($exists_array as $item) {
                //
                $newItem = $item;
                if (is_object($item)) {
                    $newItem = (array) $item;
                }
                //
                $items1[] = array(
                    'Locations'           => $newItem['site_name'],
                    'PasserBy'            => (int) $newItem['passer_by'],
                    'Visitors'              => (int) $newItem['num_to_enter'],
                    'Exits'              => (int) $newItem['num_to_exit'],
                    'Shoppers'      => (int) $newItem['shopper_visits'],
                    'Turn in rate (%)'    => (float) $newItem['turn_in_rate'],
                    'Traffic Flow'        => (float) $newItem['traffic'],
                    'Kids Visits'         => (int) $newItem['kids_visits'],
                    'Avg Time'      => $this->changeSecondsToformatTime((float) $newItem['avg_time'] * 60),
                );
            }
            $value_header =  $this->get_value_header_poc_in_out($start_date, $end_date, $start_time, $end_time, $org_name);
            $name = Carbon::today()->format('d_m_Y') . 'v' . rand(1, 1000);
            foreach ($items1 as $key => $value) {
                foreach ($index_not as $value2) {
                    unset($items1[$key][$value2]);
                }
            }

            Excel::create('FOOTFALL_SITES_' . $name, function ($excel) use ($items1, $value_header) {
                $header = array(
                    'dong1' => 'The results of measuring the number of visitors in and out at the point: ' . $value_header['value1'] . ' ',
                    'dong2' => 'The results of measuring the number of visitors in and out at the point: ' . $value_header['value1'] . ' ',
                    'dong3' => 'Locations: ' . $value_header['value1'] . '',
                    'dong4' => 'Time:  ' . $value_header['value2'] . '  ',
                    'dong5' => 'Date:  ' . $value_header['value3'] . '  ',
                );
                $excel->setCreator('ACS')->setCompany('ACS Solution');

                $title1 = 'Locations';
                $this->get_sheet_second($title1, $excel, $sheet, $header, $items1);
            })->store('xls', public_path('exports'));
            $file_name = 'FOOTFALL_SITES_' . $name . '.xls';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return response()->json($file_name);
    }

    // Xuất excel liveview
    public function sp_footfall_get_traffic_export_excel(Request $request)
    {
        try {
            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $request_user = $request->user();
            $user_id = $request_user != null ?  $request_user->id : null;
            if (isset($request->user_id))
                $user_id = $request->user_id;
            $organization_id = $request->organization_id;
            $site_id = $request->site_id;
            $start_time = $request->start_time;
            $end_time = $request->end_time;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $operation = $request->operation;
            $level = $request->level;
            $dimension = $request->dimension;

            $items = DB::select("exec sp_footfall_liveview_history $user_id, $organization_id, $site_id, 0, $start_time,  $end_time, $start_date, $end_date, $operation, $level, $dimension");

            $organization =  Organization::find($organization_id);
            $org_name =  $organization->organization_name;
            if ($site_id  != 0) {
                $site = Site::find($site_id);
                $org_name  = $site->site_name;
            }

            $total_num_to_enter = 0;
            $total_num_to_exit = 0;
            $total_traffic = 0;
            foreach ($items as $item) {
                $total_num_to_enter  += (int) $item->num_to_enter;
                $total_num_to_exit   += (int) $item->num_to_exit;
            }
            $total_traffic       =  $total_num_to_enter + $total_num_to_exit;
            $items1[] = $items2[] = array(
                'Date/Time'            => ' ',
                'Visitors'               => $total_num_to_enter,
                'Exits'                => $total_num_to_exit,
                'Traffic Flow'         => $total_traffic,
            );
            foreach ($items as $key => $item) {
                $num_to_enter    = (int) $item->num_to_enter;
                $num_to_exit     = (int) $item->num_to_exit;
                $traffic         =  $num_to_enter +  $num_to_exit;

                $num_to_enter_per = ($num_to_enter) > 0 ? (float) number_format((($num_to_enter / ($total_num_to_enter)) * 100), 2, '.', '') : 0;
                $num_to_exit_per = ($num_to_exit) > 0 ? (float) number_format((($num_to_exit / ($total_num_to_exit)) * 100), 2, '.', '') : 0;
                $traffic_per     = ($traffic) > 0 ? (float) number_format((($traffic / ($total_traffic)) * 100), 2, '.', '') : 0;

                $items1[] = array(
                    'Date/Time'            => $item->time_period,
                    'Visitors'               => $num_to_enter,
                    'Exits'                => $num_to_exit,
                    'Traffic Flow'         => $traffic,
                );
                $items2[] = array(
                    'Date/Time'            => $item->time_period,
                    'Visitors'               => $num_to_enter_per,
                    'Exits'                => $num_to_exit_per,
                    'Traffic Flow'         => $traffic_per,
                );
            }
            $name = Carbon::today()->format('d_m_Y') . 'v' . rand(1, 1000);
            $value_header =  $this->get_value_header_poc_in_out($start_date, $end_date, $start_time, $end_time, $org_name);
            Excel::create('ACS_LIVEVIEW_' . $name, function ($excel) use ($items1, $items2, $value_header) {
                $header = array(
                    'dong1' => 'The results of measuring the number of visitors in and out at the point: ' . $value_header['value1'] . ' ',
                    'dong2' => 'The results of measuring the number of visitors in and out at the point: ' . $value_header['value1'] . ' ',
                    'dong3' => 'Locations: ' . $value_header['value1'] . '',
                    'dong4' => 'Time:  ' . $value_header['value2'] . '  ',
                    'dong5' => 'Date:  ' . $value_header['value3'] . '  ',
                );
                // Tiêu đề ngoài file
                $excel->setTitle('Báo cáo kết quả hiệu quả hoạt động');
                $excel->setCreator('ACS')->setCompany('ACS Solution');
                $excel->setDescription('Báo cáo phân tích chỉ số');

                $title1 = 'Amount';
                $title2 = 'Percent';
                $this->get_sheet_second($title1, $excel, $sheet, $header, $items1);
                $this->get_sheet_second($title2, $excel, $sheet, $header, $items2);
            })->store('xls', public_path('exports'));

            $file_name = 'ACS_LIVEVIEW_' . $name . '.xls';
        } catch (\Exception $exception) {
            return 'none';
        }
        return response()->json($file_name);
    }

    // Xuất excel Customer Daily
    public function sp_report_poc_raw_data_by_day_export_excel(Request $request)
    {
        try {
            // $current_date = date('Y-m-d');
            // Log::useFiles(base_path() . '/dailyLog/' . $current_date . '-access.log', 'info');

            $request_user = $request->user();
            $user_id = $request_user->id;
            $organization_id = $request->organization_id;
            $site_id = $request->site_id;
            $start_time = $request->start_time;
            $end_time = $request->end_time;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $data = DB::select("exec sp_report_poc_raw_data_by_day $user_id, $organization_id, $site_id, $start_date,  $end_date, $start_time, $end_time");
            $menu_tree = DB::select("SELECT * FROM fc_get_site_in_role($organization_id,$user_id)");
            $organization =  Organization::find($organization_id);
            $org_name =  $organization->organization_name;
            if ($site_id  != 0) {
                $site = Site::find($site_id);
                $org_name  = $site->site_name;
            }
            $parent_tree = [];
            $child_tree = [];
            $duLieuChiTiet = [];

            $menu_tree = $this->tinh_phan_tu_con_trong_menu_cha($menu_tree, '0');

            foreach ($menu_tree as $key1 => $item) {
                if ($item->parent_id == '0') {
                    array_push($parent_tree, $item);
                } else  if ($item->parent_id != null) {
                    array_push($child_tree, $item);
                }
            }
            usort($parent_tree, function ($a, $b) {
                return $a->id < $b->id ? -1 : 1;
            });
            usort($child_tree, function ($a, $b) {
                return  $b->parent_id > $a->parent_id  ? -1 : 1;
            });


            // Log::info('menu_tree');
            // Log::info($menu_tree);
            // Log::info('parent_tree');
            // Log::info($parent_tree);
            // Log::info('child_tree');
            // Log::info($child_tree);

            $list_timeperiod = [];
            foreach ($data as $item) {

                if ($duLieuChiTiet !== null)
                    foreach ($duLieuChiTiet as $value) {
                        array_push($list_timeperiod, $value->time_period);
                    }

                if (array_search($item->time_period, $list_timeperiod) === false || $duLieuChiTiet == null) {
                    $ins = [];
                    $outs = [];
                    for ($i = 0; $i < count($child_tree) + 1; $i++) {
                        $ins[$i] = null;
                        $outs[$i] = null;
                    }

                    $object1 =  (object) array('time_period' => $item->time_period,  'ins' => $ins,  'outs' => $outs);
                    array_push($duLieuChiTiet, $object1);
                } else {
                    foreach ($duLieuChiTiet as $retl) {
                        if ($retl->time_period ==  $item->time_period)
                            $object1 =  $retl;
                    }
                }
                foreach ($child_tree as $key => $val) {
                    if ($val->id == $item->site_id) {
                        $object1->ins[$key]   = $item->num_to_enter;
                        if ($item->num_to_enter !== null) {
                            if ($object1->ins[count($child_tree)] !== null) {
                                $object1->ins[count($child_tree)] = (int)($object1->ins[count($child_tree)]) + (int)($item->num_to_enter);
                            } else {
                                $object1->ins[count($child_tree)] = (int)($item->num_to_enter);
                            }
                        }
                        $object1->outs[$key]  = $item->num_to_exit;
                        if ($item->num_to_exit !== null) {
                            if ($object1->outs[count($child_tree)] !== null) {
                                $object1->outs[count($child_tree)] = (int)($object1->outs[count($child_tree)]) + (int)($item->num_to_exit);
                            } else {
                                $object1->outs[count($child_tree)] = (int)($item->num_to_exit);
                            }
                        }
                    }
                }
            }

            $duLieuTongVaoCacCua = $duLieuTongRaCacCua = $duLieuTrungBinhCacCua = array_fill(0, count($child_tree) + 1, null);
            $duLieuThuTuCacCua =  array_fill(0, count($child_tree), null);

            foreach ($duLieuTongVaoCacCua as $key => $val) {
                foreach ($duLieuChiTiet as $value) {
                    if ($value->ins[$key] != null) {
                        if ($duLieuTongVaoCacCua[$key]  === null)
                            $duLieuTongVaoCacCua[$key] = (int) $value->ins[$key];
                        else
                            $duLieuTongVaoCacCua[$key] = (int)$duLieuTongVaoCacCua[$key] +  (int) $value->ins[$key];
                    }
                    if ($value->outs[$key] != null) {
                        if ($duLieuTongRaCacCua[$key]  === null)
                            $duLieuTongRaCacCua[$key] = (int) $value->outs[$key];
                        else
                            $duLieuTongRaCacCua[$key] = (int)$duLieuTongRaCacCua[$key] +  (int) $value->outs[$key];
                    }
                }
            }

            foreach ($duLieuTongVaoCacCua as $key => $val) {
                $tb = 0;
                if ($duLieuTongVaoCacCua[$key] !== null) {
                    $tb = (int)$duLieuTongVaoCacCua[$key];
                }
                if ($duLieuTongRaCacCua[$key] !== null) {
                    $tb = $tb + (int)$duLieuTongRaCacCua[$key];
                }
                $duLieuTrungBinhCacCua[$key]  = (int) number_format(($tb / 2), 2, '.', '');
            }

            $sort = $duLieuTrungBinhCacCua;
            $duLieuTongRaCacCuaNew = $duLieuTrungBinhCacCua;
            array_pop($sort);
            array_pop($duLieuTongRaCacCuaNew);
            usort($sort, function ($a, $b) {
                return $b  < $a ?  -1 : 1;
            });


            foreach ($sort  as $key => $val) {
                foreach ($duLieuTongRaCacCuaNew as $i =>  $item) {
                    if ($item != null) {
                        if ($item == $val) {
                            $duLieuThuTuCacCua[$i] =  $key + 1;
                        }
                    }
                }
            }

            // $value_header =  $this->get_value_header_poc_in_out($start_date, $end_date, $start_time, $end_time, $org_name);
            $newstr = str_replace("'", '', $start_date);
            $datetimea = new DateTime($newstr);
            $name = $datetimea->format('d_m_Y') . 'v' . rand(1, 1000);

            Excel::create('Daily Traffic Report on ' . $name, function ($excel) use ($duLieuChiTiet, $parent_tree, $child_tree, $duLieuTongVaoCacCua, $duLieuTongRaCacCua, $duLieuTrungBinhCacCua, $duLieuThuTuCacCua) {
                // $header = array(
                //     'dong1' => 'The results of measuring the number of visitors in and out at the point: ' . $value_header['value1'] . ' ',
                //     'dong2' => 'The results of measuring the number of visitors in and out at the point: ' . $value_header['value1'] . ' ',
                //     'dong3' => 'Locations: ' . $value_header['value1'] . '',
                //     'dong4' => 'Time:  ' . $value_header['value2'] . '  ',
                //     'dong5' => 'Date:  ' . $value_header['value3'] . '  ',
                // );

                $excel->setCreator('ACS')->setCompany('ACS Solution');
                $title1 = 'Daily Traffic Report';
                $excel->sheet($title1, function ($sheet) use ($duLieuChiTiet, $parent_tree, $child_tree, $duLieuTongVaoCacCua, $duLieuTongRaCacCua, $duLieuTrungBinhCacCua, $duLieuThuTuCacCua) {

                    $sheet->setHeight([1 => 22, 2 => 22, 3 => 22, 4 => 22, 5 => 22, 6 => 22, 7 => 22]);
                    $sheet->getRowDimension(1)->setRowHeight(25);
                    // Font family
                    $sheet->setFontFamily('Times New Roman');
                    $sheet->setAllBorders('thin');
                    // $sheet->setBorder('A1:AC9', 'thin');
                    $sheet->setStyle(array(
                        'font' => array(
                            'name'      =>  'Times New Roman',
                            'size'      =>  12,
                            // 'bold'      =>  true
                        )
                    ));
                    $sheet->loadView(
                        'export.customer_daily_report',
                        [
                            'duLieuChiTiet' => $duLieuChiTiet,
                            'parent_tree' => $parent_tree,
                            'child_tree' => $child_tree,
                            'duLieuTongVaoCacCua' => $duLieuTongVaoCacCua,
                            'duLieuTongRaCacCua' => $duLieuTongRaCacCua,
                            'duLieuTrungBinhCacCua' => $duLieuTrungBinhCacCua,
                            'duLieuThuTuCacCua' => $duLieuThuTuCacCua,
                            'child_tree' => $child_tree
                        ]
                    );
                });
            })->store('xls', public_path('exports'));
            $file_name = 'Daily Traffic Report on ' . $name . '.xls';
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
        return response()->json($file_name);
    }

    public function tinh_phan_tu_con_trong_menu_cha($data, $id)
    {
        $si = 0;
        foreach ($data as $item) {
            if ($item->parent_id  === $id) {
                $si++;
                foreach ($data as $items) {
                    if ($items->id == $id) {
                        $items->number =  $si;
                    }
                }
                $this->tinh_phan_tu_con_trong_menu_cha($data, $item->id);
            }
        }
        return $data;
    }

    // Xuất excel Customer Monthly
    public function sp_report_poc_raw_data_by_month_export_excel(Request $request)
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
            $data = DB::select("exec sp_report_poc_raw_data_by_month $user_id, $organization_id, $site_id, $start_date, $end_date, $start_time, $end_time");
            $menu_tree = DB::select("SELECT * FROM fc_get_site_in_role($organization_id,$user_id)");
            $organization =  Organization::find($organization_id);
            $org_name =  $organization->organization_name;
            if ($site_id  != 0) {
                $site = Site::find($site_id);
                $org_name  = $site->site_name;
            }
            $parent_tree = [];
            $child_tree = [];
            $duLieuChiTiet = [];

            $menu_tree = $this->tinh_phan_tu_con_trong_menu_cha($menu_tree, '0');

            foreach ($menu_tree as $key1 => $item) {
                if ($item->parent_id == '0') {
                    array_push($parent_tree, $item);
                } else  if ($item->parent_id != null) {
                    array_push($child_tree, $item);
                }
            }
            usort($parent_tree, function ($a, $b) {
                return $a->id < $b->id ? -1 : 1;
            });
            usort($child_tree, function ($a, $b) {
                return  $b->parent_id > $a->parent_id  ? -1 : 1;
            });

            $list_timeperiod = [];
            foreach ($data as $item) {

                if ($duLieuChiTiet !== null)
                    foreach ($duLieuChiTiet as $value) {
                        array_push($list_timeperiod, $value->time_period);
                    }

                if (array_search($item->time_period, $list_timeperiod) === false || $duLieuChiTiet == null) {
                    $ins = [];
                    $outs = [];
                    for ($i = 0; $i < count($child_tree) + 1; $i++) {
                        $ins[$i] = null;
                        $outs[$i] = null;
                    }

                    $object1 =  (object) array('time_period' => $item->time_period,  'ins' => $ins,  'outs' => $outs);
                    array_push($duLieuChiTiet, $object1);
                } else {
                    foreach ($duLieuChiTiet as $retl) {
                        if ($retl->time_period ==  $item->time_period)
                            $object1 =  $retl;
                    }
                }
                foreach ($child_tree as $key => $val) {
                    if ($val->id == $item->site_id) {
                        $object1->ins[$key]   = $item->num_to_enter;
                        if ($item->num_to_enter !== null) {
                            if ($object1->ins[count($child_tree)] !== null) {
                                $object1->ins[count($child_tree)] = (int)($object1->ins[count($child_tree)]) + (int)($item->num_to_enter);
                            } else {
                                $object1->ins[count($child_tree)] = (int)($item->num_to_enter);
                            }
                        }
                        $object1->outs[$key]  = $item->num_to_exit;
                        if ($item->num_to_exit !== null) {
                            if ($object1->outs[count($child_tree)] !== null) {
                                $object1->outs[count($child_tree)] = (int)($object1->outs[count($child_tree)]) + (int)($item->num_to_exit);
                            } else {
                                $object1->outs[count($child_tree)] = (int)($item->num_to_exit);
                            }
                        }
                    }
                }
            }

            $duLieuTongVaoCacCua = $duLieuTongRaCacCua = $duLieuTrungBinhCacCua = array_fill(0, count($child_tree) + 1, null);
            $duLieuThuTuCacCua =  array_fill(0, count($child_tree), null);

            foreach ($duLieuTongVaoCacCua as $key => $val) {
                foreach ($duLieuChiTiet as $value) {
                    if ($value->ins[$key] != null) {
                        if ($duLieuTongVaoCacCua[$key]  === null)
                            $duLieuTongVaoCacCua[$key] = (int) $value->ins[$key];
                        else
                            $duLieuTongVaoCacCua[$key] = (int)$duLieuTongVaoCacCua[$key] +  (int) $value->ins[$key];
                    }
                    if ($value->outs[$key] != null) {
                        if ($duLieuTongRaCacCua[$key]  === null)
                            $duLieuTongRaCacCua[$key] = (int) $value->outs[$key];
                        else
                            $duLieuTongRaCacCua[$key] = (int)$duLieuTongRaCacCua[$key] +  (int) $value->outs[$key];
                    }
                }
            }

            foreach ($duLieuTongVaoCacCua as $key => $val) {
                $tb = 0;
                if ($duLieuTongVaoCacCua[$key] !== null) {
                    $tb = (int)$duLieuTongVaoCacCua[$key];
                }
                if ($duLieuTongRaCacCua[$key] !== null) {
                    $tb = $tb + (int)$duLieuTongRaCacCua[$key];
                }
                $duLieuTrungBinhCacCua[$key]  = (int) number_format(($tb / 2), 2, '.', '');
            }

            $sort = $duLieuTrungBinhCacCua;
            $duLieuTongRaCacCuaNew = $duLieuTrungBinhCacCua;
            array_pop($sort);
            array_pop($duLieuTongRaCacCuaNew);
            usort($sort, function ($a, $b) {
                return $b  < $a ?  -1 : 1;
            });


            foreach ($sort  as $key => $val) {
                foreach ($duLieuTongRaCacCuaNew as $i =>  $item) {
                    if ($item != null) {
                        if ($item == $val) {
                            $duLieuThuTuCacCua[$i] =  $key + 1;
                        }
                    }
                }
            }

            $newstr = str_replace("'", '', $start_date);
            $datetimea = new DateTime($newstr);
            $name = $datetimea->format('d_m_Y') . 'v' . rand(1, 1000);

            Excel::create('Monthly Traffic Report on ' . $name, function ($excel) use ($duLieuChiTiet, $parent_tree, $child_tree, $duLieuTongVaoCacCua, $duLieuTongRaCacCua, $duLieuTrungBinhCacCua, $duLieuThuTuCacCua) {
                $excel->setCreator('ACS')->setCompany('ACS Solution');
                $title1 = 'Monthly Traffic Report';
                $excel->sheet($title1, function ($sheet) use ($duLieuChiTiet, $parent_tree, $child_tree, $duLieuTongVaoCacCua, $duLieuTongRaCacCua, $duLieuTrungBinhCacCua, $duLieuThuTuCacCua) {

                    $sheet->setHeight([1 => 22, 2 => 22, 3 => 22, 4 => 22, 5 => 22, 6 => 22, 7 => 22]);
                    $sheet->getRowDimension(1)->setRowHeight(25);
                    // Font family
                    $sheet->setFontFamily('Times New Roman');
                    $sheet->setAllBorders('thin');
                    // $sheet->setBorder('A1:AC9', 'thin');
                    $sheet->setStyle(array(
                        'font' => array(
                            'name'      =>  'Times New Roman',
                            'size'      =>  12,
                            // 'bold'      =>  true
                        )
                    ));
                    $sheet->loadView(
                        'export.customer_daily_report',
                        [
                            'duLieuChiTiet' => $duLieuChiTiet,
                            'parent_tree' => $parent_tree,
                            'child_tree' => $child_tree,
                            'duLieuTongVaoCacCua' => $duLieuTongVaoCacCua,
                            'duLieuTongRaCacCua' => $duLieuTongRaCacCua,
                            'duLieuTrungBinhCacCua' => $duLieuTrungBinhCacCua,
                            'duLieuThuTuCacCua' => $duLieuThuTuCacCua,
                            'child_tree' => $child_tree
                        ]
                    );
                });
            })->store('xls', public_path('exports'));
            $file_name = 'Monthly Traffic Report on ' . $name . '.xls';
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
        return response()->json($file_name);
    }

    // Xuất excel Customer Yearly
    public function sp_report_poc_raw_data_by_year_export_excel(Request $request)
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

            $data = DB::select("exec sp_report_poc_raw_data_by_year $user_id, $organization_id, $site_id, $year, $start_time, $end_time");
            $menu_tree = DB::select("SELECT * FROM fc_get_site_in_role($organization_id,$user_id)");
            $organization =  Organization::find($organization_id);
            $org_name =  $organization->organization_name;
            if ($site_id  != 0) {
                $site = Site::find($site_id);
                $org_name  = $site->site_name;
            }
            $parent_tree = [];
            $child_tree = [];
            $duLieuChiTiet = [];

            $menu_tree = $this->tinh_phan_tu_con_trong_menu_cha($menu_tree, '0');

            foreach ($menu_tree as $key1 => $item) {
                if ($item->parent_id == '0') {
                    array_push($parent_tree, $item);
                } else  if ($item->parent_id != null) {
                    array_push($child_tree, $item);
                }
            }
            usort($parent_tree, function ($a, $b) {
                return $a->id < $b->id ? -1 : 1;
            });
            usort($child_tree, function ($a, $b) {
                return  $b->parent_id > $a->parent_id  ? -1 : 1;
            });

            $list_timeperiod = [];
            foreach ($data as $item) {

                if ($duLieuChiTiet !== null)
                    foreach ($duLieuChiTiet as $value) {
                        array_push($list_timeperiod, $value->time_period);
                    }

                if (array_search($item->time_period, $list_timeperiod) === false || $duLieuChiTiet == null) {
                    $ins = [];
                    $outs = [];
                    for ($i = 0; $i < count($child_tree) + 1; $i++) {
                        $ins[$i] = null;
                        $outs[$i] = null;
                    }

                    $object1 =  (object) array('time_period' => $item->time_period,  'ins' => $ins,  'outs' => $outs);
                    array_push($duLieuChiTiet, $object1);
                } else {
                    foreach ($duLieuChiTiet as $retl) {
                        if ($retl->time_period ==  $item->time_period)
                            $object1 =  $retl;
                    }
                }
                foreach ($child_tree as $key => $val) {
                    if ($val->id == $item->site_id) {
                        $object1->ins[$key]   = $item->num_to_enter;
                        if ($item->num_to_enter !== null) {
                            if ($object1->ins[count($child_tree)] !== null) {
                                $object1->ins[count($child_tree)] = (int)($object1->ins[count($child_tree)]) + (int)($item->num_to_enter);
                            } else {
                                $object1->ins[count($child_tree)] = (int)($item->num_to_enter);
                            }
                        }
                        $object1->outs[$key]  = $item->num_to_exit;
                        if ($item->num_to_exit !== null) {
                            if ($object1->outs[count($child_tree)] !== null) {
                                $object1->outs[count($child_tree)] = (int)($object1->outs[count($child_tree)]) + (int)($item->num_to_exit);
                            } else {
                                $object1->outs[count($child_tree)] = (int)($item->num_to_exit);
                            }
                        }
                    }
                }
            }

            $duLieuTongVaoCacCua = $duLieuTongRaCacCua = $duLieuTrungBinhCacCua = array_fill(0, count($child_tree) + 1, null);
            $duLieuThuTuCacCua =  array_fill(0, count($child_tree), null);

            foreach ($duLieuTongVaoCacCua as $key => $val) {
                foreach ($duLieuChiTiet as $value) {
                    if ($value->ins[$key] != null) {
                        if ($duLieuTongVaoCacCua[$key]  === null)
                            $duLieuTongVaoCacCua[$key] = (int) $value->ins[$key];
                        else
                            $duLieuTongVaoCacCua[$key] = (int)$duLieuTongVaoCacCua[$key] +  (int) $value->ins[$key];
                    }
                    if ($value->outs[$key] != null) {
                        if ($duLieuTongRaCacCua[$key]  === null)
                            $duLieuTongRaCacCua[$key] = (int) $value->outs[$key];
                        else
                            $duLieuTongRaCacCua[$key] = (int)$duLieuTongRaCacCua[$key] +  (int) $value->outs[$key];
                    }
                }
            }

            foreach ($duLieuTongVaoCacCua as $key => $val) {
                $tb = 0;
                if ($duLieuTongVaoCacCua[$key] !== null) {
                    $tb = (int)$duLieuTongVaoCacCua[$key];
                }
                if ($duLieuTongRaCacCua[$key] !== null) {
                    $tb = $tb + (int)$duLieuTongRaCacCua[$key];
                }
                $duLieuTrungBinhCacCua[$key]  = (int) number_format(($tb / 2), 2, '.', '');
            }

            $sort = $duLieuTrungBinhCacCua;
            $duLieuTongRaCacCuaNew = $duLieuTrungBinhCacCua;
            array_pop($sort);
            array_pop($duLieuTongRaCacCuaNew);
            usort($sort, function ($a, $b) {
                return $b  < $a ?  -1 : 1;
            });


            foreach ($sort  as $key => $val) {
                foreach ($duLieuTongRaCacCuaNew as $i =>  $item) {
                    if ($item != null) {
                        if ($item == $val) {
                            $duLieuThuTuCacCua[$i] =  $key + 1;
                        }
                    }
                }
            }

            $newstr = str_replace("'", '', $start_date);
            $datetimea = new DateTime($newstr);
            $name = $datetimea->format('d_m_Y') . 'v' . rand(1, 1000);

            Excel::create('Yealy Traffic Report on ' . $name, function ($excel) use ($duLieuChiTiet, $parent_tree, $child_tree, $duLieuTongVaoCacCua, $duLieuTongRaCacCua, $duLieuTrungBinhCacCua, $duLieuThuTuCacCua) {
                $excel->setCreator('ACS')->setCompany('ACS Solution');
                $title1 = 'Yealy Traffic Report';
                $excel->sheet($title1, function ($sheet) use ($duLieuChiTiet, $parent_tree, $child_tree, $duLieuTongVaoCacCua, $duLieuTongRaCacCua, $duLieuTrungBinhCacCua, $duLieuThuTuCacCua) {

                    $sheet->setHeight([1 => 22, 2 => 22, 3 => 22, 4 => 22, 5 => 22, 6 => 22, 7 => 22]);
                    $sheet->getRowDimension(1)->setRowHeight(25);
                    // Font family
                    $sheet->setFontFamily('Times New Roman');
                    $sheet->setAllBorders('thin');
                    // $sheet->setBorder('A1:AC9', 'thin');
                    $sheet->setStyle(array(
                        'font' => array(
                            'name'      =>  'Times New Roman',
                            'size'      =>  12,
                            // 'bold'      =>  true
                        )
                    ));
                    $sheet->loadView(
                        'export.customer_daily_report',
                        [
                            'duLieuChiTiet' => $duLieuChiTiet,
                            'parent_tree' => $parent_tree,
                            'child_tree' => $child_tree,
                            'duLieuTongVaoCacCua' => $duLieuTongVaoCacCua,
                            'duLieuTongRaCacCua' => $duLieuTongRaCacCua,
                            'duLieuTrungBinhCacCua' => $duLieuTrungBinhCacCua,
                            'duLieuThuTuCacCua' => $duLieuThuTuCacCua,
                            'child_tree' => $child_tree
                        ]
                    );
                });
            })->store('xls', public_path('exports'));
            $file_name = 'Yealy Traffic Report on ' . $name . '.xls';
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
        return response()->json($file_name);
    }

    // Tính chênh lệch 2 giá trị
    public function tinh_chenh_lech($value1, $value2)
    {
        $result = $value1 - $value2;
        return abs($result);
    }

    // Hàm đổi chỗ
    public function changeindex(&$a, &$b)
    {
        $c = $a;
        $a = $b;
        $b = $c;
    }

    // Sau 15 s sẽ xóa
    public function delete_excel(Request $request)
    {
        $name = $request->name_of_excel;
        $file_path = public_path('exports/') . $name;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        return response()->json('Đã xóa');
    }
    // Lấy cột tổng store compared
    public function get_column_excel(&$array, &$org_name1, &$org_name2, &$data1, &$data2, $avg = false)
    {
        $array[] = array(
            'Date/Time'   => ' ',
            $org_name1 . ' '    => $avg ?  $this->changeSecondsToformatTime((float) $data1 * 60) :  $data1,
            $org_name2    => $avg ?  $this->changeSecondsToformatTime((float) $data2 * 60) : $data2,
            'Difference'  => $data1 >= $data2  ? ($avg ?  $this->changeSecondsToformatTime(($data1 - $data2) * 60) : ($data1 - $data2))  : ($avg ?  $this->changeSecondsToformatTime(($data2 - $data1) * 60) : ($data2 - $data1))
        );
    }

    // Lấy các row store compared
    function get_more_column_excel(&$org_name1, &$org_name2, &$item,  &$itemsComapare,  &$key1,  &$visits, &$traffic, &$shopper,  &$passer,   &$kids,    &$turn_rate,  &$avg_time)
    {
        $visits[] = array(
            'Date/Time'       =>  $item->time_period,
            $org_name1 . ' '       =>  (int) $item->num_to_enter,
            $org_name2        =>  (int) $itemsComapare[$key1]->num_to_enter,
            'Difference'      => ($item->num_to_enter >= $itemsComapare[$key1]->num_to_enter ? ($item->num_to_enter - $itemsComapare[$key1]->num_to_enter) : ($itemsComapare[$key1]->num_to_enter - $item->num_to_enter))
        );

          $exits[] = array(
              'Date/Time'       =>  $item->time_period,
              $org_name1 . ' '       =>  (int) $item->num_to_exit,
              $org_name2        =>  (int) $itemsComapare[$key1]->num_to_exit,
              'Difference'      => ($item->num_to_exit >= $itemsComapare[$key1]->num_to_exit ? ($item->num_to_exit - $itemsComapare[$key1]->num_to_exit) : ($itemsComapare[$key1]->num_to_exit - $item->num_to_exit))
          );
        $traffic[] = array(
            'Date/Time'   => $item->time_period,
            $org_name1 . ' '   => (int) $item->traffic,
            $org_name2    => (int) $itemsComapare[$key1]->traffic,
            'Difference'  => ($item->traffic >= $itemsComapare[$key1]->traffic ? ($item->traffic - $itemsComapare[$key1]->traffic) : ($itemsComapare[$key1]->traffic - $item->traffic))
        );
        $shopper[] = array(
            'Date/Time'   => $item->time_period,
            $org_name1 . ' '   => (int) $item->shopper_visits,
            $org_name2    => (int) $itemsComapare[$key1]->shopper_visits,
            'Difference'  => ($item->shopper_visits >= $itemsComapare[$key1]->shopper_visits ? ($item->shopper_visits - $itemsComapare[$key1]->shopper_visits) : ($itemsComapare[$key1]->shopper_visits - $item->shopper_visits))
        );
        $passer[] = array(
            'Date/Time'   => $item->time_period,
            $org_name1 . ' '   => (int) $item->passer_by,
            $org_name2    => (int) $itemsComapare[$key1]->passer_by,
            'Difference'  => ($item->passer_by >= $itemsComapare[$key1]->passer_by ? ($item->passer_by - $itemsComapare[$key1]->passer_by) : ($itemsComapare[$key1]->passer_by - $item->passer_by))
        );
        $kids[] = array(
            'Date/Time'   => $item->time_period,
            $org_name1 . ' '   => (int) $item->kids_visits,
            $org_name2    => (int) $itemsComapare[$key1]->kids_visits,
            'Difference'  => ($item->kids_visits >= $itemsComapare[$key1]->kids_visits ? ($item->kids_visits - $itemsComapare[$key1]->kids_visits) : ($itemsComapare[$key1]->kids_visits - $item->kids_visits))
        );
        $turn_rate[] = array(
            'Date/Time'   => $item->time_period,
            $org_name1 . ' '   => (float) $item->turn_in_rate,
            $org_name2    => (float) $itemsComapare[$key1]->turn_in_rate,
            'Difference'  => ($item->turn_in_rate >= $itemsComapare[$key1]->turn_in_rate ? ($item->turn_in_rate - $itemsComapare[$key1]->turn_in_rate) : ($itemsComapare[$key1]->turn_in_rate - $item->turn_in_rate))
        );
        $avg_time[] = array(
            'Date/Time'   => $item->time_period,
            $org_name1 . ' '   =>   $this->changeSecondsToformatTime((float) $item->avg_time * 60),
            $org_name2    =>   $this->changeSecondsToformatTime((float) $itemsComapare[$key1]->avg_time * 60),
            'Difference'  => ($item->avg_time >= $itemsComapare[$key1]->avg_time ?  $this->changeSecondsToformatTime(($item->avg_time - $itemsComapare[$key1]->avg_time) * 60)  :    $this->changeSecondsToformatTime(($itemsComapare[$key1]->avg_time - $item->avg_time) * 60))
        );
    }

    /* ------------- Danh sách các header của xuẩt excel ----------------*/
    public function get_value_header_poc_in_out($start_date, $end_date, $start_time, $end_time, $org_name)
    {
        if ($start_date === $end_date) {
            $ngay =  Carbon::parse(str_replace('\'', '', $start_date))->format('d/m/Y');
        } else {
            $start = Carbon::parse(str_replace('\'', '', $start_date))->format('d/m/Y');
            $en   =  Carbon::parse(str_replace('\'', '', $end_date))->format('d/m/Y');
            $ngay = $start . ' _ ' .  $en;
        }
        $value_header = array(
            'value1' => $org_name,
            'value2' => str_replace('\'', '', $start_time) . ' - ' . str_replace('\'', '', $end_time),
            'value3' => $ngay
        );
        return $value_header;
    }

    public function get_value_header_store($start_date, $end_date, $start_time, $end_time, $org_name1, $org_name2)
    {
        if ($start_date === $end_date) {
            $ngay =  Carbon::parse(str_replace('\'', '', $start_date))->format('d/m/Y');
        } else {
            $start = Carbon::parse(str_replace('\'', '', $start_date))->format('d/m/Y');
            $en   =  Carbon::parse(str_replace('\'', '', $end_date))->format('d/m/Y');
            $ngay = $start . ' _ ' .  $en;
        }
        $value_header = array(
            'value1' => $org_name1 .  ' compare to ' . $org_name2,
            'value2' => str_replace('\'', '', $start_time) . ' - ' . str_replace('\'', '', $end_time),
            'value3' => $ngay
        );
        return $value_header;
    }

    public function get_value_header_time($start_date, $end_date, $start_date_compare, $end_date_compare, $start_time, $end_time, $org_name)
    {
        if ($start_date === $end_date) {
            $ngay1 = Carbon::parse(str_replace('\'', '', $start_date))->format('d/m/Y');
        } else {
            $start = Carbon::parse(str_replace('\'', '', $start_date))->format('d/m/Y');
            $en    =  Carbon::parse(str_replace('\'', '', $end_date))->format('d/m/Y');
            $ngay1  = $start . ' _ ' .  $en;
        }

        if ($start_date_compare === $end_date_compare) {
            $ngay2  =  Carbon::parse(str_replace('\'', '', $start_date_compare))->format('d/m/Y');
        } else {
            $start = Carbon::parse(str_replace('\'', '', $start_date_compare))->format('d/m/Y');
            $en    =  Carbon::parse(str_replace('\'', '', $end_date_compare))->format('d/m/Y');
            $ngay2 = $start . ' _ ' .  $en;
        }
        $value_header = array(
            'value1' => $org_name,
            'value2' => str_replace('\'', '', $start_time) . ' - ' . str_replace('\'', '', $end_time),
            'value3' => $ngay1, 'value4' => $ngay2
        );
        return $value_header;
    }

    public function get_sheet_first(&$title, &$excel, &$sheet, &$header, &$items)
    {
        $excel->sheet($title, function ($sheet) use ($items,  $header) {
            $sheet->cell('A1', function ($cell) use ($header) {
                $cell->setValue($header['dong1']);
            });
            $sheet->cell('A2', function ($cell) use ($header) {
                $cell->setValue($header['dong2']);
            });
            $sheet->cell('A3', function ($cell) use ($header) {
                $cell->setValue($header['dong3']);
            });
            // Cấu hình sheet
            $sheet->getStyle('A0')->getAlignment()->applyFromArray(array('horizontal' => 'center'));
            $sheet->setWidth(['A' => 18, 'B' => 18, 'C' => 18, 'D' => 18, 'E' => 20, 'F' => 18, 'G' => 18]);
            $sheet->setHeight(array(7 =>  27));
            $sheet->setStyle(array('font' => array('name' => 'Times New Roman', 'size' =>  13)));
            $sheet->setOrientation('landscape');
            $sheet->fromArray($items, NULL, 'A6', true, true);
        });
    }

    public function get_sheet_second(&$title, &$excel, &$sheet, &$header, &$items)
    {
        $excel->sheet($title, function ($sheet) use ($items, $header) {
            $sheet->cell('A1', function ($cell) use ($header) {
                $cell->setValue($header['dong2']);
            });
            $sheet->cell('A2', function ($cell) use ($header) {
                $cell->setValue($header['dong3']);
            });
            $sheet->cell('A3', function ($cell) use ($header) {
                $cell->setValue($header['dong4']);
            });
            $sheet->cell('A4', function ($cell) use ($header) {
                $cell->setValue($header['dong5']);
            });
            $sheet->getStyle('A0')->getAlignment()->applyFromArray(array('horizontal' => 'center'));
            $sheet->setWidth(['A' => 18, 'B' => 13, 'C' => 13, 'D' => 13, 'E' => 13,]);
            $sheet->setStyle(array('font' => array('name' => 'Times New Roman', 'size' =>  13)));
            $sheet->setHeight(array(7 =>  27));
            $sheet->setOrientation('landscape');
            $sheet->fromArray($items, NULL, 'A6', true, true);
        });
    }
}
