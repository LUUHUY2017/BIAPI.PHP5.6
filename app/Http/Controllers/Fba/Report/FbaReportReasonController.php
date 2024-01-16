<?php

namespace App\Http\Controllers\Fba\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\FbaQuestion;
use App\Site;
use App\Organization;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class FbaReportReasonController extends Controller
{
    public function fba_report_reason(Request $request)
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

            $fba_reason_chart = DB::select("exec sp_fba_report_reason_version2 $user_id, $organization_id, $site_id, $question_id, $start_hour, $end_hour, $start_date, $end_date");
            $items_other = DB::select("exec sp_fba_report_reason_other_version2 $user_id, $organization_id, $site_id, $question_id, $start_hour, $end_hour, $start_date, $end_date");

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

            $question = FbaQuestion::find($question_id);
            if ($question) {
                $question_name      =  $question->question_name;
                $very_negative_name =  $question->very_negative;
                $negative_name      =  $question->negative;
                $very_positive_name =  $question->very_positive;
                $positive_name      = $question->positive;
            } else {
                $question_name      =  '';
                $very_negative_name =  '';
                $negative_name      = '';
                $very_positive_name =  '';
                $positive_name      = '';
            }

            $organization =  Organization::find($organization_id);
            $org_name =  $organization->organization_name;
            if ($site_id  != 0) {
                $site = Site::find($site_id);
                $org_name  = $site->site_name;
            }
            // tổng đánh giá lý do , không phải lấy phản hồi mỗi lý do

            $total_reasons = DB::select("exec sp_fba_report_overview_total_by_site $user_id, $organization_id, $site_id, $question_id, $start_hour, $end_hour, $start_date, $end_date");
            foreach ($total_reasons as $item) {
                $negative = (int) $item->negative;
                $very_negative = (int) $item->very_negative;
                $total_response = (int) $item->total_response;
            }
            $total_reason[] = array(
                'negative' => (int) $negative,
                'very_negative' => (int) $very_negative,
                'total_response' => $total_response,
                'total_negative' => (int) ($negative + $very_negative),
            );
            // Lấy lý do khác
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

            foreach ($fba_reason_chart as $item) {
                $negative             = (int) $item->negative;
                $very_negative        = (int) $item->very_negative;
                $total_reas           = (int) $item->total_reason;
            }
            // thêm lý do khác vào chart
            $fba_reason_chart[] = array(
                'reason_id' => 0,
                'reason_name' => 'Lý do khác',
                'very_positive' => $very_positive_reason,
                'positive' => $positive_reason,
                'negative' => $negative_reason,
                'very_negative' => $very_negative_reason,
                'total_reason' => ($very_positive_reason + $positive_reason + $negative_reason + $very_negative_reason),
                'total_negative' => ($negative_reason + $very_negative_reason),
            );
            return response()->json(['fba_reason_chart' => $fba_reason_chart, 'total_reason' => $total_reason]);
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }
    public function export_report_reason(Request $request)
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

            $fba_reason_chart = DB::select("exec sp_fba_report_reason_version2 $user_id, $organization_id, $site_id, $question_id, $start_hour, $end_hour, $start_date, $end_date");
            $items_other = DB::select("exec sp_fba_report_reason_other_version2 $user_id, $organization_id, $site_id, $question_id, $start_hour, $end_hour, $start_date, $end_date");

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
            $total_reasons = DB::select("exec sp_fba_report_overview_total_by_site $user_id, $organization_id, $site_id, $question_id, $start_hour, $end_hour, $start_date, $end_date");
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
                'dong5' => str_replace('\'', '', $start_hour) . ' - ' . str_replace('\'', '', $end_hour),
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
            return response()->json($file_name);
        } catch (\Exception $exception) {
            return 'none';
        }
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
