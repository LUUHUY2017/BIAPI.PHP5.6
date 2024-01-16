<?php

namespace App\Http\Controllers\Fba\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;

class FbaReportOverviewController extends Controller
{
    public function sp_fba_overview_total(Request $request)
    {
        try {
            $request_user = $request->user();
            $user_id = $request_user->id;

            $organization_id = $request->organization_id;
            $site_id = $request->site_id;
            $question_id = $request->question_id;

            $start_hour = $request->start_hour;
            $end_hour = $request->end_hour;

            $start_date = $request->start_date;
            $end_date = $request->end_date;

            $start_date_pre = $request->start_date_pre;
            $end_date_pre = $request->end_date_pre;

            $question_id = intval($request->question_id);

            // Lấy dữ liệu so sánh theo id của tổ chức và id của site
            $items = DB::select("exec sp_fba_report_overview_total_by_site $user_id, $organization_id, $site_id, $question_id, $start_hour, $end_hour, $start_date, $end_date");
            $data = $this->convert_data($items);

            // Lấy dữ liệu của kỳ trước
            $items_pre = DB::select("exec sp_fba_report_overview_total_by_site $user_id, $organization_id, $site_id, $question_id, $start_hour, $end_hour, $start_date_pre, $end_date_pre");
            $data_pre = $this->convert_data($items_pre);

            // nhóm các điểm có kết quả tốt nhất và kết quả xấu nhất theo cx_index và nps_index
            // tính tổng theo site_id dựa vào location_id
            $data_by_site = DB::select("exec sp_fba_question_response_group_by_site $user_id, $organization_id, $site_id, $question_id, $start_hour, $end_hour, $start_date, $end_date");
            $data_by_site_pre = DB::select("exec sp_fba_question_response_group_by_site $user_id, $organization_id, $site_id, $question_id, $start_hour, $end_hour, $start_date_pre, $end_date_pre");

            // Lý do khách hàng kém hài lòng nhất
            $top_reason = DB::select("exec sp_fba_report_overview_top_reason_very_negative_and_negative $user_id, $organization_id, $site_id, $question_id, $start_hour, $end_hour, $start_date, $end_date");

            // Nhóm các điểm có số lượng đánh giá nhiều nhất
            $top_sites_respons = DB::select("exec sp_fba_report_overview_top_sites_response $user_id, $organization_id, $site_id, $question_id, $start_hour, $end_hour, $start_date, $end_date");
            $top_sites_respons_pre = DB::select("exec sp_fba_report_overview_top_sites_response $user_id, $organization_id, $site_id, $question_id, $start_hour, $end_hour, $start_date, $end_date");

            // Nhóm các điểm có số lượng đánh giá nhiều nhất



            return response()->json(array(
                'data' => $data, 'data_pre' => $data_pre, 'data_by_site' => $data_by_site, 'data_by_site_pre' => $data_by_site_pre, 'top_reason' => $top_reason, 'top_sites_respons' => $top_sites_respons, 'top_sites_respons_pre' => $top_sites_respons_pre

            ));
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }
    private function convert_data($items)
    {
        $data_arr = array();
        if ($items) {
            $item = $items[0];

            $very_positive = $this->convert_to_int($item->very_positive);
            $positive = $this->convert_to_int($item->positive);
            $negative = $this->convert_to_int($item->negative);
            $very_negative = $this->convert_to_int($item->very_negative);

            $total_response = $this->convert_to_int($item->total_response);

            $very_positive_percen = $this->convert_to_float($item->very_positive_percen);
            $positive_percen =  $this->convert_to_float($item->positive_percen);
            $negative_percen =  $this->convert_to_float($item->negative_percen);
            $very_negative_percen =  $this->convert_to_float($item->very_negative_percen);

            $cx_index = $this->convert_to_float($item->cx_index);

            $nps_index = $this->convert_to_float($item->nps_index);

            $data_arr = array(
                'very_positive' => $very_positive,
                'positive' => $positive,
                'negative' => $negative,
                'very_negative' => $very_negative,
                'total_response' => $total_response,

                'very_positive_percen' => $very_positive_percen,
                'positive_percen' => $positive_percen,
                'negative_percen' => $negative_percen,
                'very_negative_percen' => $very_negative_percen,

                'cx_index' => $cx_index,
                'nps_index' => $nps_index,
            );
        }
        return $data_arr;
    }
    private function convert_to_int($val)
    {
        if (!$val)
            return 0;
        return (int) $val;
    }
    private function convert_to_float($val)
    {
        if (!$val)
            return 0;
        $ret_val = round(floatval($val), 2);
        return $ret_val;
    }
}
