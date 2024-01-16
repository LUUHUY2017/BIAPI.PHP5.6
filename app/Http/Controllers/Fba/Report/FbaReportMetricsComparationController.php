<?php

namespace App\Http\Controllers\Fba\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\FbaQuestion;
use App\Site;
use App\Organization;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class FbaReportMetricsComparationController extends Controller
{
    public function get_category(Request $request)
    {
        $request_user = $request->user();
        $user_id =  $request_user->id;
        $site_id = $request->site_id;
        $organization_id = $request->organization_id;
        $categories_fba = DB::select("exec sp_fba_get_categories $user_id,$organization_id, $site_id");
        $categories_admin = DB::select("exec sp_ad_get_categories_for_site $organization_id");
        $category_admin = array();
        foreach ($categories_admin as $item) {
            $category_admin[] =  array('label' => $item->category_name, 'value' => strval($item->id));
        }
        return response()->json(['categories_fba' => $categories_fba, 'categories_admin' => $category_admin]);
    }

    public function get_metrics_comparison(Request $request)
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
            $question_id = (int) $request->question_id;
            $category_id = $request->category_id;

            $itemsparent = DB::select("exec sp_fba_report_metrics_comparison $user_id, $organization_id, $site_id, $question_id, $start_hour, $end_hour, $start_date, $end_date, $category_id");
            $itemschild = DB::select("exec sp_fba_report_metrics_comparison $user_id, $organization_id, $site_id, $question_id, $start_hour, $end_hour, $start_date, $end_date, $category_id");
            return response()->json(['parent' => $itemsparent, 'child' => $itemschild]);
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }

    public function export_metrics_comparison(Request $request)
    {
        try {
            $request_user = $request->user();
            $user_id = $request_user != null ?  $request_user->id : null;
            if (isset($request->user_id))
                $user_id = $request->user_id;
            $organization_id = $request->organization_id;
            $site_id = $request->site_id;
            $question_id = $request->question_id;
            $start_hour = $request->start_hour;
            $end_hour = $request->end_hour;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $question_id = (int) $request->question_id;
            $category_id = $request->category_id;

            $itemsparent = DB::select("exec sp_fba_report_metrics_comparison $user_id, $organization_id, $site_id, $question_id, $start_hour, $end_hour, $start_date, $end_date, $category_id");
            $itemschild = DB::select("exec sp_fba_report_metrics_comparison $user_id, $organization_id, $site_id, $question_id, $start_hour, $end_hour, $start_date, $end_date, $category_id");

            $question = FbaQuestion::find($question_id);
            $question_name      =  $question->question_name;
            $very_negative_name =  $question->very_negative;
            $negative_name      =  $question->negative;
            $very_positive_name =  $question->very_positive;
            $positive_name      = $question->positive;

            $organization =  Organization::find($organization_id);
            $org_name =  $organization->organization_name;
            if ($site_id  != 0) {
                $site = Site::find($site_id);
                $org_name  = $site->site_name;
            }
            $all_negative = 0;
            $all_very_negative = 0;
            $all_very_positive = 0;
            $all_positive = 0;
            $total_all_response = 0;
            $all_cx_index = 0;
            $metrics  = DB::select("exec sp_fba_report_metrics_analytics $user_id, $organization_id, $site_id, $question_id, $start_hour, $end_hour, $start_date, $end_date, N'Month'");
            foreach ($metrics as $item) {
                $all_very_positive  += (int) $item->very_positive;
                $all_positive  += (int) $item->positive;
                $all_negative  += (int) $item->negative;
                $all_very_negative  += (int) $item->very_negative;
            }
            $total_all_response = $all_negative + $all_very_negative + $all_very_positive + $all_positive;

            $all_very_positive_per = $total_all_response > 0 ?  (float) number_format((($all_very_positive /  $total_all_response) * 100), 2, '.', '') : 0;
            $all_positive_per =  $total_all_response > 0 ?   (float) number_format((($all_positive /  $total_all_response) * 100), 2, '.', '') : 0;
            $all_negative_per = $total_all_response > 0 ?    (float) number_format((($all_negative /  $total_all_response) * 100), 2, '.', '') : 0;
            $all_very_negative_per =  $total_all_response > 0 ?   (float) number_format((($all_very_negative /  $total_all_response) * 100), 2, '.', '') : 0;
            $all_cx_index = $total_all_response > 0 ?  (float) number_format((($all_very_positive * 100) + ($all_positive * 66.66) + ($all_negative * 33.33)) / ($total_all_response), 2, '.', '') : 0;
            $all_nps_index = (float) number_format(($all_very_positive_per -  $all_negative_per - $all_very_negative_per), 2, '.', '');

            $items1[] = array(
                'ĐỊA ĐIỂM'                 => '',
                'ĐÁNH GIÁ'                 => $total_all_response,
                $very_positive_name        => $all_very_positive,
                $positive_name            => $all_positive,
                $negative_name            => $all_negative,
                $very_negative_name       => $all_very_negative,
                'CX Index'                => $all_cx_index,
                'NPS Index'               => $all_nps_index,
            );
            $items2[] = array(
                'ĐỊA ĐIỂM'                 => '',
                'ĐÁNH GIÁ'                 => 100,
                $very_positive_name        => $all_very_positive_per,
                $positive_name            => $all_positive_per,
                $negative_name            => $all_negative_per,
                $very_negative_name       => $all_very_negative_per,
                'CX Index'                => $all_cx_index,
                'NPS Index'               => $all_nps_index,
            );

            // tính lại công thức phần trăm
            foreach ($itemsparent as $item) {
                $site_name            = $item->site_name;
                $negative             = (int) $item->negative;
                $very_negative        = (int) $item->very_negative;
                $very_positive        = (int) $item->very_positive;
                $positive             = (int) $item->positive;
                $total_response       = (int) $item->total_response;
                $very_negative_percen = ($very_negative) > 0 ?  (float) number_format((($very_negative / ($total_all_response)) * 100), 2, '.', '') : 0;
                $negative_percen      = ($negative) > 0 ?  (float) number_format((($negative / ($total_all_response)) * 100), 2, '.', '') : 0;
                $very_positive_percen = ($very_positive) > 0 ?  (float) number_format((($very_positive / ($total_all_response)) * 100), 2, '.', '') : 0;
                $positive_percen      = ($positive) > 0 ?  (float) number_format((($positive / ($total_all_response)) * 100), 2, '.', '') : 0;
                $total_response_percen = ($total_response) > 0 ?  (float) number_format((($total_response / ($total_all_response)) * 100), 2, '.', '') : 0;
                $cx_index             = (float) $item->cx_index;
                $nps_index            = (float) $item->nps_index;

                $items1[] = array(
                    'ĐỊA ĐIỂM'                 => $site_name,
                    'ĐÁNH GIÁ'                 => $total_response,
                    $very_positive_name        => $very_positive,
                    $positive_name            => $positive,
                    $negative_name            => $negative,
                    $very_negative_name       => $very_negative,
                    'CX Index'                => $cx_index,
                    'NPS Index'               => $nps_index,
                );
                $items2[] = array(
                    'ĐỊA ĐIỂM'                 => $site_name,
                    'ĐÁNH GIÁ'                 => $total_response_percen,
                    $very_positive_name        => $very_positive_percen,
                    $positive_name             => $positive_percen,
                    $negative_name             => $negative_percen,
                    $very_negative_name        => $very_negative_percen,
                    'CX Index'                     => $cx_index,
                    'NPS Index'                    => $nps_index,
                );
            }

            if ($start_date === $end_date) {
                $stylengay =  str_replace('\'', '', $start_date);
                $ngay =   Carbon::parse($stylengay)->format('d/m/Y');
            } else {
                $st_date =  str_replace('\'', '', $start_date);
                $start =   Carbon::parse($st_date)->format('d/m/Y');
                $en_date =  str_replace('\'', '', $end_date);
                $en =   Carbon::parse($en_date)->format('d/m/Y');
                $ngay =   $start . ' _ ' .  $en;
            }
            $name = Carbon::today()->format('d-m-Y') . 'v' . rand(10, 1000);
            $value_header = array(
                'dong1' => $org_name,
                'dong2' => $org_name,
                'dong3' => $org_name,
                'dong4' => $question_name,
                'dong5' => str_replace('\'', '', $start_hour) . ' - ' . str_replace('\'', '', $end_hour),
                'dong6' => $ngay
            );
            Excel::create('ACS Metrics Comparation ' . $name, function ($excel) use ($items1, $items2, $value_header) {
                $header = array(
                    'dong1' => 'Kết quả ACS SMILEYS tại: ' . $value_header['dong1'] . ' ',
                    'dong2' => 'Kết quả đo lường mức độ hài lòng của khách hàng : ' . $value_header['dong2'] . ' ',
                    'dong3' => 'Địa điểm: ' . $value_header['dong3'] . '',
                    'dong4' => 'Câu hỏi khảo sát:  ' . $value_header['dong4'] . ' ',
                    'dong5' => 'Thời gian:  ' . $value_header['dong5'] . '  ',
                    'dong6' => 'Ngày:  ' . $value_header['dong6'] . '  ',
                );
                // Tiêu đề ngoài file
                $excel->setTitle('Báo cáo kết quả trải nghiệm khách hàng');
                $excel->setCreator('ACS')->setCompany('ACS Solution');
                $excel->setDescription('Báo cáo so sánh');

                $title1 = 'Đánh giá';
                $title2 = 'Phần trăm';
                $this->get_sheet($title1, $excel, $sheet, $items1, $header);
                $this->get_sheet($title2, $excel, $sheet, $items2, $header);
            })->store('xls', public_path('exports'));
            $file_name = 'ACS Metrics Comparation ' . $name . '.xls';
            return response()->json($file_name);
        } catch (\Exception $e) {
            return 'none';
        }
    }

    public function get_sheet(&$title, &$excel, &$sheet, &$items, &$header)
    {
        $excel->sheet($title, function ($sheet) use ($items, $header) {
            // Cấu hình sheet
            $sheet->getStyle('A0')->getAlignment()->applyFromArray(array('horizontal' => 'center'));
            $sheet->setWidth(['A' => 18, 'B' => 15, 'C' => 15, 'D' => 15, 'E' => 15, 'F' => 15, 'G' => 15, 'H' => 15,]);
            $sheet->setOrientation('landscape');
            $sheet->setHeight(array(9 =>  27));
            //   $sheet->cell('A1', function($cell) use ($header) {$cell->setValue($header['dong1']);});
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
            $sheet->cell('A5', function ($cell) use ($header) {
                $cell->setValue($header['dong6']);
            });
            $sheet->fromArray($items, NULL, 'A8', true, true);
        });
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
}
