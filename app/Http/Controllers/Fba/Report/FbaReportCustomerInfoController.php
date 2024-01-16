<?php

namespace App\Http\Controllers\Fba\Report;

use Illuminate\Http\Request;
use App\FbaQuestion;
use App\Site;
use App\Organization;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class FbaReportCustomerInfoController extends Controller
{
    public function fba_customer_info(Request $request)
    {
        try {
            $request_user = $request->user();
            $user_id = $request_user->id;
            $organization_id = (int) $request->organization_id;
            $site_id = $request->site_id;

            $start_hour = $request->start_hour;
            $end_hour = $request->end_hour;

            $start_date = $request->start_date;
            $end_date = $request->end_date;

            $question_id = (int) $request->question_id;
            // procedure: sp_report_fba_customer_info Chỉ lấy dữ liệu khách hàng nhập thông tin
            $customer_info = DB::select("exec sp_report_fba_customer_info  $user_id, $organization_id, $site_id, $question_id, $start_hour, $end_hour, $start_date, $end_date");
            return response()->json(['customer_info' => $customer_info]);
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }

    public function export_customer_info(Request $request)
    {
        try {
            // $this->fba_customer_info($request); // ??
            $request_user = $request->user();
            $user_id = $request_user != null ?  $request_user->id : null;
            if (isset($request->user_id))
                $user_id = $request->user_id;
            $organization_id = (int) $request->organization_id;
            $site_id = $request->site_id;

            $start_hour = $request->start_hour;
            $end_hour = $request->end_hour;

            $start_date = $request->start_date;
            $end_date = $request->end_date;

            $question_id = (int) $request->question_id;
            $customer_info = DB::select("exec sp_report_fba_customer_info $user_id, $organization_id, $site_id, $question_id, $start_hour, $end_hour, $start_date, $end_date");
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
                'dong5' => str_replace('\'', '', $start_hour) . ' - ' . str_replace('\'', '', $end_hour),
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
            return response()->json($file_name);
        } catch (\Exception $e) {
            return 'none';
        }
    }

    public function fba_customer_info_response(Request $request)
    {
        try {
            $request_user = $request->user();
            $user_id = $request_user->id;
            $organization_id = (int) $request->organization_id;
            $site_id = $request->site_id;

            $start_hour = $request->start_hour;
            $end_hour = $request->end_hour;

            $start_date = $request->start_date;
            $end_date = $request->end_date;

            $question_id = (int) $request->question_id;
            // procedure: sp_report_fba_customer_info Chỉ lấy dữ liệu khách hàng nhập thông tin
            $customer_info = DB::select("exec sp_fba_report_detail_response  $user_id, $organization_id, $site_id, $question_id, $start_hour, $end_hour, $start_date, $end_date");
            return response()->json(['customer_info' => $customer_info]);
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }

    public function export_customer_info_response(Request $request)
    {
        try {
            // $this->fba_customer_info($request); // ??
            $request_user = $request->user();
            $user_id = $request_user != null ?  $request_user->id : null;
            if (isset($request->user_id))
                $user_id = $request->user_id;
            $organization_id = (int) $request->organization_id;
            $site_id = $request->site_id;

            $start_hour = $request->start_hour;
            $end_hour = $request->end_hour;

            $start_date = $request->start_date;
            $end_date = $request->end_date;

            $question_id = (int) $request->question_id;
            $customer_info = DB::select("exec sp_fba_report_detail_response $user_id, $organization_id, $site_id, $question_id, $start_hour, $end_hour, $start_date, $end_date");
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
                'dong5' => str_replace('\'', '', $start_hour) . ' - ' . str_replace('\'', '', $end_hour),
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
            return response()->json($file_name);
        } catch (\Exception $e) {
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
