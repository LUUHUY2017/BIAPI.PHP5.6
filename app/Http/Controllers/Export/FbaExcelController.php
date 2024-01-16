<?php

namespace App\Http\Controllers\Export;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\FbaQuestion;
use App\Site;
use App\Organization;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class FbaExcelController extends Controller
{
    public function export_customer_info_v2(Request $request)
    {
        try {
            // $this->fba_customer_info($request); // ??
            $request_user = $request->user();
            $user_id = $request_user != null ?  $request_user->id : null;
            if (isset($request->user_id))
                $user_id = $request->user_id;
            $params = json_decode($request->params);
            $organization_id = (int) $params->organization_id;
            $site_id = $params->site_id;

            $start_time = '\'' . $params->start_time . '\'';
            $end_time = '\'' . $params->end_time . '\'';

            $start_date = $request->start_date;
            $end_date = $request->end_date;

            $question_id = (int) $params->question_id;
            $customer_info = DB::select("exec sp_report_fba_customer_info $user_id, $organization_id, $site_id, $question_id, $start_time, $end_time, $start_date, $end_date");
            $question = FbaQuestion::find($question_id);
            $question_name      =  $question->question_name;

            $organization =  Organization::find($organization_id);
            $org_name =  $organization->organization_name;
            if ($site_id  != 0) {
                $site = Site::find($site_id);
                $org_name  = $site->site_name;
            }
            $Excel[] = array(
                'HỌ TÊN'                => ' ',
                'EMAIL'                 => ' ',
                'SỐ ĐIỆN THOẠI'         => ' ',
                'ĐÁNH GIÁ'              => ' ',
                'LÝ DO KHÔNG HÀI LÒNG'  => ' ',
                'Ý KIẾN BỔ SUNG'        => ' ',
                'THỜI GIAN'             => ' ',
            );
            // xử lý trùng lặp
            $data = [];
            $k = 0;
            $a = $customer_info;

            while (count($a) > 0) {
                $a = array_values($a);
                $item = $a[0];
                unset($a[0]);
                for ($i = 1; $i < count($a); $i++) {
                    if ($item->id === $a[$i]->id) {
                        $item->reason_name =    $item->reason_name . ', '  . $a[$i]->reason_name;
                        unset($a[$i]);
                    }
                }
                $data[$k] = $item;
                $k++;
            }

            foreach ($data as $item) {
                $recerviced_time = Carbon::parse($item->recerviced_time)->format('d/m/y H:i:s');
                $Excel[] = array(
                    'HỌ TÊN'                => $item->customer_name,
                    'EMAIL'                 => $item->customer_email,
                    'SỐ ĐIỆN THOẠI'         => $item->customer_phone,
                    'ĐÁNH GIÁ'              => $item->answer,
                    'LÝ DO KHÔNG HÀI LÒNG'  => $item->reason_name,
                    'Ý KIẾN BỔ SUNG'        => $item->other_reason_name,
                    'THỜI GIAN'             => $recerviced_time,
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
            $name = Carbon::today()->format('d-m-Y') . 'v' . rand(1, 1000);
            $value_header = array(
                'dong1' => $org_name,
                'dong2' => $org_name,
                'dong3' => $org_name,
                'dong4' => $question_name,
                'dong5' => str_replace('\'', '', $start_time) . ' - ' . str_replace('\'', '', $end_time),
                'dong6' => $ngay
            );
            Excel::create('ACS Customers Info ' . $name, function ($excel) use ($Excel, $value_header) {
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
                $excel->setDescription('Thông tin khách hàng');

                // tạo sheet1
                $excel->sheet('Thông tin', function ($sheet) use ($Excel, $header) {

                    // $sheet->cell('A1', function($cell) use ($header) {$cell->setValue($header['dong1']);});
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

                    // Cấu hình sheet
                    $sheet->getStyle('A0')->getAlignment()->applyFromArray(
                        array('horizontal' => 'center')
                    );
                    $sheet->setWidth(['A' => 18, 'B' => 18, 'C' => 15, 'D' => 15, 'E' => 30, 'F' => 25, 'g' => 20,]);
                    $sheet->setStyle(array('font' => array('name' => 'Times New Roman', 'size' =>  13)));  // 'bold'      =>  true
                    $sheet->setHeight(array(9 =>  27));
                    $sheet->fromArray($Excel, NULL, 'A8', true, true);
                });
            })->store('xls', public_path('exports'));
            $file_name = 'ACS Customers info ' . $name . '.xls';
            $response = [
                'status' => 1
                , 'fileName' => $file_name
                , 'contentFromReport' => $org_name
            ];
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [
                'status' => 0
                , 'message' => $e->getMessage()
                , 'line' => $e->getLine()
            ];
            return response()->json($response);
        }
    }
    public function export_report_reason_v2(Request $request)
    {
        try {
            $request_user = $request->user();
            $user_id = $request_user != null ?  $request_user->id : null;
                if (isset($request->user_id))
                    $user_id = $request->user_id;
            $params = json_decode($request->params);
            $organization_id = $params->organization_id;
            $site_id = $params->site_id;
            $question_id = $params->question_id;
            $start_time = '\'' . $params->start_time . '\'';
            $end_time = '\'' . $params->end_time . '\'';
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $question_id = (int) $params->question_id;

            $fba_reason_chart = DB::select("exec sp_fba_report_reason_version2 $user_id, $organization_id, $site_id, $question_id, $start_time, $end_time, $start_date, $end_date");
            $items_other = DB::select("exec sp_fba_report_reason_other_version2 $user_id, $organization_id, $site_id, $question_id, $start_time, $end_time, $start_date, $end_date");

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
            $total_reasons = DB::select("exec sp_fba_report_overview_total_by_site $user_id, $organization_id, $site_id, $question_id, $start_time, $end_time, $start_date, $end_date");
            $very_positive = 0;
            $positive = 0;
            $negative = 0;
            $very_negative = 0;
            $very_positive_reason = 0;
            $positive_reason = 0;
            $negative_reason = 0;
            $very_negative_reason = 0;
            $total_negative = 0;
            $total_response = 0;
            $total_reas = 0;
            $tongphanhoi1 = 0;
            $all_tieccuc = 0;
            // Lấy phản hồi mỗi đánh giá
            foreach ($items_other as $item) {
                if ($item->answer == 'very_positive')
                    $very_positive_reason = (int) $item->reason_count;
                if ($item->answer == 'positive')
                    $positive_reason = (int) $item->reason_count;
                if ($item->answer == 'negative')
                    $negative_reason = (int) $item->reason_count;
                if ($item->answer == 'very_negative')
                    $very_negative_reason = (int) $item->reason_count;
            }
            foreach ($total_reasons as $item) {
                // lấy tổng đánh giá
                $negative = (int) $item->negative;
                $very_negative = (int) $item->very_negative;
                $total_response = (int) $item->total_response;
            }
            $all_tieccuc = $negative +  $very_negative;
            // tính lại công thức phần trăm  để xuất excel
            foreach ($fba_reason_chart as $item) {
                // tổng tất cả phản hồi lý do ngoại trừ lý do khác
                $tongphanhoi1  += (int) $item->total_reason;
            }
            // tổng tất cả phản hồi lẫn lý do khác
            $tongphanhoi11 =  $tongphanhoi1 + $negative_reason + $very_negative_reason;

            $Excel[] = array(
                'ĐÁNH GIÁ'              => $all_tieccuc, // tổng đánh giá
                $negative_name       =>  $negative,
                $very_negative_name  =>  $very_negative,
                'PHẢN HỒI '          => $tongphanhoi11,
                $negative_name . ' '          => $all_tieccuc > 0 ?  (float) (number_format((($negative / ($all_tieccuc)) * 100), 2, '.', '')) . '% ' : 0,
                $very_negative_name . ' '     => $all_tieccuc > 0 ?  (float) (number_format((($very_negative / ($all_tieccuc)) * 100), 2, '.', '')) . '% ' : 0,
                'PHẢN HỒI' => '100%',
            );
            foreach ($fba_reason_chart as $item) {
                $negatives            = (int) $item->negative;
                $very_negatives        = (int) $item->very_negative;
                $total_reas           = (int) $item->total_reason;
                $Excel[] = array(
                    'ĐÁNH GIÁ'         => $item->reason_name,
                    $negative_name       => $negatives,
                    $very_negative_name  => $very_negatives,
                    'PHẢN HỒI ' => $total_reas,
                    $negative_name . ' '          => $tongphanhoi11 > 0 ?  (float) number_format((($negatives / ($tongphanhoi11)) * 100), 2, '.', '') . '% ' : 0,
                    $very_negative_name . ' '     => $tongphanhoi11 > 0 ?  (float) number_format((($very_negatives / ($tongphanhoi11)) * 100), 2, '.', '') . '% ' : 0,
                    'PHẢN HỒI' => ($total_reas)  > 0 ? (float) number_format(((($total_reas) / ($tongphanhoi11)) * 100), 2, '.', '') . '% ' : 0,
                );
            }
            // thêm lý do khác để xuất excel
            $Excel[] = array(
                'ĐÁNH GIÁ'         => 'Lý do khác',
                $negative_name             => $negative_reason,
                $very_negative_name        => $very_negative_reason,
                'PHẢN HỒI ' => ($negative_reason + $very_negative_reason),
                $negative_name . ' '        => $tongphanhoi11 > 0 ? (float) number_format((($negative_reason / ($tongphanhoi11)) * 100), 2, '.', '') . '% ' : 0,
                $very_negative_name . ' '   => $tongphanhoi11 > 0 ? (float) number_format((($very_negative_reason / ($tongphanhoi11)) * 100), 2, '.', '') . '% ' : 0,
                'PHẢN HỒI' => ($negative_reason + $very_negative_reason)  > 0 ? (float) number_format(((($negative_reason + $very_negative_reason) / ($tongphanhoi11)) * 100), 2, '.', '') . '% ' : 0,
            );

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
                'dong5' => str_replace('\'', '', $start_time) . ' - ' . str_replace('\'', '', $end_time),
                'dong6' => $ngay
            );
            Excel::create('ACS Reasons ' . $name, function ($excel) use ($Excel, $value_header) {
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
                $excel->setDescription('Báo cáo lý do');

                // tạo sheet1
                $excel->sheet('response', function ($sheet) use ($Excel, $header) {
                    // $sheet->cell('A1', function($cell) use ($header) {$cell->setValue($header['dong1']);});
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

                    // Cấu hình sheet
                    $sheet->getStyle('A0')->getAlignment()->applyFromArray(array('horizontal' => 'center'));
                    $sheet->setWidth(['A' => 18, 'B' => 15, 'C' => 15, 'D' => 15, 'E' => 15, 'F' => 15, 'G' => 15, 'H' => 15,]);
                    $sheet->setStyle(array('font' => array('name'  =>  'Times New Roman',  'size'  =>  13)));
                    $sheet->setHeight(array(9 =>  27));
                    $sheet->setOrientation('landscape');
                    $sheet->fromArray($Excel, null, 'A8', true, true);
                });
            })->store('xls', public_path('exports'));
            $file_name = 'ACS Reasons ' . $name . '.xls';
            $response = [
                'status' => 1
                , 'fileName' => $file_name
                , 'contentFromReport' => $org_name
            ];
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [
                'status' => 0
                , 'message' => $e->getMessage()
                , 'line' => $e->getLine()
            ];
            return $response;
        }
    }
    public function export_metrics_analytic_v2(Request $request)
    {
        try {
            $request_user    = $request->user();
            $user_id = $request_user != null ?  $request_user->id : null;
            if (isset($request->user_id))
                $user_id = $request->user_id;
            $params = json_decode($request->params);
            $organization_id = (int) $params->organization_id;
            $site_id         = (int) $params->site_id;
            $start_time      = '\'' . $params->start_time . '\'';
            $end_time        = '\'' . $params->end_time . '\'';
            $start_date      = $request->start_date;
            $end_date        = $request->end_date;
            $question_id     = (int) $params->question_id; // 0
            $view_by         = $params->view_by;
            $metrics  = DB::select("exec sp_fba_report_metrics_analytics $user_id, $organization_id, $site_id, $question_id, $start_time, $end_time, $start_date, $end_date, $view_by");

            $question = FbaQuestion::find($question_id);
            $question_name      =  $question->question_name;
            $very_negative_name =  $question->very_negative;
            $negative_name      =  $question->negative;
            $very_positive_name =  $question->very_positive;
            $positive_name      =  $question->positive;

            $organization =  Organization::find($organization_id);
            $org_name =  $organization->organization_name;
            if ($site_id  != 0) {
                $site = Site::find($site_id);
                $org_name  = $site->site_name;
            }
            // Lấy tổng đánh giá để tính phần trăm
            $all_negative = 0;
            $all_very_negative = 0;
            $all_very_positive = 0;
            $all_positive = 0;
            $total_all_response = 0;
            $all_cx_index = 0;
            foreach ($metrics as $item) {
                $all_very_positive  += (int) $item->very_positive;
                $all_positive  += (int) $item->positive;
                $all_negative  += (int) $item->negative;
                $all_very_negative  += (int) $item->very_negative;
            }
            $total_all_response = $all_negative + $all_very_negative + $all_very_positive + $all_positive;

            $all_very_positive_per =  $total_all_response > 0 ?  (float) ($all_very_positive /  $total_all_response) * 100 : 0;
            $all_negative_per =    $total_all_response > 0 ? (float) ($all_negative /  $total_all_response) * 100 : 0;
            $all_very_negative_per =   $total_all_response > 0 ?  (float) ($all_very_negative /  $total_all_response) * 100 : 0;
            $all_cx_index = $total_all_response > 0 ? (float) number_format((($all_very_positive * 100) + ($all_positive * 66.66) + ($all_negative * 33.33)) / ($total_all_response), 2, '.', '')  : 0;
            $all_nps_index = (float) number_format(($all_very_positive_per -  $all_negative_per - $all_very_negative_per), 2, '.', '');
            // tính lại công thức phần trăm
            $items1[] = array(
                'THỜI GIAN'                   =>  '',
                $very_positive_name       => $all_very_positive,
                $positive_name            => $all_positive,
                $negative_name            => $all_negative,
                $very_negative_name       => $all_very_negative,
                'ĐÁNH GIÁ'                => $total_all_response,
                'CX Index'                => $all_cx_index,
                'NPS Index'               => $all_nps_index,
            );
            $items2[] = array(
                'THỜI GIAN'                   =>  '',
                $very_positive_name       => $all_very_positive,
                $positive_name            => $all_positive,
                $negative_name            => $all_negative,
                $very_negative_name       => $all_very_negative,
                'ĐÁNH GIÁ'                => $total_all_response,
                'CX Index'                => $all_cx_index,
                'NPS Index'               => $all_nps_index,
            );
            foreach ($metrics as $item) {
                $negative             = (int) $item->negative;
                $very_negative        = (int) $item->very_negative;
                $very_positive        = (int) $item->very_positive;
                $positive             = (int) $item->positive;
                $total_response       = (int) $item->total_response;
                $very_negative_percen = ($very_negative) > 0 ?   (float) number_format((($very_negative / ($total_all_response)) * 100), 2, '.', '') : 0;
                $negative_percen      = ($negative) > 0 ?  (float) number_format((($negative / ($total_all_response)) * 100), 2, '.', '') : 0;
                $very_positive_percen = ($very_positive) > 0 ?   (float) number_format((($very_positive / ($total_all_response)) * 100), 2, '.', '') : 0;
                $positive_percen      = ($positive) > 0 ?   (float) number_format((($positive / ($total_all_response)) * 100), 2, '.', '') : 0;
                $total_response_percen = ($total_response) > 0 ?   (float) number_format((($total_response / ($total_all_response)) * 100), 2, '.', '') : 0;
                $cx_index             =  (float) $item->cx_index;
                $nps_index            =  (float) $item->nps_index;
                $time_period          = $item->time_period;

                $items1[] = array(
                    'THỜI GIAN'                   =>  $time_period,
                    $very_positive_name       => $very_positive,
                    $positive_name            => $positive,
                    $negative_name            => $negative,
                    $very_negative_name       => $very_negative,
                    'ĐÁNH GIÁ'                => $total_response,
                    'CX Index'                => $cx_index,
                    'NPS Index'               => $nps_index,
                );
                $items2[] = array(
                    'THỜI GIAN'                    => $time_period,
                    $very_positive_name        => $very_positive_percen,
                    $positive_name             => $positive_percen,
                    $negative_name             => $negative_percen,
                    $very_negative_name        => $very_negative_percen,
                    'ĐÁNH GIÁ'                  => $total_response_percen,
                    'CX Index'                  => $cx_index,
                    'NPS Index'                 => $nps_index,
                );
            }

            if ($start_date === $end_date) {
                $stylengay =  str_replace('\'', '', $start_date);
                $ngay =  Carbon::parse($stylengay)->format('d/m/Y');
            } else {
                $st_date =  str_replace('\'', '', $start_date);
                $start   =   Carbon::parse($st_date)->format('d/m/Y');
                $en_date =  str_replace('\'', '', $end_date);
                $en      =   Carbon::parse($en_date)->format('d/m/Y');
                $ngay    =   $start . ' _ ' .  $en;
            }
            $name    = Carbon::today()->format('d-m-Y') . 'v' . rand(1, 1000);
            $value_header = array(
                'dong1' => $org_name,
                'dong2' => $org_name,
                'dong3' => $org_name,
                'dong4' => $question_name,
                'dong5' => str_replace('\'', '', $start_time) . ' - ' . str_replace('\'', '', $end_time),
                'dong6' => $ngay
            );
            Excel::create('ACS Metrics Analytics ' . $name, function ($excel) use ($items1, $items2, $value_header) {
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
                $excel->setDescription('Báo cáo phân tích chỉ số');

                $title1 = 'Đánh giá';
                $title2 = 'Phần trăm';
                $this->get_sheet($title1, $excel, $sheet, $items1, $header);
                $this->get_sheet($title2, $excel, $sheet, $items2, $header);
            })->store('xls', public_path('exports'));
            $file_name = 'ACS Metrics Analytics ' . $name . '.xls';
            $response = [
                'status' => 1
                , 'fileName' => $file_name
                , 'contentFromReport' => $org_name
            ];
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [
                'status' => 0
                , 'message' => $e->getMessage()
                , 'line' => $e->getLine()
            ];
            return $response;
        }
    }
    public function export_metrics_comparison_v2(Request $request)
    {
        try {
            $request_user = $request->user();
            $user_id = $request_user != null ?  $request_user->id : null;
            if (isset($request->user_id))
                $user_id = $request->user_id;
            $params = json_decode($request->params);
            $organization_id = $params->organization_id;
            $site_id = $params->site_id;
            $question_id = $params->question_id;
            $start_time = '\'' . $params->start_time . '\'';
            $end_time = '\'' . $params->end_time . '\'';
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $question_id = (int) $params->question_id;
            $category_id = $params->category_id;

            $itemsparent = DB::select("exec sp_fba_report_metrics_comparison $user_id, $organization_id, $site_id, $question_id, $start_time, $end_time, $start_date, $end_date, $category_id");
            $itemschild = DB::select("exec sp_fba_report_metrics_comparison $user_id, $organization_id, $site_id, $question_id, $start_time, $end_time, $start_date, $end_date, $category_id");

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
            $metrics  = DB::select("exec sp_fba_report_metrics_analytics $user_id, $organization_id, $site_id, $question_id, $start_time, $end_time, $start_date, $end_date, N'Month'");
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
                'dong5' => str_replace('\'', '', $start_time) . ' - ' . str_replace('\'', '', $end_time),
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
            $response = [
                'status' => 1
                , 'fileName' => $file_name
                , 'contentFromReport' => $org_name
            ];
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [
                'status' => 0
                , 'message' => $e->getMessage()
                , 'line' => $e->getLine()
            ];
            return $response;
        }
    }

    public function get_sheet(&$title, &$excel, &$sheet, &$items, &$header)
    {
        $excel->sheet($title, function ($sheet) use ($items, $header) {
            // Cấu hình sheet
            $sheet->getStyle('A0')->getAlignment()->applyFromArray(array('horizontal' => 'center'));
            $sheet->setWidth(['A' => 18, 'B' => 13, 'C' => 13, 'D' => 13, 'E' => 13, 'F' => 13, 'G' => 13, 'H' => 13,]);
            $sheet->setHeight(array(9 =>  27));
            $sheet->setOrientation('landscape');

            // $sheet->cell('A1', function($cell) use ($header) {$cell->setValue($header['dong1']);});
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
