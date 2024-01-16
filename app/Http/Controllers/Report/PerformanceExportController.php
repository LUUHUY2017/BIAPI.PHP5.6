<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DateTime;
use App\Organization;
use App\Site;
use Illuminate\Support\Facades\DB;
use Excel;
use Error;
use File;

class PerformanceExportController extends Controller
{

    // Xuất excel cho visits, và metrics comparison
    public function sp_footfall_performance_data_by_site_export_excel(Request $request)
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
            $operation = $request->operation;
            $export = isset($request->export) ? $request->export : '';

            $items = DB::select("exec sp_general_report_data_in_out_by_site $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date, $end_date, $view_by,  $operation");
            $row = count($items);

            $index_not_list = DB::select("select * from  fc_get_index_module_not_org( $organization_id)");
            foreach ($index_not_list as $item) {
                $index_not[]   = $item->index_name;
            }
            // $index_not  =  ["Kids Visits", "PasserBy", "Traffic Flow"];

            $organization =  Organization::find($organization_id);
            $org_name =  $organization->organization_name;
            if ($site_id  != 0) {
                $site = Site::find($site_id);
                $org_name  = $site->site_name;
            }
            // Lấy tổng đánh giá để tính phần trăm
            $passer_by = 0;
            $total_num_to_enter = 0;
            $shopper_visits = 0;
            $kids_visits = 0;
            $turn_in_rate = 0;
            $total_traffic = 0;
            $total_avg_time = 0;
            $conversion_rate = 0;
            $total_atv = 0;
            $total_avg_item = 0;
            $total_sales_yield = 0;
            $total_transactions = 0;
            $total_sales = 0;
            $total_missed_sales = 0;
            $total_sales_hours = 0;
            $total_shopper_on_sh = 0;
            $total_sale_on_sh = 0;
            $loyal_visits = 0;
            $loyal_transactions = 0;
            $loyal_conversion = 0;
            $miss_loyal = 0;
            $cx_index = 0;
            $nps_index = 0;
            // $total_seconds = 0;
            $i = 0;
            foreach ($items as $item) {
                $passer_by  += (int) $item->passer_by;
                $total_num_to_enter  += (int) $item->num_to_enter;
                $kids_visits         += (int) $item->kids_visits;
                $shopper_visits      += (int) $item->shopper_visits;
                $turn_in_rate        += (float) $item->turn_in_rate;
                $total_traffic       += (int) $item->traffic;
                $total_avg_time      += (float) $item->avg_time;
                $conversion_rate     += (float) $item->conversion;
                $total_atv           += (float) $item->atv;
                $total_avg_item      += (float) $item->avg_item;
                $total_sales_yield   += (float) $item->sales_yield;
                $total_transactions  += (int) $item->transactions;
                $total_sales         += (int) $item->sales;
                $total_missed_sales  += (int) $item->missed_sales;
                $total_sales_hours   += (float) $item->sales_hour;
                $total_shopper_on_sh += (float) $item->shopper_on_s_h;
                $total_sale_on_sh    += (float) $item->sales_on_s_h;
                $loyal_visits        += (float) $item->loyal_visits;
                $loyal_transactions  += (float) $item->loyal_transactions;
                $loyal_conversion    += (float) $item->loyal_conversion;
                $miss_loyal          += $item->loyal_conversion > 0 ? (float) (100 - $item->loyal_conversion) : 0;
                $cx_index            += (float) $item->cx_index;
                $nps_index           += (float) $item->nps_index;
                // $total_seconds      += (float) $item->total_seconds;
                if (intval($item->avg_time) > 0)
                    $i++;
            }
            // if ($view_by === 'Hour') {
            //     $total_avg_time = ($total_num_to_enter) > 0 ? (float) number_format((($total_seconds / 60) / ($total_num_to_enter)), 2, '.', '') : 0;
            // } else {
            $total_avg_time = ($i) > 0 ? (float) number_format(($total_avg_time / ($i)), 2, '.', '') : 0;
            // }
            $turn_in_rate = ($row) > 0 ? (float) number_format(($turn_in_rate / ($row)), 2, '.', '') : 0;
            $total_atv = ($row) > 0 ? (float) number_format(($total_atv / ($row)), 2, '.', '') : 0;
            $conversion_rate = ($row) > 0 ? (float) number_format(($conversion_rate / ($row)), 2, '.', '') : 0;
            $loyal_visits = ($row) > 0 ? (float) number_format(($loyal_visits / ($row)), 2, '.', '') : 0;
            $loyal_transactions = ($row) > 0 ? (float) number_format(($loyal_transactions / ($row)), 2, '.', '') : 0;
            $loyal_conversion = ($row) > 0 ? (float) number_format(($loyal_conversion / ($row)), 2, '.', '') : 0;
            $miss_loyal = ($loyal_conversion) > 0 ? (float) number_format((100 - $loyal_conversion), 2, '.', '') : 0;
            $total_avg_item = ($row) > 0 ? (float) number_format(($total_avg_item / ($row)), 2, '.', '') : 0;
            $cx_index = ($row) > 0 ? (float) number_format(($cx_index / ($row)), 2, '.', '') : 0;
            $nps_index = ($row) > 0 ? (float) number_format(($nps_index / ($row)), 2, '.', '') : 0;
            $total_sales_hours = ($row) > 0 ? (float) number_format(($total_sales_hours / ($row)), 2, '.', '') : 0;
            $total_shopper_on_sh = ($row) > 0 ? (float) number_format(($total_shopper_on_sh / ($row)), 2, '.', '') : 0;
            $total_sale_on_sh = ($row) > 0 ? (float) number_format(($total_sale_on_sh / ($row)), 2, '.', '') : 0;
            if ($export === 'xuhuong') {
                $passer_by = ($row) > 0 ? (int) number_format(($passer_by / ($row)), 2, '.', '') : 0;
                $total_num_to_enter = ($row) > 0 ? (int) number_format(($total_num_to_enter / ($row)), 2, '.', '') : 0;
                $kids_visits = ($row) > 0 ? (int) number_format(($kids_visits / ($row)), 2, '.', '') : 0;
                $shopper_visits = ($row) > 0 ? (int) number_format(($shopper_visits / ($row)), 2, '.', '') : 0;
                $total_traffic = ($row) > 0 ? (int) number_format(($total_traffic / ($row)), 2, '.', '') : 0;
                $total_sales_yield = ($row) > 0 ? (int) number_format(($total_sales_yield / ($row)), 2, '.', '') : 0;
                $total_transactions = ($row) > 0 ? (int) number_format(($total_transactions / ($row)), 2, '.', '') : 0;
                $total_sales = ($row) > 0 ? (int) number_format(($total_sales / ($row)), 2, '.', '') : 0;
                $total_missed_sales = ($row) > 0 ? (int) number_format(($total_missed_sales / ($row)), 2, '.', '') : 0;
            }
            // Dòng tổng excel
            $items2[] = $items1[] = array(
                'Thời Gian'           =>  '',
                'PasserBy'            => $passer_by,
                'Visits'              => $total_num_to_enter,
                'Shoppers'      => $shopper_visits,
                'Turn in rate (%)'    => $turn_in_rate . ' %',
                'Traffic Flow'        => $total_traffic,
                'Kids Visits'         => $kids_visits,
                'Avg Time (min)'      => $this->changeSecondsToformatTime($total_avg_time * 60),
                'Conversion rate (%)'     => $conversion_rate . ' %',
                'ATV'                     => $total_atv,
                'Avg Items'               => $total_avg_item,
                'Sales Yield'             => $total_sales_yield,
                'Transactions'            => $total_transactions,
                'Sales'                   => $total_sales,
                'Missed Sales Opportunity' => $total_missed_sales,
                'Sales hours'                 => $total_sales_hours,
                'Shoppers on sales hour'      => $total_shopper_on_sh,
                'Sales on sales hour'         => $total_sale_on_sh,
                'Member Visits (%)'            => $loyal_visits . ' %',
                'Member Transactions (%)'      => $loyal_transactions . ' %',
                'Member CR (%)'   => $loyal_conversion . ' %',
                'Lost member CR (%)'       => $miss_loyal . ' %',
                'CX index (%)'                => $cx_index . ' %',
                'NPS index (%)'               => $nps_index . ' %',
            );
            foreach ($items as $item) {
                $items1[] = array(
                    'Thời Gian'           => $item->time_period,
                    'PasserBy'            => (int) $item->passer_by,
                    'Visits'              => (int) $item->num_to_enter,
                    'Shoppers'      => (int) $item->shopper_visits,
                    'Turn in rate (%)'    => (float) $item->turn_in_rate . ' %',
                    'Traffic Flow'        => (int) $item->traffic,
                    'Kids Visits'         => (int) $item->kids_visits,
                    'Avg Time (min)'      => $this->changeSecondsToformatTime((float) $item->avg_time * 60),
                    'Conversion rate (%)'     => (float) $item->conversion . ' %',
                    'ATV'                     => (float) $item->atv,
                    'Avg Items'               => (float) $item->avg_item,
                    'Sales Yield'             => (float) $item->sales_yield,
                    'Transactions'            => (int) $item->transactions,
                    'Sales'                   => (int) $item->sales,
                    'Missed Sales Opportunity' => (int) $item->missed_sales,
                    'Sales hours'                 => (float) $item->sales_hour,
                    'Shoppers on sales hour'      => (float) $item->shopper_on_s_h,
                    'Sales on sales hour'         => (float) $item->sales_on_s_h,
                    'Member Visits (%)'            => (float) $item->loyal_visits . ' %',
                    'Member Transactions (%)'      => (float) $item->loyal_transactions . ' %',
                    'Member CR (%)'   => (float) $item->loyal_conversion . ' %',
                    'Lost member CR (%)'       => $item->loyal_conversion > 0 ? 100 - $item->loyal_conversion . ' %' : 0 . ' %',
                    'CX index (%)'                => (float) $item->cx_index . ' %',
                    'NPS index (%)'               => (float) $item->nps_index . ' %',
                );
                $num_to_enter_per = ($item->num_to_enter) > 0 ? (float) number_format((($item->num_to_enter / ($total_num_to_enter)) * 100), 2, '.', '') : 0;
                $kids_visits_per = ($item->kids_visits) > 0 ? (float) number_format((($item->kids_visits / ($kids_visits)) * 100), 2, '.', '') : 0;
                $shopper_visits_per = ($item->shopper_visits) > 0 ? (float) number_format((($item->shopper_visits / ($shopper_visits)) * 100), 2, '.', '') : 0;
                $passer_by_per = ($item->passer_by) > 0 ? (float) number_format((($item->passer_by / ($passer_by)) * 100), 2, '.', '') : 0;
                $transactions_per     = ($item->transactions) > 0 ? (float) number_format((($item->transactions / ($total_transactions)) * 100), 2, '.', '') : 0;
                $sales_yield_per     = ($item->sales_yield) > 0 ? (float) number_format((($item->sales_yield / ($total_sales_yield)) * 100), 2, '.', '') : 0;
                $sales_per     = ($item->sales) > 0 ? (float) number_format((($item->sales / ($total_sales)) * 100), 2, '.', '') : 0;
                $traffic_per     = ($item->traffic) > 0 ? (float) number_format((($item->traffic / ($total_traffic)) * 100), 2, '.', '') : 0;
                $missed_sales_per     = ($item->missed_sales) > 0 ? (float) number_format((($item->missed_sales / ($total_missed_sales)) * 100), 2, '.', '') : 0;

                $items2[] = array(
                    'Thời Gian'           => $item->time_period,
                    'PasserBy'            => (float) $passer_by_per . ' %',
                    'Visits'              => (float) $num_to_enter_per . ' %',
                    'Shoppers'      => (float) $shopper_visits_per . ' %',
                    'Turn in rate (%)'    => (float) $item->turn_in_rate . ' %',
                    'Traffic Flow'        => (float) $traffic_per . ' %',
                    'Kids Visits'         => (float) $kids_visits_per . ' %',
                    'Avg Time (min)'      => $this->changeSecondsToformatTime((float) $item->avg_time * 60),
                    'Conversion rate (%)'     => (float) $item->conversion . ' %',
                    'ATV'                     => (float) $item->atv . ' %',
                    'Avg Items'               => (float) $item->avg_item . ' %',
                    'Sales Yield'             => (float) $sales_yield_per . ' %',
                    'Transactions'            => (float) $transactions_per . ' %',
                    'Sales'                   => (float) $sales_per . ' %',
                    'Missed Sales Opportunity' => (float) $missed_sales_per . ' %',
                    'Sales hours'                 => (float) $item->sales_hour . ' %',
                    'Shoppers on sales hour'      => (float) $item->shopper_on_s_h . ' %',
                    'Sales on sales hour'         => (float) $item->sales_on_s_h . ' %',
                    'Member Visits (%)'            => (float) $item->loyal_visits . ' %',
                    'Member Transactions (%)'      => (float) $item->loyal_transactions . ' %',
                    'Member CR (%)'   => (float) $item->loyal_conversion . ' %',
                    'Lost member CR (%)'       => $item->loyal_conversion > 0 ? (float) 100 - $item->loyal_conversion . ' %' : 0 . ' %',
                    'CX index (%)'                => (float) $item->cx_index . ' %',
                    'NPS index (%)'               => (float) $item->nps_index . ' %',
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

            Excel::create('PERFORMANCE_VISITS_' . $name, function ($excel) use ($export, $items1, $items2, $value_header) {
                $header = array(
                    'dong1' => 'Kết quả đo lường lưu lượng ra vào tại : ' . $value_header['value1'] . ' ',
                    'dong2' => 'Kết quả đo lường lưu lượng ra vào tại : ' . $value_header['value1'] . ' ',
                    'dong3' => 'Địa điểm: ' . $value_header['value1'] . '',
                    'dong4' => 'Thời gian:  ' . $value_header['value2'] . '  ',
                    'dong5' => 'Ngày:  ' . $value_header['value3'] . '  ',
                );
                $excel->setCreator('ACS')->setCompany('ACS Solution');
                $title1 = 'Số lượng';
                $title2 = 'Phần trăm';
                $this->get_sheet_second($title1, $excel, $sheet, $header, $items1);
                if ($export === 'sosanh') {
                    $this->get_sheet_second($title2, $excel, $sheet, $header, $items2);
                }
            })->store('xls', public_path('exports'));
            $file_name = 'PERFORMANCE_VISITS_' . $name . '.xls';
        } catch (\Exception $exception) {
            return 'none';
        }
        return response()->json($file_name);
    }

    // Xuất excel phần so sánh 2 cửa hàng
    public function sp_footfall_performance_store_comparison_export_excel(Request $request)
    {
        // try {
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

        $items = DB::select("exec sp_general_report_data_in_out_by_site $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date, $end_date, $view_by, $operation");
        $itemsComapare = DB::select("exec sp_general_report_data_in_out_by_site $user_id, $organization_id_compare, $site_id_compare, $start_time, $end_time, $start_date, $end_date, $view_by, $operation");
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
        $shopper_visits1 = 0;
        $kids_visits1 = 0;
        $turn_in_rate1 = 0;
        $total_traffic1 = 0;
        $total_avg_time1 = 0;
        $conversion_rate1 = 0;
        $total_atv1 = 0;
        $total_avg_item1 = 0;
        $total_sales_yield1 = 0;
        $total_transactions1 = 0;
        $total_sales1 = 0;
        $total_missed_sales1 = 0;
        $loyal_visits1 = 0;
        $loyal_transactions1 = 0;
        $loyal_conversion1 = 0;
        $miss_loyal1 = 0;
        $cx_index1 = 0;
        $nps_index1 = 0;
        $total_sales_hours1 = 0;
        $total_shopper_on_sh1 = 0;
        $total_sale_on_sh1 = 0;

        $passer_by2 = 0;
        $total_num_to_enter2 = 0;
        $shopper_visits2 = 0;
        $kids_visits2 = 0;
        $turn_in_rate2 = 0;
        $total_traffic2 = 0;
        $total_avg_time2 = 0;
        $conversion_rate2 = 0;
        $total_atv2 = 0;
        $total_avg_item2 = 0;
        $total_sales_yield2 = 0;
        $total_transactions2 = 0;
        $total_sales2 = 0;
        $total_missed_sales2 = 0;
        $loyal_visits2 = 0;
        $loyal_transactions2 = 0;
        $loyal_conversion2 = 0;
        $miss_loyal2 = 0;
        $cx_index2 = 0;
        $nps_index2 = 0;
        $total_sales_hours2 = 0;
        $total_shopper_on_sh2 = 0;
        $total_sale_on_sh2 = 0;
        // $total_seconds1 = 0;
        // $total_seconds2 = 0;
        $i = 0;
        $i_compared = 0;

        foreach ($items as $key1 => $item) {
            $this->get_total_store($item, $passer_by1, $total_num_to_enter1, $kids_visits1, $shopper_visits1, $turn_in_rate1, $total_traffic1, $total_avg_time1, $conversion_rate1, $total_atv1, $total_avg_item1, $total_sales_yield1, $total_transactions1, $total_sales1, $total_missed_sales1, $loyal_visits1, $loyal_transactions1, $loyal_conversion1, $miss_loyal1, $cx_index1, $nps_index1, $total_sales_hours1, $total_shopper_on_sh1, $total_sale_on_sh1);
            $this->get_total_store($itemsComapare[$key1], $passer_by2, $total_num_to_enter2, $kids_visits2, $shopper_visits2, $turn_in_rate2, $total_traffic2, $total_avg_time2, $conversion_rate2, $total_atv2, $total_avg_item2, $total_sales_yield2, $total_transactions2, $total_sales2, $total_missed_sales2, $loyal_visits2, $loyal_transactions2, $loyal_conversion2, $miss_loyal2, $cx_index2, $nps_index2, $total_sales_hours2, $total_shopper_on_sh2, $total_sale_on_sh2);
            if ((float) $item->avg_time > 0)
                $i++;
            if ((float) $itemsComapare[$key1]->avg_time > 0)
                $i_compared++;
        }


        // if ($view_by === 'Hour') {
        //     $total_avg_time1 = ($total_num_to_enter1) > 0 ? (float) number_format((($total_seconds1 / 60) / ($total_num_to_enter1)), 2, '.', '') : 0;
        //     $total_avg_time2 = ($total_num_to_enter2) > 0 ? (float) number_format((($total_seconds2 / 60) / ($total_num_to_enter2)), 2, '.', '') : 0;
        // } else {
        $total_avg_time1 = ($i) > 0 ? (float) number_format(($total_avg_time1 / ($i)), 2, '.', '') : 0;
        $total_avg_time2 = ($i_compared) > 0 ? (float) number_format(($total_avg_time2 / ($i_compared)), 2, '.', '') : 0;
        // }

        $turn_in_rate1 = ($row) > 0 ? (float) number_format(($turn_in_rate1 / ($row)), 2, '.', '') : 0;
        $total_atv1 = ($row) > 0 ? (float) number_format(($total_atv1 / ($row)), 2, '.', '') : 0;
        $conversion_rate1 = ($row) > 0 ? (float) number_format(($conversion_rate1 / ($row)), 2, '.', '') : 0;
        $loyal_visits1 = ($row) > 0 ? (float) number_format(($loyal_visits1 / ($row)), 2, '.', '') : 0;
        $loyal_transactions1 = ($row) > 0 ? (float) number_format(($loyal_transactions1 / ($row)), 2, '.', '') : 0;
        $loyal_conversion1 = ($row) > 0 ? (float) number_format(($loyal_conversion1 / ($row)), 2, '.', '') : 0;
        $miss_loyal1 = ($loyal_conversion1) > 0 ? (float) number_format((100 - $loyal_conversion1), 2, '.', '') : 0;
        $total_avg_item1 = ($row) > 0 ? (float) number_format(($total_avg_item1 / ($row)), 2, '.', '') : 0;
        $cx_index1 = ($row) > 0 ? (float) number_format(($cx_index1 / ($row)), 2, '.', '') : 0;
        $nps_index1 = ($row) > 0 ? (float) number_format(($nps_index1 / ($row)), 2, '.', '') : 0;
        $total_sales_hours1 = ($row) > 0 ? (float) number_format(($total_sales_hours1 / ($row)), 2, '.', '') : 0;
        $total_shopper_on_sh1 = ($row) > 0 ? (float) number_format(($total_shopper_on_sh1 / ($row)), 2, '.', '') : 0;
        $total_sale_on_sh1 = ($row) > 0 ? (float) number_format(($total_sale_on_sh1 / ($row)), 2, '.', '') : 0;

        $turn_in_rate2 = ($row) > 0 ? (float) number_format(($turn_in_rate2 / ($row)), 2, '.', '') : 0;
        $total_atv2 = ($row) > 0 ? (float) number_format(($total_atv2 / ($row)), 2, '.', '') : 0;
        $conversion_rate2 = ($row) > 0 ? (float) number_format(($conversion_rate2 / ($row)), 2, '.', '') : 0;
        $loyal_visits2 = ($row) > 0 ? (float) number_format(($loyal_visits2 / ($row)), 2, '.', '') : 0;
        $loyal_transactions2 = ($row) > 0 ? (float) number_format(($loyal_transactions2 / ($row)), 2, '.', '') : 0;
        $loyal_conversion2 = ($row) > 0 ? (float) number_format(($loyal_conversion2 / ($row)), 2, '.', '') : 0;
        $miss_loyal2 = ($loyal_conversion2) > 0 ? (float) number_format((200 - $loyal_conversion2), 2, '.', '') : 0;
        $total_avg_item2 = ($row) > 0 ? (float) number_format(($total_avg_item2 / ($row)), 2, '.', '') : 0;
        $cx_index2 = ($row) > 0 ? (float) number_format(($cx_index2 / ($row)), 2, '.', '') : 0;
        $nps_index2 = ($row) > 0 ? (float) number_format(($nps_index2 / ($row)), 2, '.', '') : 0;
        $total_sales_hours2 = ($row) > 0 ? (float) number_format(($total_sales_hours2 / ($row)), 2, '.', '') : 0;
        $total_shopper_on_sh2 = ($row) > 0 ? (float) number_format(($total_shopper_on_sh2 / ($row)), 2, '.', '') : 0;
        $total_sale_on_sh2 = ($row) > 0 ? (float) number_format(($total_sale_on_sh2 / ($row)), 2, '.', '') : 0;
        if ($export === 'xuhuong') {
            $passer_by1 = ($row) > 0 ? (int) number_format(($passer_by1 / ($row)), 2, '.', '') : 0;
            $total_num_to_enter1 = ($row) > 0 ? (int) number_format(($total_num_to_enter1 / ($row)), 2, '.', '') : 0;
            $kids_visits1 = ($row) > 0 ? (int) number_format(($kids_visits1 / ($row)), 2, '.', '') : 0;
            $shopper_visits1 = ($row) > 0 ? (int) number_format(($shopper_visits1 / ($row)), 2, '.', '') : 0;
            $total_traffic1 = ($row) > 0 ? (int) number_format(($total_traffic1 / ($row)), 2, '.', '') : 0;
            $total_sales_yield1 = ($row) > 0 ? (int) number_format(($total_sales_yield1 / ($row)), 2, '.', '') : 0;
            $total_transactions1 = ($row) > 0 ? (int) number_format(($total_transactions1 / ($row)), 2, '.', '') : 0;
            $total_sales1 = ($row) > 0 ? (int) number_format(($total_sales1 / ($row)), 2, '.', '') : 0;
            $total_missed_sales1 = ($row) > 0 ? (int) number_format(($total_missed_sales1 / ($row)), 2, '.', '') : 0;

            $passer_by2 = ($row) > 0 ? (int) number_format(($passer_by2 / ($row)), 2, '.', '') : 0;
            $total_num_to_enter2 = ($row) > 0 ? (int) number_format(($total_num_to_enter2 / ($row)), 2, '.', '') : 0;
            $kids_visits2 = ($row) > 0 ? (int) number_format(($kids_visits2 / ($row)), 2, '.', '') : 0;
            $shopper_visits2 = ($row) > 0 ? (int) number_format(($shopper_visits2 / ($row)), 2, '.', '') : 0;
            $total_traffic2 = ($row) > 0 ? (int) number_format(($total_traffic2 / ($row)), 2, '.', '') : 0;
            $total_sales_yield2 = ($row) > 0 ? (int) number_format(($total_sales_yield2 / ($row)), 2, '.', '') : 0;
            $total_transactions2 = ($row) > 0 ? (int) number_format(($total_transactions2 / ($row)), 2, '.', '') : 0;
            $total_sales2 = ($row) > 0 ? (int) number_format(($total_sales2 / ($row)), 2, '.', '') : 0;
            $total_missed_sales2 = ($row) > 0 ? (int) number_format(($total_missed_sales2 / ($row)), 2, '.', '') : 0;
        }
        $this->get_column_excel($visits, $org_name1, $org_name2, $total_num_to_enter1, $total_num_to_enter2);
        $this->get_column_excel($traffic, $org_name1, $org_name2, $total_traffic1, $total_traffic2);
        $this->get_column_excel($shopper, $org_name1, $org_name2, $shopper_visits1, $shopper_visits2);
        $this->get_column_excel($passer, $org_name1, $org_name2, $passer_by1, $passer_by2);
        $this->get_column_excel($kids, $org_name1, $org_name2, $kids_visits1, $kids_visits2);
        $this->get_column_excel($turn_rate, $org_name1, $org_name2, $turn_in_rate1, $turn_in_rate2);
        $this->get_column_excel($avg_time, $org_name1, $org_name2, $total_avg_time1, $total_avg_time2, true);
        $this->get_column_excel($conversion, $org_name1, $org_name2, $conversion_rate1, $conversion_rate2);
        $this->get_column_excel($atv, $org_name1, $org_name2, $total_atv1, $total_atv2);
        $this->get_column_excel($avg_item, $org_name1, $org_name2, $total_avg_item1, $total_avg_item2);
        $this->get_column_excel($sales_yield, $org_name1, $org_name2, $total_sales_yield1, $total_sales_yield2);
        $this->get_column_excel($transactions, $org_name1, $org_name2, $total_transactions1, $total_transactions2);
        $this->get_column_excel($sales, $org_name1, $org_name2, $total_sales1, $total_sales2);
        $this->get_column_excel($missed_sales, $org_name1, $org_name2, $total_missed_sales1, $total_missed_sales2);
        $this->get_column_excel($sales_hour, $org_name1, $org_name2, $total_sales_hours1, $total_sales_hours2);
        $this->get_column_excel($shopper_on_s_h, $org_name1, $org_name2, $total_shopper_on_sh1, $total_shopper_on_sh2);
        $this->get_column_excel($sales_on_s_h, $org_name1, $org_name2, $total_sale_on_sh1, $total_sale_on_sh2);
        $this->get_column_excel($loyal_visits, $org_name1, $org_name2, $loyal_visits1, $loyal_visits2);
        $this->get_column_excel($loyal_transactions, $org_name1, $org_name2, $loyal_transactions1, $loyal_transactions2);
        $this->get_column_excel($loyal_conversion, $org_name1, $org_name2, $loyal_conversion1, $loyal_conversion2);
        $this->get_column_excel($miss_loyal, $org_name1, $org_name2, $miss_loyal1, $miss_loyal2);
        $this->get_column_excel($cx_index, $org_name1, $org_name2, $cx_index1, $cx_index2);
        $this->get_column_excel($nps_index, $org_name1, $org_name2, $nps_index1, $nps_index2);


        foreach ($items as $key1 => $item) {
            $this->get_more_column_excel($org_name1, $org_name2, $item, $itemsComapare, $key1, $visits, $traffic, $shopper, $passer, $kids, $turn_rate, $avg_time, $conversion, $atv, $avg_item, $sales_yield, $transactions, $sales, $missed_sales, $loyal_visits, $loyal_transactions, $loyal_conversion, $miss_loyal, $cx_index, $nps_index, $sales_hour, $shopper_on_s_h, $sales_on_s_h);
        }
        $name = Carbon::today()->format('d_m_Y') . 'v' . rand(1, 1000);
        $value_header =  $this->get_value_header_store($start_date, $end_date, $start_time, $end_time, $org_name1, $org_name2);
        Excel::create('PERFORMANCE_STORE_COMPARISON_' . $name, function ($excel) use ($visits, $traffic, $shopper, $passer, $kids, $turn_rate, $avg_time, $conversion, $atv, $avg_item, $sales_yield, $transactions, $sales, $missed_sales, $loyal_visits, $loyal_transactions, $loyal_conversion, $miss_loyal, $cx_index, $nps_index, $sales_hour, $shopper_on_s_h, $sales_on_s_h, $value_header, $index_not) {
            $header = array(
                'dong1' => 'Kết quả so sánh tổng lượng khách ra vào tại : ' . $value_header['value1'] . ' ',
                'dong2' => 'Thời gian:  ' . $value_header['value2'] . '  ',
                'dong3' => 'Ngày:  ' . $value_header['value3'] . '  ',
            );
            $excel->setCreator('ACS')->setCompany('ACS Solution');
            $Passer_title = 'PasserBy';
            $visits_title = 'Visits';
            $shopper_title = 'Shoppers';
            $turn_rate_title = 'Turn in rate (%)';
            $kids_title = 'Kids Visits';
            $avg_time_title = 'Avg Time (min)';
            $traffic_title = 'Traffic Flow';
            $conversion_rate_title = 'Conversion rate (%)';
            $atv_title = 'ATV';
            $avg_item_title = 'Avg Items';
            $sales_yield_title = 'Sales Yield';
            $transactions_title = 'Transactions';
            $sales_title = 'Sales';
            $missed_sales_title = 'Missed Sales Opportunity';
            $sales_hour_title = 'Sales hours';
            $shopper_on_s_h_title = 'Shoppers on sales hour';
            $sales_on_s_h_title = 'Sales on sales hour';
            $loyal_visits_title = 'Member Visits (%)';
            $loyal_transactions_title = 'Member Transactions (%)';
            $loyal_conversion_title = 'Member CR (%)';
            $miss_loyal_title = 'Lost member CR (%)';
            $cx_index_title = 'CX index (%)';
            $nps_index_title = 'NPS index (%)';
            if (!in_array('PasserBy', $index_not, true))
                $this->create_new_sheet($Passer_title, $excel, $sheet, $header, $passer);
            if (!in_array('Visits', $index_not, true))
                $this->create_new_sheet($visits_title, $excel, $sheet, $header, $visits);
            if (!in_array('Shoppers', $index_not, true))
                $this->create_new_sheet($shopper_title, $excel, $sheet, $header, $shopper);
            if (!in_array('Turn in rate (%)', $index_not, true))
                $this->create_new_sheet($turn_rate_title, $excel, $sheet, $header, $turn_rate);
            if (!in_array('Kids Visits', $index_not, true))
                $this->create_new_sheet($kids_title, $excel, $sheet, $header, $kids);
            if (!in_array('Avg Time (min)', $index_not, true))
                $this->create_new_sheet($avg_time_title, $excel, $sheet, $header, $avg_time);
            if (!in_array('Traffic Flow', $index_not, true))
                $this->create_new_sheet($traffic_title, $excel, $sheet, $header, $traffic);

            if (!in_array('Conversion rate (%)', $index_not, true))
                $this->create_new_sheet($conversion_rate_title, $excel, $sheet, $header, $conversion);
            if (!in_array('ATV', $index_not, true))
                $this->create_new_sheet($atv_title, $excel, $sheet, $header, $atv);
            if (!in_array('Avg Items', $index_not, true))
                $this->create_new_sheet($avg_item_title, $excel, $sheet, $header, $avg_item);
            if (!in_array('Sales Yield', $index_not, true))
                $this->create_new_sheet($sales_yield_title, $excel, $sheet, $header, $sales_yield);
            if (!in_array('Transactions', $index_not, true))
                $this->create_new_sheet($transactions_title, $excel, $sheet, $header, $transactions);
            if (!in_array('Sales', $index_not, true))
                $this->create_new_sheet($sales_title, $excel, $sheet, $header, $sales);
            if (!in_array('Missed Sales Opportunity', $index_not, true))
                $this->create_new_sheet($missed_sales_title, $excel, $sheet, $header, $missed_sales);
            if (!in_array('Sales hours', $index_not, true))
                $this->create_new_sheet($sales_hour_title, $excel, $sheet, $header, $sales_hour);
            if (!in_array('Shoppers on sales hour', $index_not, true))
                $this->create_new_sheet($shopper_on_s_h_title, $excel, $sheet, $header, $shopper_on_s_h);
            if (!in_array('Sales on sales hour', $index_not, true))
                $this->create_new_sheet($sales_on_s_h_title, $excel, $sheet, $header, $sales_on_s_h);
            if (!in_array('Member Visits (%)', $index_not, true))
                $this->create_new_sheet($loyal_visits_title, $excel, $sheet, $header, $loyal_visits);
            if (!in_array('Member Transactions (%)', $index_not, true))
                $this->create_new_sheet($loyal_transactions_title, $excel, $sheet, $header, $loyal_transactions);
            if (!in_array('Member CR (%)', $index_not, true))
                $this->create_new_sheet($loyal_conversion_title, $excel, $sheet, $header, $loyal_conversion);
            if (!in_array('Lost member CR (%)', $index_not, true))
                $this->create_new_sheet($miss_loyal_title, $excel, $sheet, $header, $miss_loyal);
            if (!in_array('CX index (%)', $index_not, true))
                $this->create_new_sheet($cx_index_title, $excel, $sheet, $header, $cx_index);
            if (!in_array('NPS index (%)', $index_not, true))
                $this->create_new_sheet($nps_index_title, $excel, $sheet, $header, $nps_index);
        })->store('xls', public_path('exports'));

        $file_name = 'PERFORMANCE_STORE_COMPARISON_' . $name . '.xls';
        // } catch (\Exception $exception) {
        //     return 'none';
        // }
        return response()->json($file_name);
    }

    public function get_total_store(&$item, &$passer_by, &$total_num_to_enter, &$kids_visits, &$shopper_visits, &$turn_in_rate, &$total_traffic, &$total_avg_time, &$conversion_rate, &$total_atv, &$total_avg_item, &$total_sales_yield, &$total_transactions, &$total_sales, &$total_missed_sales, &$loyal_visits, &$loyal_transactions, &$loyal_conversion, &$miss_loyal, &$cx_index, &$nps_index, &$sales_hour, &$shopper_on_s_h, &$sales_on_s_h)
    {
        $passer_by  += (int) $item->passer_by;
        $total_num_to_enter  += (int) $item->num_to_enter;
        $kids_visits         += (int) $item->kids_visits;
        $shopper_visits      += (int) $item->shopper_visits;
        $turn_in_rate        += (float) $item->turn_in_rate;
        $total_traffic       += (int) $item->traffic;
        $total_avg_time      +=  (float) $item->avg_time;
        $conversion_rate     += (float) $item->conversion;
        $total_atv           += (float) $item->atv;
        $total_avg_item      +=  (float) $item->avg_item;
        $total_sales_yield   += (float) $item->sales_yield;
        $total_transactions  += (int) $item->transactions;
        $total_sales         += (int) $item->sales;
        $total_missed_sales  += (int) $item->missed_sales;
        $sales_hour          += (float) $item->sales_hour;
        $shopper_on_s_h      += (float) $item->shopper_on_s_h;
        $sales_on_s_h        += (float) $item->sales_on_s_h;
        $loyal_visits        += (float) $item->loyal_visits;
        $loyal_transactions  += (float) $item->loyal_transactions;
        $loyal_conversion    += (float) $item->loyal_conversion;
        $miss_loyal          += $item->loyal_conversion > 0 ? (float) (100 - $item->loyal_conversion) : 0;
        $cx_index            += (float) $item->cx_index;
        $nps_index           += (float) $item->nps_index;
        // $total_seconds           += (float) $item->total_seconds;

    }

    // Lấy cột tổng store compared
    public function get_column_excel(&$array, &$org_name1, &$org_name2, &$data1, &$data2, $avg = false)
    {
        $array[] = array(
            'Thời Gian'   => ' ',
            $org_name1 . ' '    => $avg ?  $this->changeSecondsToformatTime((float) $data1 * 60) :  $data1,
            $org_name2    => $avg ?  $this->changeSecondsToformatTime((float) $data2 * 60) : $data2,
            'Chênh Lệch'  => $data1 >= $data2  ? ($avg ?  $this->changeSecondsToformatTime(($data1 - $data2) * 60) : ($data1 - $data2))  : ($avg ?  $this->changeSecondsToformatTime(($data2 - $data1) * 60) : ($data2 - $data1))
        );
    }

    // Lấy các row store compared
    function get_more_column_excel(&$org_name1, &$org_name2, &$item, &$itemsComapare, &$key1, &$visits, &$traffic, &$shopper, &$passer, &$kids, &$turn_rate, &$avg_time, &$conversion, &$atv, &$avg_item, &$sales_yield, &$transactions, &$sales, &$missed_sales, &$loyal_visits, &$loyal_transactions, &$loyal_conversion, &$miss_loyal, &$cx_index, &$nps_index, &$sales_hour, &$shopper_on_s_h, &$sales_on_s_h)
    {
        $visits[] = array(
            'Thời Gian'       =>  $item->time_period,
            $org_name1 . ' '      =>  (int) $item->num_to_enter,
            $org_name2        =>  (int) $itemsComapare[$key1]->num_to_enter,
            'Chênh Lệch'      => ($item->num_to_enter >= $itemsComapare[$key1]->num_to_enter ? ($item->num_to_enter - $itemsComapare[$key1]->num_to_enter) : ($itemsComapare[$key1]->num_to_enter - $item->num_to_enter))
        );
        $traffic[] = array(
            'Thời Gian'   => $item->time_period,
            $org_name1 . ' '  => (int) $item->traffic,
            $org_name2    => (int) $itemsComapare[$key1]->traffic,
            'Chênh Lệch'  => ($item->traffic >= $itemsComapare[$key1]->traffic ? ($item->traffic - $itemsComapare[$key1]->traffic) : ($itemsComapare[$key1]->traffic - $item->traffic))
        );
        $shopper[] = array(
            'Thời Gian'   => $item->time_period,
            $org_name1 . ' '  => (int) $item->shopper_visits,
            $org_name2    => (int) $itemsComapare[$key1]->shopper_visits,
            'Chênh Lệch'  => ($item->shopper_visits >= $itemsComapare[$key1]->shopper_visits ? ($item->shopper_visits - $itemsComapare[$key1]->shopper_visits) : ($itemsComapare[$key1]->shopper_visits - $item->shopper_visits))
        );
        $passer[] = array(
            'Thời Gian'   => $item->time_period,
            $org_name1 . ' '  => (int) $item->passer_by,
            $org_name2    => (int) $itemsComapare[$key1]->passer_by,
            'Chênh Lệch'  => ($item->passer_by >= $itemsComapare[$key1]->passer_by ? ($item->passer_by - $itemsComapare[$key1]->passer_by) : ($itemsComapare[$key1]->passer_by - $item->passer_by))
        );
        $kids[] = array(
            'Thời Gian'   => $item->time_period,
            $org_name1 . ' '  => (int) $item->kids_visits,
            $org_name2    => (int) $itemsComapare[$key1]->kids_visits,
            'Chênh Lệch'  => ($item->kids_visits >= $itemsComapare[$key1]->kids_visits ? ($item->kids_visits - $itemsComapare[$key1]->kids_visits) : ($itemsComapare[$key1]->kids_visits - $item->kids_visits))
        );
        $turn_rate[] = array(
            'Thời Gian'   => $item->time_period,
            $org_name1 . ' '  => (float) $item->turn_in_rate,
            $org_name2    => (float) $itemsComapare[$key1]->turn_in_rate,
            'Chênh Lệch'  => ($item->turn_in_rate >= $itemsComapare[$key1]->turn_in_rate ? ($item->turn_in_rate - $itemsComapare[$key1]->turn_in_rate) : ($itemsComapare[$key1]->turn_in_rate - $item->turn_in_rate))
        );
        $avg_time[] = array(
            'Thời Gian'   => $item->time_period,
            $org_name1 . ' '  => $this->changeSecondsToformatTime((float) $item->avg_time * 60),
            $org_name2    => $this->changeSecondsToformatTime((float) $itemsComapare[$key1]->avg_time * 60),
            'Chênh Lệch'  => ($item->avg_time >= $itemsComapare[$key1]->avg_time ?  $this->changeSecondsToformatTime(($item->avg_time - $itemsComapare[$key1]->avg_time) * 60)  :    $this->changeSecondsToformatTime(($itemsComapare[$key1]->avg_time - $item->avg_time) * 60))
        );
        $conversion[] = array(
            'Thời Gian'   => $item->time_period,
            $org_name1 . ' '  => (float) $item->conversion,
            $org_name2    => (float) $itemsComapare[$key1]->conversion,
            'Chênh Lệch'  => ($item->conversion >= $itemsComapare[$key1]->conversion ? ($item->conversion - $itemsComapare[$key1]->conversion) : ($itemsComapare[$key1]->conversion - $item->conversion))
        );
        $atv[] = array(
            'Thời Gian'   => $item->time_period,
            $org_name1 . ' '  => (float) $item->atv,
            $org_name2    => (float) $itemsComapare[$key1]->atv,
            'Chênh Lệch'  => ($item->atv >= $itemsComapare[$key1]->atv ? ($item->atv - $itemsComapare[$key1]->atv) : ($itemsComapare[$key1]->atv - $item->atv))
        );
        $avg_item[] = array(
            'Thời Gian'   => $item->time_period,
            $org_name1 . ' '  => (float) $item->avg_item,
            $org_name2    => (float) $itemsComapare[$key1]->avg_item,
            'Chênh Lệch'  => ($item->avg_item >= $itemsComapare[$key1]->avg_item ? ($item->avg_item - $itemsComapare[$key1]->avg_item) : ($itemsComapare[$key1]->avg_item - $item->avg_item))
        );
        $sales_yield[] = array(
            'Thời Gian'   => $item->time_period,
            $org_name1 . ' '  => (float) $item->sales_yield,
            $org_name2    => (float) $itemsComapare[$key1]->sales_yield,
            'Chênh Lệch'  => ($item->sales_yield >= $itemsComapare[$key1]->sales_yield ? ($item->sales_yield - $itemsComapare[$key1]->sales_yield) : ($itemsComapare[$key1]->sales_yield - $item->sales_yield))
        );
        $transactions[] = array(
            'Thời Gian'   => $item->time_period,
            $org_name1 . ' '  => (float) $item->transactions,
            $org_name2    => (float) $itemsComapare[$key1]->transactions,
            'Chênh Lệch'  => ($item->transactions >= $itemsComapare[$key1]->transactions ? ($item->transactions - $itemsComapare[$key1]->transactions) : ($itemsComapare[$key1]->transactions - $item->transactions))
        );
        $sales[] = array(
            'Thời Gian'   => $item->time_period,
            $org_name1 . ' '  => (float) $item->sales,
            $org_name2    => (float) $itemsComapare[$key1]->sales,
            'Chênh Lệch'  => ($item->sales >= $itemsComapare[$key1]->sales ? ($item->sales - $itemsComapare[$key1]->sales) : ($itemsComapare[$key1]->sales - $item->sales))
        );
        $missed_sales[] = array(
            'Thời Gian'   => $item->time_period,
            $org_name1 . ' '  => (float) $item->missed_sales,
            $org_name2    => (float) $itemsComapare[$key1]->missed_sales,
            'Chênh Lệch'  => ($item->missed_sales >= $itemsComapare[$key1]->missed_sales ? ($item->missed_sales - $itemsComapare[$key1]->missed_sales) : ($itemsComapare[$key1]->missed_sales - $item->missed_sales))
        );
        $sales_hour[] = array(
            'Thời Gian'   => $item->time_period,
            $org_name1 . ' '  => (float) $item->sales_hour,
            $org_name2    => (float) $itemsComapare[$key1]->sales_hour,
            'Chênh Lệch'  => ($item->sales_hour >= $itemsComapare[$key1]->sales_hour ? ($item->sales_hour - $itemsComapare[$key1]->sales_hour) : ($itemsComapare[$key1]->sales_hour - $item->sales_hour))
        );
        $shopper_on_s_h[] = array(
            'Thời Gian'   => $item->time_period,
            $org_name1 . ' '  => (float) $item->shopper_on_s_h,
            $org_name2    => (float) $itemsComapare[$key1]->shopper_on_s_h,
            'Chênh Lệch'  => ($item->shopper_on_s_h >= $itemsComapare[$key1]->shopper_on_s_h ? ($item->shopper_on_s_h - $itemsComapare[$key1]->shopper_on_s_h) : ($itemsComapare[$key1]->shopper_on_s_h - $item->shopper_on_s_h))
        );
        $sales_on_s_h[] = array(
            'Thời Gian'   => $item->time_period,
            $org_name1 . ' '  => (float) $item->sales_on_s_h,
            $org_name2    => (float) $itemsComapare[$key1]->sales_on_s_h,
            'Chênh Lệch'  => ($item->sales_on_s_h >= $itemsComapare[$key1]->sales_on_s_h ? ($item->sales_on_s_h - $itemsComapare[$key1]->sales_on_s_h) : ($itemsComapare[$key1]->sales_on_s_h - $item->sales_on_s_h))
        );
        $loyal_visits[] = array(
            'Thời Gian'   => $item->time_period,
            $org_name1 . ' '  => (float) $item->loyal_visits,
            $org_name2    => (float) $itemsComapare[$key1]->loyal_visits,
            'Chênh Lệch'  => ($item->loyal_visits >= $itemsComapare[$key1]->loyal_visits ? ($item->loyal_visits - $itemsComapare[$key1]->loyal_visits) : ($itemsComapare[$key1]->loyal_visits - $item->loyal_visits))
        );
        $loyal_transactions[] = array(
            'Thời Gian'   => $item->time_period,
            $org_name1 . ' '  => (float) $item->loyal_transactions,
            $org_name2    => (float) $itemsComapare[$key1]->loyal_transactions,
            'Chênh Lệch'  => ($item->loyal_transactions >= $itemsComapare[$key1]->loyal_transactions ? ($item->loyal_transactions - $itemsComapare[$key1]->loyal_transactions) : ($itemsComapare[$key1]->loyal_transactions - $item->loyal_transactions))
        );
        $loyal_conversion[] = array(
            'Thời Gian'   => $item->time_period,
            $org_name1 . ' '  => (float) $item->loyal_conversion,
            $org_name2    => (float) $itemsComapare[$key1]->loyal_conversion,
            'Chênh Lệch'  => ($item->loyal_conversion >= $itemsComapare[$key1]->loyal_conversion ? ($item->loyal_conversion - $itemsComapare[$key1]->loyal_conversion) : ($itemsComapare[$key1]->loyal_conversion - $item->loyal_conversion))
        );
        $miss_loyal1 =  $item->loyal_conversion > 0 ? (float) (100 - $item->loyal_conversion) : 0;
        $miss_loyal2 =  $itemsComapare[$key1]->loyal_conversion > 0 ? (float) (100 - $itemsComapare[$key1]->loyal_conversion) : 0;
        $miss_loyal[] = array(
            'Thời Gian'   => $item->time_period,
            $org_name1 . ' '  => $miss_loyal1,
            $org_name2    => $miss_loyal2,
            'Chênh Lệch'  => ($miss_loyal1 >= $miss_loyal2 ? ($miss_loyal1 - $miss_loyal2) : ($miss_loyal2 - $miss_loyal1))
        );
        $cx_index[] = array(
            'Thời Gian'   => $item->time_period,
            $org_name1 . ' '  => (float) $item->cx_index,
            $org_name2    => (float) $itemsComapare[$key1]->cx_index,
            'Chênh Lệch'  => ($item->cx_index >= $itemsComapare[$key1]->cx_index ? ($item->cx_index - $itemsComapare[$key1]->cx_index) : ($itemsComapare[$key1]->cx_index - $item->cx_index))
        );
        $nps_index[] = array(
            'Thời Gian'   => $item->time_period,
            $org_name1 . ' '  => (float) $item->nps_index,
            $org_name2    => (float) $itemsComapare[$key1]->nps_index,
            'Chênh Lệch'  => ($item->nps_index >= $itemsComapare[$key1]->nps_index ? ($item->nps_index - $itemsComapare[$key1]->nps_index) : ($itemsComapare[$key1]->nps_index - $item->nps_index))
        );
    }

    // Xuất excel só sánh thời gian
    public function sp_footfall_performance_time_comparison_export_excel(Request $request)
    {
        // try {
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

        $items = DB::select("exec sp_general_report_data_in_out_by_site $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date, $end_date, $view_by, $operation");
        $itemsComapare = DB::select("exec sp_general_report_data_in_out_by_site $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date_compare, $end_date_compare, $view_by, $operation");
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
        $shopper_visits1 = 0;
        $kids_visits1 = 0;
        $turn_in_rate1 = 0;
        $total_traffic1 = 0;
        $total_avg_time1 = 0;
        $conversion_rate1 = 0;
        $total_atv1 = 0;
        $total_avg_item1 = 0;
        $total_sales_yield1 = 0;
        $total_transactions1 = 0;
        $total_sales1 = 0;
        $total_missed_sales1 = 0;
        $loyal_visits1 = 0;
        $loyal_transactions1 = 0;
        $loyal_conversion1 = 0;
        $miss_loyal1 = 0;
        $cx_index1 = 0;
        $nps_index1 = 0;
        $total_sales_hours1 = 0;
        $total_shopper_on_sh1 = 0;
        $total_sale_on_sh1 = 0;

        $passer_by2 = 0;
        $total_num_to_enter2 = 0;
        $shopper_visits2 = 0;
        $kids_visits2 = 0;
        $turn_in_rate2 = 0;
        $total_traffic2 = 0;
        $total_avg_time2 = 0;
        $conversion_rate2 = 0;
        $total_atv2 = 0;
        $total_avg_item2 = 0;
        $total_sales_yield2 = 0;
        $total_transactions2 = 0;
        $total_sales2 = 0;
        $total_missed_sales2 = 0;
        $loyal_visits2 = 0;
        $loyal_transactions2 = 0;
        $loyal_conversion2 = 0;
        $miss_loyal2 = 0;
        $cx_index2 = 0;
        $nps_index2 = 0;
        $total_sales_hours2 = 0;
        $total_shopper_on_sh2 = 0;
        $total_sale_on_sh2 = 0;
        // $total_seconds1 = 0;
        // $total_seconds2 = 0;
        $i = 0;
        $i_compared = 0;
        foreach ($items as $item) {
            $this->get_total_store($item, $passer_by1, $total_num_to_enter1, $kids_visits1, $shopper_visits1, $turn_in_rate1, $total_traffic1, $total_avg_time1, $conversion_rate1, $total_atv1, $total_avg_item1, $total_sales_yield1, $total_transactions1, $total_sales1, $total_missed_sales1, $loyal_visits1, $loyal_transactions1, $loyal_conversion1, $miss_loyal1, $cx_index1, $nps_index1, $total_sales_hours1, $total_shopper_on_sh1, $total_sale_on_sh1);
            if ((float) $item->avg_time > 0)
                $i++;
        }
        foreach ($itemsComapare as $item) {
            $this->get_total_store($item, $passer_by2, $total_num_to_enter2, $kids_visits2, $shopper_visits2, $turn_in_rate2, $total_traffic2, $total_avg_time2, $conversion_rate2, $total_atv2, $total_avg_item2, $total_sales_yield2, $total_transactions2, $total_sales2, $total_missed_sales2, $loyal_visits2, $loyal_transactions2, $loyal_conversion2, $miss_loyal2, $cx_index2, $nps_index2, $total_sales_hours2, $total_shopper_on_sh2, $total_sale_on_sh2);
            if ((float) $item->avg_time > 0)
                $i_compared++;
        }

        // if ($view_by === 'Hour') {
        //     $total_avg_time1 = ($total_num_to_enter1) > 0 ? (float) number_format((($total_seconds1 / 60) / ($total_num_to_enter1)), 2, '.', '') : 0;
        //     $total_avg_time2 = ($total_num_to_enter2) > 0 ? (float) number_format((($total_seconds2 / 60) / ($total_num_to_enter2)), 2, '.', '') : 0;
        // } else {
        $total_avg_time1 = ($i) > 0 ? (float) number_format(($total_avg_time1 / ($i)), 2, '.', '') : 0;
        $total_avg_time2 = ($i_compared) > 0 ? (float) number_format(($total_avg_time2 / ($i_compared)), 2, '.', '') : 0;
        // }
        $value_header =  $this->get_value_header_time($start_date, $end_date, $start_date_compare, $end_date_compare, $start_time, $end_time, $org_name);
        $turn_in_rate1 = ($row1) > 0 ? (float) number_format(($turn_in_rate1 / ($row1)), 2, '.', '') : 0;
        $total_atv1 = ($row1) > 0 ? (float) number_format(($total_atv1 / ($row1)), 2, '.', '') : 0;
        $conversion_rate1 = ($row1) > 0 ? (float) number_format(($conversion_rate1 / ($row1)), 2, '.', '') : 0;
        $loyal_visits1 = ($row1) > 0 ? (float) number_format(($loyal_visits1 / ($row1)), 2, '.', '') : 0;
        $loyal_transactions1 = ($row1) > 0 ? (float) number_format(($loyal_transactions1 / ($row1)), 2, '.', '') : 0;
        $loyal_conversion1 = ($row1) > 0 ? (float) number_format(($loyal_conversion1 / ($row1)), 2, '.', '') : 0;
        $miss_loyal1 = ($loyal_conversion1) > 0 ? (float) number_format((100 - $loyal_conversion1), 2, '.', '') : 0;
        $total_avg_item1 = ($row1) > 0 ? (float) number_format(($total_avg_item1 / ($row1)), 2, '.', '') : 0;
        $cx_index1 = ($row1) > 0 ? (float) number_format(($cx_index1 / ($row1)), 2, '.', '') : 0;
        $nps_index1 = ($row1) > 0 ? (float) number_format(($nps_index1 / ($row1)), 2, '.', '') : 0;
        $total_sales_hours1 = ($row1) > 0 ? (float) number_format(($total_sales_hours1 / ($row1)), 2, '.', '') : 0;
        $total_shopper_on_sh1 = ($row1) > 0 ? (float) number_format(($total_shopper_on_sh1 / ($row1)), 2, '.', '') : 0;
        $total_sale_on_sh1 = ($row1) > 0 ? (float) number_format(($total_sale_on_sh1 / ($row1)), 2, '.', '') : 0;

        $turn_in_rate2 = ($row2) > 0 ? (float) number_format(($turn_in_rate2 / ($row2)), 2, '.', '') : 0;
        $total_atv2 = ($row2) > 0 ? (float) number_format(($total_atv2 / ($row2)), 2, '.', '') : 0;
        $conversion_rate2 = ($row2) > 0 ? (float) number_format(($conversion_rate2 / ($row2)), 2, '.', '') : 0;
        $loyal_visits2 = ($row2) > 0 ? (float) number_format(($loyal_visits2 / ($row2)), 2, '.', '') : 0;
        $loyal_transactions2 = ($row2) > 0 ? (float) number_format(($loyal_transactions2 / ($row2)), 2, '.', '') : 0;
        $loyal_conversion2 = ($row2) > 0 ? (float) number_format(($loyal_conversion2 / ($row2)), 2, '.', '') : 0;
        $miss_loyal2 = ($loyal_conversion2) > 0 ? (float) number_format((100 - $loyal_conversion2), 2, '.', '') : 0;
        $total_avg_item2 = ($row2) > 0 ? (float) number_format(($total_avg_item2 / ($row2)), 2, '.', '') : 0;
        $cx_index2 = ($row2) > 0 ? (float) number_format(($cx_index2 / ($row2)), 2, '.', '') : 0;
        $nps_index2 = ($row2) > 0 ? (float) number_format(($nps_index2 / ($row2)), 2, '.', '') : 0;
        $total_sales_hours2 = ($row2) > 0 ? (float) number_format(($total_sales_hours2 / ($row2)), 2, '.', '') : 0;
        $total_shopper_on_sh2 = ($row2) > 0 ? (float) number_format(($total_shopper_on_sh2 / ($row2)), 2, '.', '') : 0;
        $total_sale_on_sh2 = ($row2) > 0 ? (float) number_format(($total_sale_on_sh2 / ($row2)), 2, '.', '') : 0;
        $this->get_column_excel($visits, $value_header['value3'], $value_header['value4'], $total_num_to_enter1, $total_num_to_enter2);
        $this->get_column_excel($traffic, $value_header['value3'], $value_header['value4'], $total_traffic1, $total_traffic2);
        $this->get_column_excel($shopper, $value_header['value3'], $value_header['value4'], $shopper_visits1, $shopper_visits2);
        $this->get_column_excel($passer, $value_header['value3'], $value_header['value4'], $passer_by1, $passer_by2);
        $this->get_column_excel($kids, $value_header['value3'], $value_header['value4'], $kids_visits1, $kids_visits2);
        $this->get_column_excel($turn_rate, $value_header['value3'], $value_header['value4'], $turn_in_rate1, $turn_in_rate2);
        $this->get_column_excel($avg_time, $value_header['value3'], $value_header['value4'], $total_avg_time1, $total_avg_time2, true);
        $this->get_column_excel($conversion, $value_header['value3'], $value_header['value4'], $conversion_rate1, $conversion_rate2);
        $this->get_column_excel($atv, $value_header['value3'], $value_header['value4'], $total_atv1, $total_atv2);
        $this->get_column_excel($avg_item, $value_header['value3'], $value_header['value4'], $total_avg_item1, $total_avg_item2);
        $this->get_column_excel($sales_yield, $value_header['value3'], $value_header['value4'], $total_sales_yield1, $total_sales_yield2);
        $this->get_column_excel($transactions, $value_header['value3'], $value_header['value4'], $total_transactions1, $total_transactions2);
        $this->get_column_excel($sales, $value_header['value3'], $value_header['value4'], $total_sales1, $total_sales2);
        $this->get_column_excel($missed_sales, $value_header['value3'], $value_header['value4'], $total_missed_sales1, $total_missed_sales2);
        $this->get_column_excel($sales_hour, $value_header['value3'], $value_header['value4'], $total_sales_hours1, $total_sales_hours2);
        $this->get_column_excel($shopper_on_s_h, $value_header['value3'], $value_header['value4'], $total_shopper_on_sh1, $total_shopper_on_sh2);
        $this->get_column_excel($sales_on_s_h, $value_header['value3'], $value_header['value4'], $total_sale_on_sh1, $total_sale_on_sh2);
        $this->get_column_excel($loyal_visits, $value_header['value3'], $value_header['value4'], $loyal_visits1, $loyal_visits2);
        $this->get_column_excel($loyal_transactions, $value_header['value3'], $value_header['value4'], $loyal_transactions1, $loyal_transactions2);
        $this->get_column_excel($loyal_conversion, $value_header['value3'], $value_header['value4'], $loyal_conversion1, $loyal_conversion2);
        $this->get_column_excel($miss_loyal, $value_header['value3'], $value_header['value4'], $miss_loyal1, $miss_loyal2);
        $this->get_column_excel($cx_index, $value_header['value3'], $value_header['value4'], $cx_index1, $cx_index2);
        $this->get_column_excel($nps_index, $value_header['value3'], $value_header['value4'], $nps_index1, $nps_index2);

        if ($row1 >= $row2) {
            foreach ($items as $key1 => $item) {
                $this->get_all_row_if_left_more($item, $itemsComapare, $key1, $visits, $traffic, $shopper, $passer, $kids, $turn_rate, $avg_time, $conversion, $atv, $avg_item, $sales_yield, $transactions, $sales, $missed_sales, $loyal_visits, $loyal_transactions, $loyal_conversion, $miss_loyal, $cx_index, $nps_index, $sales_hour, $shopper_on_s_h, $sales_on_s_h, $value_header, $view_by);
            }
        }
        if ($row2 > $row1) {
            foreach ($itemsComapare as $key1 => $item) {
                $this->get_all_row_if_right_more($item, $items, $key1, $visits, $traffic, $shopper, $passer, $kids, $turn_rate, $avg_time, $conversion, $atv, $avg_item, $sales_yield, $transactions, $sales, $missed_sales, $loyal_visits, $loyal_transactions, $loyal_conversion, $miss_loyal, $cx_index, $nps_index, $sales_hour, $shopper_on_s_h, $sales_on_s_h, $value_header, $view_by);
            }
        }

        $name = Carbon::today()->format('d_m_Y') . 'v' . rand(1, 1000);
        Excel::create('PERFORMANCE_TIME_COMPARISON_' . $name, function ($excel)  use ($visits, $traffic, $shopper, $passer, $kids, $turn_rate, $avg_time, $conversion, $atv, $avg_item, $sales_yield, $transactions, $sales, $missed_sales, $loyal_visits, $loyal_transactions, $loyal_conversion, $miss_loyal, $cx_index, $nps_index, $sales_hour, $shopper_on_s_h, $sales_on_s_h, $value_header, $index_not) {
            $header = array(
                'dong1' => 'Kết quả so sánh tổng lượng khách ra vào tại : ' . $value_header['value1'] . ' ',
                'dong2' => 'Thời gian:  ' . $value_header['value2'] . '  ',
                'dong3' => 'Ngày:  (' . $value_header['value3'] . ')  so với  (' . $value_header['value4'] . ')'
            );
            // Tiêu đề ngoài file
            $excel->setCreator('ACS')->setCompany('ACS Solution');

            $Passer_title = 'PasserBy';
            $visits_title = 'Visits';
            $shopper_title = 'Shoppers';
            $turn_rate_title = 'Turn in rate (%)';
            $kids_title = 'Kids Visits';
            $avg_time_title = 'Avg Time (min)';
            $traffic_title = 'Traffic Flow';
            $conversion_rate_title = 'Conversion rate (%)';
            $atv_title = 'ATV';
            $avg_item_title = 'Avg Items';
            $sales_yield_title = 'Sales Yield';
            $transactions_title = 'Transactions';
            $sales_title = 'Sales';
            $missed_sales_title = 'Missed Sales Opportunity';
            $sales_hour_title = 'Sales hours';
            $shopper_on_s_h_title = 'Shoppers on sales hour';
            $sales_on_s_h_title = 'Sales on sales hour';
            $loyal_visits_title = 'Member Visits (%)';
            $loyal_transactions_title = 'Member Transactions (%)';
            $loyal_conversion_title = 'Member CR (%)';
            $miss_loyal_title = 'Lost member CR (%)';
            $cx_index_title = 'CX index (%)';
            $nps_index_title = 'NPS index (%)';
            if (!in_array('PasserBy', $index_not, true))
                $this->create_new_sheet($Passer_title, $excel, $sheet, $header, $passer);
            if (!in_array('Visits', $index_not, true))
                $this->create_new_sheet($visits_title, $excel, $sheet, $header, $visits);
            if (!in_array('Shoppers', $index_not, true))
                $this->create_new_sheet($shopper_title, $excel, $sheet, $header, $shopper);
            if (!in_array('Turn in rate (%)', $index_not, true))
                $this->create_new_sheet($turn_rate_title, $excel, $sheet, $header, $turn_rate);
            if (!in_array('Kids Visits', $index_not, true))
                $this->create_new_sheet($kids_title, $excel, $sheet, $header, $kids);
            if (!in_array('Avg Time (min)', $index_not, true))
                $this->create_new_sheet($avg_time_title, $excel, $sheet, $header, $avg_time);
            if (!in_array('Traffic Flow', $index_not, true))
                $this->create_new_sheet($traffic_title, $excel, $sheet, $header, $traffic);

            if (!in_array('Conversion rate (%)', $index_not, true))
                $this->create_new_sheet($conversion_rate_title, $excel, $sheet, $header, $conversion);
            if (!in_array('ATV', $index_not, true))
                $this->create_new_sheet($atv_title, $excel, $sheet, $header, $atv);
            if (!in_array('Avg Items', $index_not, true))
                $this->create_new_sheet($avg_item_title, $excel, $sheet, $header, $avg_item);
            if (!in_array('Sales Yield', $index_not, true))
                $this->create_new_sheet($sales_yield_title, $excel, $sheet, $header, $sales_yield);
            if (!in_array('Transactions', $index_not, true))
                $this->create_new_sheet($transactions_title, $excel, $sheet, $header, $transactions);
            if (!in_array('Sales', $index_not, true))
                $this->create_new_sheet($sales_title, $excel, $sheet, $header, $sales);
            if (!in_array('Missed Sales Opportunity', $index_not, true))
                $this->create_new_sheet($missed_sales_title, $excel, $sheet, $header, $missed_sales);
            if (!in_array('Sales hours', $index_not, true))
                $this->create_new_sheet($sales_hour_title, $excel, $sheet, $header, $sales_hour);
            if (!in_array('Shoppers on sales hour', $index_not, true))
                $this->create_new_sheet($shopper_on_s_h_title, $excel, $sheet, $header, $shopper_on_s_h);
            if (!in_array('Sales on sales hour', $index_not, true))
                $this->create_new_sheet($sales_on_s_h_title, $excel, $sheet, $header, $sales_on_s_h);
            if (!in_array('Member Visits (%)', $index_not, true))
                $this->create_new_sheet($loyal_visits_title, $excel, $sheet, $header, $loyal_visits);
            if (!in_array('Member Transactions (%)', $index_not, true))
                $this->create_new_sheet($loyal_transactions_title, $excel, $sheet, $header, $loyal_transactions);
            if (!in_array('Member CR (%)', $index_not, true))
                $this->create_new_sheet($loyal_conversion_title, $excel, $sheet, $header, $loyal_conversion);
            if (!in_array('Lost member CR (%)', $index_not, true))
                $this->create_new_sheet($miss_loyal_title, $excel, $sheet, $header, $miss_loyal);
            if (!in_array('CX index (%)', $index_not, true))
                $this->create_new_sheet($cx_index_title, $excel, $sheet, $header, $cx_index);
            if (!in_array('NPS index (%)', $index_not, true))
                $this->create_new_sheet($nps_index_title, $excel, $sheet, $header, $nps_index);
        })->store('xls', public_path('exports'));

        $file_name = 'PERFORMANCE_TIME_COMPARISON_' . $name . '.xls';
        // } catch (\Exception $exception) {
        //     return 'none';
        // }
        return response()->json($file_name);
    }
    // Lấy các row store compared
    function get_all_row_if_left_more(&$item, $data_old, &$key1, &$visits, &$traffic, &$shopper, &$passer, &$kids, &$turn_rate, &$avg_time, &$conversion, &$atv, &$avg_item, &$sales_yield, &$transactions, &$sales, &$missed_sales, &$loyal_visits, &$loyal_transactions, &$loyal_conversion, &$miss_loyal, &$cx_index, &$nps_index, &$sales_hour, &$shopper_on_s_h, &$sales_on_s_h, $value_header, &$view_by)
    {
        $visits[] = array(
            'Thời Gian'       =>  $item->time_period . ((isset($data_old[$key1]) && $view_by != 'Hour') ? (' / ' . $data_old[$key1]->time_period) : ''),
            $value_header['value3']    =>   (int) $item->num_to_enter,
            $value_header['value4']    => (isset($data_old[$key1]) ? (int) $data_old[$key1]->num_to_enter : 0),
            'Chênh Lệch'      => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->num_to_enter, $data_old[$key1]->num_to_enter) : 0)
        );
        $traffic[] = array(
            'Thời Gian'   =>  $item->time_period . ((isset($data_old[$key1]) && $view_by != 'Hour') ? (' / ' . $data_old[$key1]->time_period) : ''),
            $value_header['value3']    => (int) $item->traffic,
            $value_header['value4']    => (isset($data_old[$key1])  ? (int) $data_old[$key1]->traffic : 0),
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->traffic, $data_old[$key1]->traffic) : 0)
        );
        $shopper[] = array(
            'Thời Gian'   =>  $item->time_period . ((isset($data_old[$key1]) && $view_by != 'Hour') ? (' / ' . $data_old[$key1]->time_period) : ''),
            $value_header['value3']    => (int) $item->shopper_visits,
            $value_header['value4']    => (isset($data_old[$key1])  ? (int) $data_old[$key1]->shopper_visits : 0),
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->shopper_visits, $data_old[$key1]->shopper_visits) : 0)
        );
        $passer[] = array(
            'Thời Gian'   =>  $item->time_period . ((isset($data_old[$key1]) && $view_by != 'Hour') ? (' / ' . $data_old[$key1]->time_period) : ''),
            $value_header['value3']    => (int) $item->passer_by,
            $value_header['value4']    => (isset($data_old[$key1])  ? (int) $data_old[$key1]->passer_by : 0),
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->passer_by, $data_old[$key1]->passer_by) : 0)
        );
        $kids[] = array(
            'Thời Gian'   =>  $item->time_period . ((isset($data_old[$key1]) && $view_by != 'Hour') ? (' / ' . $data_old[$key1]->time_period) : ''),
            $value_header['value3']    => (int) $item->kids_visits,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (int) $data_old[$key1]->kids_visits : 0),
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->kids_visits, $data_old[$key1]->kids_visits) : 0)
        );
        $turn_rate[] = array(
            'Thời Gian'   =>  $item->time_period . ((isset($data_old[$key1]) && $view_by != 'Hour') ? (' / ' . $data_old[$key1]->time_period) : ''),
            $value_header['value3']    => (float) $item->turn_in_rate,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->turn_in_rate : 0),
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->turn_in_rate, $data_old[$key1]->turn_in_rate) : 0)
        );

        $avg_time[] = array(
            'Thời Gian'   =>   $item->time_period . ((isset($data_old[$key1]) && $view_by != "Hour") ? (' / ' . $data_old[$key1]->time_period) : ''),
            $value_header['value3']    => $this->changeSecondsToformatTime((float) $item->avg_time * 60),
            $value_header['value4']    => (isset($data_old[$key1])  ? $this->changeSecondsToformatTime((float)  $data_old[$key1]->avg_time * 60) : ''),
            'Chênh Lệch'  => (isset($data_old[$key1]) ?  $this->changeSecondsToformatTime($this->tinh_chenh_lech($item->avg_time, $data_old[$key1]->avg_time) * 60)  : '00:00:00'),
        );

        $conversion[] = array(
            'Thời Gian'   => $item->time_period . ((isset($data_old[$key1]) && $view_by != 'Hour') ? (' / ' . $data_old[$key1]->time_period) : ''),
            $value_header['value3']    => (float) $item->conversion,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->conversion : 0),
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->conversion, $data_old[$key1]->conversion) : 0)
        );
        $atv[] = array(
            'Thời Gian'   => $item->time_period . ((isset($data_old[$key1]) && $view_by != 'Hour') ? (' / ' . $data_old[$key1]->time_period) : ''),
            $value_header['value3']    => (float) $item->atv,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->atv : 0),
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->atv, $data_old[$key1]->atv) : 0)
        );
        $avg_item[] = array(
            'Thời Gian'   => $item->time_period . ((isset($data_old[$key1]) && $view_by != 'Hour') ? (' / ' . $data_old[$key1]->time_period) : ''),
            $value_header['value3']    => (float) $item->avg_item,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->avg_item : 0),
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->avg_item, $data_old[$key1]->avg_item) : 0)
        );
        $sales_yield[] = array(
            'Thời Gian'   => $item->time_period . ((isset($data_old[$key1]) && $view_by != 'Hour') ? (' / ' . $data_old[$key1]->time_period) : ''),
            $value_header['value3']    => (float) $item->sales_yield,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->sales_yield : 0),
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->sales_yield, $data_old[$key1]->sales_yield) : 0)
        );
        $transactions[] = array(
            'Thời Gian'   => $item->time_period . ((isset($data_old[$key1]) && $view_by != 'Hour') ? (' / ' . $data_old[$key1]->time_period) : ''),
            $value_header['value3']    => (float) $item->transactions,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->transactions : 0),
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->transactions, $data_old[$key1]->transactions) : 0)
        );
        $sales[] = array(
            'Thời Gian'   => $item->time_period . ((isset($data_old[$key1]) && $view_by != 'Hour') ? (' / ' . $data_old[$key1]->time_period) : ''),
            $value_header['value3']    => (float) $item->sales,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->sales : 0),
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->sales, $data_old[$key1]->sales) : 0)
        );
        $missed_sales[] = array(
            'Thời Gian'   => $item->time_period . ((isset($data_old[$key1]) && $view_by != 'Hour') ? (' / ' . $data_old[$key1]->time_period) : ''),
            $value_header['value3']    => (float) $item->missed_sales,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->missed_sales : 0),
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->missed_sales, $data_old[$key1]->missed_sales) : 0)
        );
        $sales_hour[] = array(
            'Thời Gian'   => $item->time_period . ((isset($data_old[$key1]) && $view_by != 'Hour') ? (' / ' . $data_old[$key1]->time_period) : ''),
            $value_header['value3']    => (float) $item->sales_hour,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->sales_hour : 0),
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->sales_hour, $data_old[$key1]->sales_hour) : 0)
        );
        $shopper_on_s_h[] = array(
            'Thời Gian'   => $item->time_period . ((isset($data_old[$key1]) && $view_by != 'Hour') ? (' / ' . $data_old[$key1]->time_period) : ''),
            $value_header['value3']    => (float) $item->shopper_on_s_h,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->shopper_on_s_h : 0),
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->shopper_on_s_h, $data_old[$key1]->shopper_on_s_h) : 0)
        );
        $sales_on_s_h[] = array(
            'Thời Gian'   => $item->time_period . ((isset($data_old[$key1]) && $view_by != 'Hour') ? (' / ' . $data_old[$key1]->time_period) : ''),
            $value_header['value3']    => (float) $item->sales_on_s_h,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->sales_on_s_h : 0),
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->sales_on_s_h, $data_old[$key1]->sales_on_s_h) : 0)
        );
        $loyal_visits[] = array(
            'Thời Gian'   => $item->time_period . ((isset($data_old[$key1]) && $view_by != 'Hour') ? (' / ' . $data_old[$key1]->time_period) : ''),
            $value_header['value3']    => (float) $item->loyal_visits,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->loyal_visits : 0),
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->loyal_visits, $data_old[$key1]->loyal_visits) : 0)
        );
        $loyal_transactions[] = array(
            'Thời Gian'   => $item->time_period . ((isset($data_old[$key1]) && $view_by != 'Hour') ? (' / ' . $data_old[$key1]->time_period) : ''),
            $value_header['value3']    => (float) $item->loyal_transactions,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->loyal_transactions : 0),
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->loyal_transactions, $data_old[$key1]->loyal_transactions) : 0)
        );
        $loyal_conversion[] = array(
            'Thời Gian'   => $item->time_period . ((isset($data_old[$key1]) && $view_by != 'Hour') ? (' / ' . $data_old[$key1]->time_period) : ''),
            $value_header['value3']    => (float) $item->loyal_conversion,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->loyal_conversion : 0),
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->loyal_conversion, $data_old[$key1]->loyal_conversion) : 0)
        );
        $miss_loyal1 =  $item->loyal_conversion > 0 ? (float) (100 - $item->loyal_conversion) : 0;
        $miss_loyal2 = (isset($data_old[$key1]) && $data_old[$key1]->loyal_conversion > 0) ? (float) (100 - $data_old[$key1]->loyal_conversion) : 0;
        $miss_loyal[] = array(
            'Thời Gian'   => $item->time_period . ((isset($data_old[$key1]) && $view_by != 'Hour') ? (' / ' . $data_old[$key1]->time_period) : ''),
            $value_header['value3']    => $miss_loyal1,
            $value_header['value4']    => $miss_loyal2,
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($miss_loyal1, $miss_loyal2) : 0)
        );
        $cx_index[] = array(
            'Thời Gian'   => $item->time_period . ((isset($data_old[$key1]) && $view_by != 'Hour') ? (' / ' . $data_old[$key1]->time_period) : ''),
            $value_header['value3']    => (float) $item->cx_index,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->cx_index : 0),
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->cx_index, $data_old[$key1]->cx_index) : 0)
        );
        $nps_index[] = array(
            'Thời Gian'   => $item->time_period . ((isset($data_old[$key1]) && $view_by != 'Hour') ? (' / ' . $data_old[$key1]->time_period) : ''),
            $value_header['value3']    => (float) $item->nps_index,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->nps_index : 0),
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->nps_index, $data_old[$key1]->nps_index) : 0)
        );
    }
    // Lấy các row store compared
    function get_all_row_if_right_more(&$item, $data_old, &$key1, &$visits, &$traffic, &$shopper, &$passer, &$kids, &$turn_rate, &$avg_time, &$conversion, &$atv, &$avg_item, &$sales_yield, &$transactions, &$sales, &$missed_sales, &$loyal_visits, &$loyal_transactions, &$loyal_conversion, &$miss_loyal, &$cx_index, &$nps_index, &$sales_hour, &$shopper_on_s_h, &$sales_on_s_h, $value_header)
    {
        $visits[] = array(
            'Thời Gian'       => ((isset($data_old[$key1])) ? ($data_old[$key1]->time_period) . ' / ' : '') . $item->time_period,
            $value_header['value4']    => (isset($data_old[$key1]) ? (int) $data_old[$key1]->num_to_enter : 0),
            $value_header['value3']    =>   (int) $item->num_to_enter,
            'Chênh Lệch'      => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->num_to_enter, $data_old[$key1]->num_to_enter) : 0)
        );
        $traffic[] = array(
            'Thời Gian'   => ((isset($data_old[$key1])) ? ($data_old[$key1]->time_period) . ' / ' : '') . $item->time_period,
            $value_header['value4']    => (isset($data_old[$key1])  ? (int) $data_old[$key1]->traffic : 0),
            $value_header['value3']    => (int) $item->traffic,
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->traffic, $data_old[$key1]->traffic) : 0)
        );
        $shopper[] = array(
            'Thời Gian'   => ((isset($data_old[$key1])) ? ($data_old[$key1]->time_period) . ' / ' : '') . $item->time_period,
            $value_header['value4']    => (isset($data_old[$key1])  ? (int) $data_old[$key1]->shopper_visits : 0),
            $value_header['value3']    => (int) $item->shopper_visits,
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->shopper_visits, $data_old[$key1]->shopper_visits) : 0)
        );
        $passer[] = array(
            'Thời Gian'   => ((isset($data_old[$key1])) ? ($data_old[$key1]->time_period) . ' / ' : '') . $item->time_period,
            $value_header['value4']    => (isset($data_old[$key1])  ? (int) $data_old[$key1]->passer_by : 0),
            $value_header['value3']    => (int) $item->passer_by,
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->passer_by, $data_old[$key1]->passer_by) : 0)
        );
        $kids[] = array(
            'Thời Gian'   => ((isset($data_old[$key1])) ? ($data_old[$key1]->time_period) . ' / ' : '') . $item->time_period,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (int) $data_old[$key1]->kids_visits : 0),
            $value_header['value3']    => (int) $item->kids_visits,
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->kids_visits, $data_old[$key1]->kids_visits) : 0)
        );
        $turn_rate[] = array(
            'Thời Gian'   => ((isset($data_old[$key1])) ? ($data_old[$key1]->time_period) . ' / ' : '') . $item->time_period,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->turn_in_rate : 0),
            $value_header['value3']    => (float) $item->turn_in_rate,
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->turn_in_rate, $data_old[$key1]->turn_in_rate) : 0)
        );
        $avg_time[] = array(
            'Thời Gian'   =>  $item->time_period . (isset($data_old[$key1]) ? (' / ' . $data_old[$key1]->time_period) : ''),
            $value_header['value4']    => (isset($data_old[$key1])  ?  $this->changeSecondsToformatTime((float) $data_old[$key1]->avg_time * 60) : ''),
            $value_header['value3']    =>   $this->changeSecondsToformatTime((float) $item->avg_time * 60),
            'Chênh Lệch'  => (isset($data_old[$key1]) ?  $this->changeSecondsToformatTime(($this->tinh_chenh_lech($item->avg_time, $data_old[$key1]->avg_time)) * 60) : '00:00:00'),
        );

        $conversion[] = array(
            'Thời Gian'   => ((isset($data_old[$key1])) ? ($data_old[$key1]->time_period) . ' / ' : '') . $item->time_period,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->conversion : 0),
            $value_header['value3']    => (float) $item->conversion,
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->conversion, $data_old[$key1]->conversion) : 0)
        );
        $atv[] = array(
            'Thời Gian'   => ((isset($data_old[$key1])) ? ($data_old[$key1]->time_period) . ' / ' : '') . $item->time_period,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->atv : 0),
            $value_header['value3']    => (float) $item->atv,
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->atv, $data_old[$key1]->atv) : 0)
        );
        $avg_item[] = array(
            'Thời Gian'   => ((isset($data_old[$key1])) ? ($data_old[$key1]->time_period) . ' / ' : '') . $item->time_period,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->avg_item : 0),
            $value_header['value3']    => (float) $item->avg_item,
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->avg_item, $data_old[$key1]->avg_item) : 0)
        );
        $sales_yield[] = array(
            'Thời Gian'   => ((isset($data_old[$key1])) ? ($data_old[$key1]->time_period) . ' / ' : '') . $item->time_period,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->sales_yield : 0),
            $value_header['value3']    => (float) $item->sales_yield,
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->sales_yield, $data_old[$key1]->sales_yield) : 0)
        );
        $transactions[] = array(
            'Thời Gian'   => ((isset($data_old[$key1])) ? ($data_old[$key1]->time_period) . ' / ' : '') . $item->time_period,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->transactions : 0),
            $value_header['value3']    => (float) $item->transactions,
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->transactions, $data_old[$key1]->transactions) : 0)
        );
        $sales[] = array(
            'Thời Gian'   => ((isset($data_old[$key1])) ? ($data_old[$key1]->time_period) . ' / ' : '') . $item->time_period,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->sales : 0),
            $value_header['value3']    => (float) $item->sales,
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->sales, $data_old[$key1]->sales) : 0)
        );
        $missed_sales[] = array(
            'Thời Gian'   => ((isset($data_old[$key1])) ? ($data_old[$key1]->time_period) . ' / ' : '') . $item->time_period,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->missed_sales : 0),
            $value_header['value3']    => (float) $item->missed_sales,
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->missed_sales, $data_old[$key1]->missed_sales) : 0)
        );
        $sales_hour[] = array(
            'Thời Gian'   => ((isset($data_old[$key1])) ? ($data_old[$key1]->time_period) . ' / ' : '') . $item->time_period,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->sales_hour : 0),
            $value_header['value3']    => (float) $item->sales_hour,
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->sales_hour, $data_old[$key1]->sales_hour) : 0)
        );
        $shopper_on_s_h[] = array(
            'Thời Gian'   => ((isset($data_old[$key1])) ? ($data_old[$key1]->time_period) . ' / ' : '') . $item->time_period,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->shopper_on_s_h : 0),
            $value_header['value3']    => (float) $item->shopper_on_s_h,
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->shopper_on_s_h, $data_old[$key1]->shopper_on_s_h) : 0)
        );
        $sales_on_s_h[] = array(
            'Thời Gian'   => ((isset($data_old[$key1])) ? ($data_old[$key1]->time_period) . ' / ' : '') . $item->time_period,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->sales_on_s_h : 0),
            $value_header['value3']    => (float) $item->sales_on_s_h,
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->sales_on_s_h, $data_old[$key1]->sales_on_s_h) : 0)
        );
        $loyal_visits[] = array(
            'Thời Gian'   => ((isset($data_old[$key1])) ? ($data_old[$key1]->time_period) . ' / ' : '') . $item->time_period,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->loyal_visits : 0),
            $value_header['value3']    => (float) $item->loyal_visits,
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->loyal_visits, $data_old[$key1]->loyal_visits) : 0)
        );
        $loyal_transactions[] = array(
            'Thời Gian'   => ((isset($data_old[$key1])) ? ($data_old[$key1]->time_period) . ' / ' : '') . $item->time_period,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->loyal_transactions : 0),
            $value_header['value3']    => (float) $item->loyal_transactions,
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->loyal_transactions, $data_old[$key1]->loyal_transactions) : 0)
        );
        $loyal_conversion[] = array(
            'Thời Gian'   => ((isset($data_old[$key1])) ? ($data_old[$key1]->time_period) . ' / ' : '') . $item->time_period,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->loyal_conversion : 0),
            $value_header['value3']    => (float) $item->loyal_conversion,
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->loyal_conversion, $data_old[$key1]->loyal_conversion) : 0)
        );
        $miss_loyal1 =  $item->loyal_conversion > 0 ? (float) (100 - $item->loyal_conversion) : 0;
        $miss_loyal2 = (isset($data_old[$key1]) && $data_old[$key1]->loyal_conversion > 0) ? (float) (100 - $data_old[$key1]->loyal_conversion) : 0;
        $miss_loyal[] = array(
            'Thời Gian'   => ((isset($data_old[$key1])) ? ($data_old[$key1]->time_period) . ' / ' : '') . $item->time_period,
            $value_header['value4']    => $miss_loyal2,
            $value_header['value3']    => $miss_loyal1,
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($miss_loyal1, $miss_loyal2) : 0)
        );
        $cx_index[] = array(
            'Thời Gian'   => ((isset($data_old[$key1])) ? ($data_old[$key1]->time_period) . ' / ' : '') . $item->time_period,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->cx_index : 0),
            $value_header['value3']    => (float) $item->cx_index,
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->cx_index, $data_old[$key1]->cx_index) : 0)
        );
        $nps_index[] = array(
            'Thời Gian'   => ((isset($data_old[$key1])) ? ($data_old[$key1]->time_period) . ' / ' : '') . $item->time_period,
            $value_header['value4']    => (isset($data_old[$key1])  ?  (float) $data_old[$key1]->nps_index : 0),
            $value_header['value3']    => (float) $item->nps_index,
            'Chênh Lệch'  => (isset($data_old[$key1]) ?   $this->tinh_chenh_lech($item->nps_index, $data_old[$key1]->nps_index) : 0)
        );
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
            $row = count($items);

            $index_not_list = DB::select("select * from  fc_get_index_module_not_org( $organization_id)");
            foreach ($index_not_list as $item) {
                $index_not[]   = $item->index_name;
            }
            // $index_not  =  ["Kids Visits", "PasserBy", "Traffic Flow"];

            $organization =  Organization::find($organization_id);
            $org_name =  $organization->organization_name;
            if ($site_id  != 0) {
                $site = Site::find($site_id);
                $org_name  = $site->site_name;
            }
            $passer_by = 0;
            $total_num_to_enter = 0;
            $shopper_visits = 0;
            $kids_visits = 0;
            $turn_in_rate = 0;
            $total_traffic = 0;
            $total_avg_time = 0;
            $conversion_rate = 0;
            $total_atv = 0;
            $total_avg_item = 0;
            $total_sales_yield = 0;
            $total_transactions = 0;
            $total_sales = 0;
            $total_missed_sales = 0;
            $total_sales_hours = 0;
            $total_shopper_on_sh = 0;
            $total_sale_on_sh = 0;
            $loyal_visits = 0;
            $loyal_transactions = 0;
            $loyal_conversion = 0;
            $miss_loyal = 0;
            $cx_index = 0;
            $nps_index = 0;
            $i = 0;
            foreach ($items as $item) {
                $passer_by  += (int) $item->passer_by;
                $total_num_to_enter  += (int) $item->num_to_enter;
                $kids_visits         += (int) $item->kids_visits;
                $shopper_visits      += (int) $item->shopper_visits;
                $turn_in_rate        += (float) $item->turn_in_rate;
                $total_traffic       += (int) $item->traffic;
                $total_avg_time      += (float) $item->avg_time;
                $conversion_rate     += (float) $item->conversion;
                $total_atv           += (float) $item->atv;
                $total_avg_item      += (float) $item->avg_item;
                $total_sales_yield   += (float) $item->sales_yield;
                $total_transactions  += (int) $item->transactions;
                $total_sales         += (int) $item->sales;
                $total_missed_sales  += (int) $item->missed_sales;
                $total_sales_hours   += (float) $item->sales_hour;
                $total_shopper_on_sh += (float) $item->shopper_on_s_h;
                $total_sale_on_sh    += (float) $item->sales_on_s_h;
                $loyal_visits        += (float) $item->loyal_visits;
                $loyal_transactions  += (float) $item->loyal_transactions;
                $loyal_conversion    += (float) $item->loyal_conversion;
                $miss_loyal          += $item->loyal_conversion > 0 ? (float) (100 - $item->loyal_conversion) : 0;
                $cx_index            += (float) $item->cx_index;
                $nps_index           += (float) $item->nps_index;
                if (intval($item->avg_time) > 0)
                    $i++;
            }
            $turn_in_rate = ($row) > 0 ? (float) number_format(($turn_in_rate / ($row)), 2, '.', '') : 0;
            $total_avg_time = ($row) > 0 ? (float) number_format(($total_avg_time / ($i)), 2, '.', '') : 0;
            $total_atv = ($row) > 0 ? (float) number_format(($total_atv / ($row)), 2, '.', '') : 0;
            $conversion_rate = ($row) > 0 ? (float) number_format(($conversion_rate / ($row)), 2, '.', '') : 0;
            $loyal_visits = ($row) > 0 ? (float) number_format(($loyal_visits / ($row)), 2, '.', '') : 0;
            $loyal_transactions = ($row) > 0 ? (float) number_format(($loyal_transactions / ($row)), 2, '.', '') : 0;
            $loyal_conversion = ($row) > 0 ? (float) number_format(($loyal_conversion / ($row)), 2, '.', '') : 0;
            $miss_loyal = ($loyal_conversion) > 0 ? (float) number_format((100 - $loyal_conversion), 2, '.', '') : 0;
            $total_avg_item = ($row) > 0 ? (float) number_format(($total_avg_item / ($row)), 2, '.', '') : 0;
            $cx_index = ($row) > 0 ? (float) number_format(($cx_index / ($row)), 2, '.', '') : 0;
            $nps_index = ($row) > 0 ? (float) number_format(($nps_index / ($row)), 2, '.', '') : 0;
            $total_sales_hours = ($row) > 0 ? (float) number_format(($total_sales_hours / ($row)), 2, '.', '') : 0;
            $total_shopper_on_sh = ($row) > 0 ? (float) number_format(($total_shopper_on_sh / ($row)), 2, '.', '') : 0;
            $total_sale_on_sh = ($row) > 0 ? (float) number_format(($total_sale_on_sh / ($row)), 2, '.', '') : 0;
            $items1[] = array(
                'Địa điểm'            =>  '',
                'PasserBy'            => $passer_by,
                'Visits'              => $total_num_to_enter,
                'Shoppers'      => $shopper_visits,
                'Turn in rate (%)'    => $turn_in_rate,
                'Traffic Flow'        => $total_traffic,
                'Kids Visits'         => $kids_visits,
                'Avg Time (min)'      => $this->changeSecondsToformatTime((float) $total_avg_time * 60),
                'Conversion rate (%)'     => $conversion_rate,
                'ATV'                     => $total_atv,
                'Avg Items'               => $total_avg_item,
                'Sales Yield'             => $total_sales_yield,
                'Transactions'            => $total_transactions,
                'Sales'                   => $total_sales,
                'Missed Sales Opportunity' => $total_missed_sales,
                'Sales hours'                 => $total_sales_hours,
                'Shoppers on sales hour'      => $total_shopper_on_sh,
                'Sales on sales hour'         => $total_sale_on_sh,
                'Member Visits (%)'            => $loyal_visits,
                'Member Transactions (%)'      => $loyal_transactions,
                'Member CR (%)'   => $loyal_conversion,
                'Lost member CR (%)'       => $miss_loyal,
                'CX index (%)'                => $cx_index,
                'NPS index (%)'               => $nps_index,
            );
            foreach ($items as $item) {
                $items1[] = array(
                    'Địa điểm'           => $item->site_name,
                    'PasserBy'            => (int) $item->passer_by,
                    'Visits'              => (int) $item->num_to_enter,
                    'Shoppers'      => (int) $item->shopper_visits,
                    'Turn in rate (%)'    => (float) $item->turn_in_rate,
                    'Traffic Flow'        => (int) $item->traffic,
                    'Kids Visits'         => (int) $item->kids_visits,
                    'Avg Time (min)'      =>  $this->changeSecondsToformatTime((float) $item->avg_time * 60),
                    'Conversion rate (%)'     => (float) $item->conversion,
                    'ATV'                     => (float) $item->atv,
                    'Avg Items'               => (float) $item->avg_item,
                    'Sales Yield'             => (float) $item->sales_yield,
                    'Transactions'            => (int) $item->transactions,
                    'Sales'                   => (int) $item->sales,
                    'Missed Sales Opportunity' => (int) $item->missed_sales,
                    'Sales hours'                 => (float) $item->sales_hour,
                    'Shoppers on sales hour'      => (float) $item->shopper_on_s_h,
                    'Sales on sales hour'         => (float) $item->sales_on_s_h,
                    'Member Visits (%)'            => (float) $item->loyal_visits,
                    'Member Transactions (%)'      => (float) $item->loyal_transactions,
                    'Member CR (%)'   => (float) $item->loyal_conversion,
                    'Lost member CR (%)'       => $item->loyal_conversion > 0 ? 100 - $item->loyal_conversion : 0,
                    'CX index (%)'                => (float) $item->cx_index,
                    'NPS index (%)'               => (float) $item->nps_index,
                );
            }
            $value_header =  $this->get_value_header_poc_in_out($start_date, $end_date, $start_time, $end_time, $org_name);
            $name = Carbon::today()->format('d_m_Y') . 'v' . rand(1, 1000);

            foreach ($items1 as $key => $value) {
                foreach ($index_not as $value2) {
                    unset($items1[$key][$value2]);
                }
            }
            Excel::create('PERFORMANCE_SITES_' . $name, function ($excel) use ($items1, $value_header) {
                $header = array(
                    'dong1' => 'Kết quả đo lường lưu lượng ra vào tại : ' . $value_header['value1'] . ' ',
                    'dong2' => 'Kết quả đo lường lưu lượng ra vào tại : ' . $value_header['value1'] . ' ',
                    'dong3' => 'Địa điểm: ' . $value_header['value1'] . '',
                    'dong4' => 'Thời gian:  ' . $value_header['value2'] . '  ',
                    'dong5' => 'Ngày:  ' . $value_header['value3'] . '  ',
                );
                $excel->setCreator('ACS')->setCompany('ACS Solution');

                $title1 = 'Địa điểm';
                $this->get_sheet_second($title1, $excel, $sheet, $header, $items1);
            })->store('xls', public_path('exports'));
            $file_name = 'PERFORMANCE_SITES_' . $name . '.xls';
        } catch (\Exception $exception) {
            return 'none';
        }
        return response()->json($file_name);
    }

    // Xuất excel báo cáo các cửa hàng
    public function sp_footfall_performance_boston_reporting_export_excel(Request $request)
    {
        try {
            $request_user = $request->user();
            $user_id = $request_user != null ?  $request_user->id : null;
            if (isset($request->user_id))
                $user_id = $request->user_id;
            $organization_id = $request->organization_id;
            $items = isset($request->data) ? $request->data : [];
            $site_id = $request->site_id;
            $start_time = $request->start_time;
            $end_time = $request->end_time;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $operation = "SUM";
            // $items = DB::select("exec sp_general_report_data_in_out_sum_by_store $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date, $end_date, $operation,0");
            // Nghĩa sửa từ đây
            if (isset($request->data)) {
                $items = $request->data;
            } else {
                $items = DB::select("exec sp_general_report_data_in_out_sum_by_store $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date, $end_date, $operation,0");
            }
            // end

            $exists_array    = array();
            foreach ($items as $element) {
                if (!in_array($element, $exists_array)) {
                    $exists_array[]    = $element;
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
            $shopper_visits = 0;
            $kids_visits = 0;
            $turn_in_rate = 0;
            $total_traffic = 0;
            $total_avg_time = 0;
            $conversion_rate = 0;
            $total_atv = 0;
            $total_avg_item = 0;
            $total_sales_yield = 0;
            $total_transactions = 0;
            $total_sales = 0;
            $total_missed_sales = 0;
            $total_sales_hours = 0;
            $total_shopper_on_sh = 0;
            $total_sale_on_sh = 0;
            $loyal_visits = 0;
            $loyal_transactions = 0;
            $loyal_conversion = 0;
            $miss_loyal = 0;
            $cx_index = 0;
            $nps_index = 0;
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
                $kids_visits         += (int) $newItem['kids_visits'];
                $shopper_visits      += (int) $newItem['shopper_visits'];
                $turn_in_rate        += (float) $newItem['turn_in_rate'];
                $total_traffic       += (int) $newItem['traffic'];
                $total_avg_time      += (int) $newItem['avg_time'];
                $conversion_rate     += (float) $newItem['conversion'];
                $total_atv           += (float) $newItem['atv'];
                $total_avg_item      += (float) $newItem['avg_item'];
                $total_sales_yield   += (float) $newItem['sales_yield'];
                $total_transactions  += (int) $newItem['transactions'];
                $total_sales         += (int) $newItem['sales'];
                $total_missed_sales  += (int) $newItem['missed_sales'];
                $total_sales_hours   += (float) $newItem['sales_hour'];
                $total_shopper_on_sh += (float) $newItem['shopper_on_s_h'];
                $total_sale_on_sh    += (float) $newItem['sales_on_s_h'];
                $loyal_visits        += (float) $newItem['loyal_visits'];
                $loyal_transactions  += (float) $newItem['loyal_transactions'];
                $loyal_conversion    += (float) $newItem['loyal_conversion'];
                $miss_loyal          += $newItem['loyal_conversion'] > 0 ? (float) (100 - $newItem['loyal_conversion']) : 0;
                $cx_index            += (float) $newItem['cx_index'];
                $nps_index           += (float) $newItem['nps_index'];
                if (intval($newItem['avg_time']) > 0)
                    $i++;
            }
            $turn_in_rate = ($row) > 0 ? (float) number_format(($turn_in_rate / ($row)), 2, '.', '') : 0;
            if ($i > 0)
                $total_avg_time = ($row) > 0 ? (float) number_format(($total_avg_time / ($i)), 2, '.', '') : 0;
            $total_atv = ($row) > 0 ? (float) number_format(($total_atv / ($row)), 2, '.', '') : 0;
            $conversion_rate = ($row) > 0 ? (float) number_format(($conversion_rate / ($row)), 2, '.', '') : 0;
            $loyal_visits = ($row) > 0 ? (float) number_format(($loyal_visits / ($row)), 2, '.', '') : 0;
            $loyal_transactions = ($row) > 0 ? (float) number_format(($loyal_transactions / ($row)), 2, '.', '') : 0;
            $loyal_conversion = ($row) > 0 ? (float) number_format(($loyal_conversion / ($row)), 2, '.', '') : 0;
            $miss_loyal = ($loyal_conversion) > 0 ? (float) number_format((100 - $loyal_conversion), 2, '.', '') : 0;
            $total_avg_item = ($row) > 0 ? (float) number_format(($total_avg_item / ($row)), 2, '.', '') : 0;
            $cx_index = ($row) > 0 ? (float) number_format(($cx_index / ($row)), 2, '.', '') : 0;
            $nps_index = ($row) > 0 ? (float) number_format(($nps_index / ($row)), 2, '.', '') : 0;
            $total_sales_hours = ($row) > 0 ? (float) number_format(($total_sales_hours / ($row)), 2, '.', '') : 0;
            $total_shopper_on_sh = ($row) > 0 ? (float) number_format(($total_shopper_on_sh / ($row)), 2, '.', '') : 0;
            $total_sale_on_sh = ($row) > 0 ? (float) number_format(($total_sale_on_sh / ($row)), 2, '.', '') : 0;
            $items1[] = array(
                'Địa điểm'            =>  '',
                'PasserBy'            => $passer_by,
                'Visits'              => $total_num_to_enter,
                'Shoppers'      => $shopper_visits,
                'Turn in rate (%)'    => $turn_in_rate,
                'Traffic Flow'        => $total_traffic,
                'Kids Visits'         => $kids_visits,
                'Avg Time (min)'      =>    $this->changeSecondsToformatTime((float) $total_avg_time * 60),
                'Conversion rate (%)'     => $conversion_rate,
                'ATV'                     => $total_atv,
                'Avg Items'               => $total_avg_item,
                'Sales Yield'             => $total_sales_yield,
                'Transactions'            => $total_transactions,
                'Sales'                   => $total_sales,
                'Missed Sales Opportunity' => $total_missed_sales,
                'Sales hours'                 => $total_sales_hours,
                'Shoppers on sales hour'      => $total_shopper_on_sh,
                'Sales on sales hour'         => $total_sale_on_sh,
                'Member Visits (%)'            => $loyal_visits,
                'Member Transactions (%)'      => $loyal_transactions,
                'Member CR (%)'   => $loyal_conversion,
                'Lost member CR (%)'       => $miss_loyal,
                'CX index (%)'                => $cx_index,
                'NPS index (%)'               => $nps_index,
            );
            foreach ($exists_array as $item) {
                //
                $newItem = $item;
                if (is_object($item)) {
                    $newItem = (array) $item;
                }
                //
                $items1[] = array(
                    'Địa điểm'           => $newItem['site_name'],
                    'PasserBy'            => (int) $newItem['passer_by'],
                    'Visits'              => (int) $newItem['num_to_enter'],
                    'Shoppers'      => (int) $newItem['shopper_visits'],
                    'Turn in rate (%)'    => (float) $newItem['turn_in_rate'],
                    'Traffic Flow'        => (int) $newItem['traffic'],
                    'Kids Visits'         => (int) $newItem['kids_visits'],
                    'Avg Time (min)'      =>  $this->changeSecondsToformatTime((float) $newItem['avg_time'] * 60),
                    'Conversion rate (%)'     => (float) $newItem['conversion'],
                    'ATV'                     => (float) $newItem['atv'],
                    'Avg Items'               => (float) $newItem['avg_item'],
                    'Sales Yield'             => (float) $newItem['sales_yield'],
                    'Transactions'            => (int) $newItem['transactions'],
                    'Sales'                   => (int) $newItem['sales'],
                    'Missed Sales Opportunity' => (int) $newItem['missed_sales'],
                    'Sales hours'                 => (float) $newItem['sales_hour'],
                    'Shoppers on sales hour'      => (float) $newItem['shopper_on_s_h'],
                    'Sales on sales hour'         => (float) $newItem['sales_on_s_h'],
                    'Member Visits (%)'            => (float) $newItem['loyal_visits'],
                    'Member Transactions (%)'      => (float) $newItem['loyal_transactions'],
                    'Member CR (%)'   => (float) $newItem['loyal_conversion'],
                    'Lost member CR (%)'       => $newItem['loyal_conversion'] > 0 ? 100 - $newItem['loyal_conversion'] : 0,
                    'CX index (%)'                => (float) $newItem['cx_index'],
                    'NPS index (%)'               => (float) $newItem['nps_index'],
                );
            }
            $value_header =  $this->get_value_header_poc_in_out($start_date, $end_date, $start_time, $end_time, $org_name);
            $name = Carbon::today()->format('d_m_Y') . 'v' . rand(1, 1000);

            foreach ($items1 as $key => $value) {
                foreach ($index_not as $value2) {
                    unset($items1[$key][$value2]);
                }
            }

            Excel::create('PERFORMANCE_SITES_' . $name, function ($excel) use ($items1, $value_header) {
                $header = array(
                    'dong1' => 'Kết quả đo lường lưu lượng ra vào tại : ' . $value_header['value1'] . ' ',
                    'dong2' => 'Kết quả đo lường lưu lượng ra vào tại : ' . $value_header['value1'] . ' ',
                    'dong3' => 'Địa điểm: ' . $value_header['value1'] . '',
                    'dong4' => 'Thời gian:  ' . $value_header['value2'] . '  ',
                    'dong5' => 'Ngày:  ' . $value_header['value3'] . '  ',
                );
                $excel->setCreator('ACS')->setCompany('ACS Solution');

                $title1 = 'Địa điểm';
                $this->get_sheet_second($title1, $excel, $sheet, $header, $items1);
            })->store('xls', public_path('exports'));
            $file_name = 'PERFORMANCE_SITES_' . $name . '.xls';
        } catch (\Exception $exception) {
            return 'none';
        }
        return response()->json($file_name);
    }

    // Tính chênh lệch 2 giá trị
    public function tinh_chenh_lech($value1, $value2)
    {
        $result = $value1 - $value2;
        return abs($result);
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
            'value1' => $org_name1 .  ' so với ' . $org_name2,
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

    public function create_new_sheet(&$title, &$excel, &$sheet, &$header, &$items)
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
