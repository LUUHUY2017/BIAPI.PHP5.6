<?php

namespace App\Http\Controllers\Export;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DateTime;
use App\Organization;
use App\Site;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AgeExcelController extends Controller
{
    public function get_value_header_gender_age($start_date, $end_date, $start_time, $end_time, $org_name)
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
    public function sp_poc_gender_metric_analytic_export_excel_v2(Request $request)
    {
        try {
            $request_user = $request->user();
            $user_id = $request_user != null ?  $request_user->id : null;
            if (isset($request->user_id))
                $user_id = $request->user_id;
            $params = json_decode($request->params);
            $organization_id = $params->organization_id;
            $site_id = $params->site_id;
            $start_time = '\'' . $params->start_time . '\'';
            $end_time = '\'' . $params->end_time . '\'';
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $view_by = $params->view_by;
            $items = DB::select("exec sp_poc_gender_metric_analytic $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date, $end_date, $view_by");

            $organization =  Organization::find($organization_id);
            $org_name =  $organization->organization_name;
            if ($site_id  != 0) {
                $site = Site::find($site_id);
                $org_name  = $site->site_name;
            }
            // Lấy tổng đánh giá để tính phần trăm
            $age18_t = 0;
            $age18_24_t = 0;
            $age25_34_t = 0;
            $age35_44_t = 0;
            $age45_54_t = 0;
            $age55_64_t = 0;
            $age65_t = 0;
            $unknown_t = 0;
            $total_age = 0;
            $total_age_no = 0;
            foreach ($items as $item) {
                $age18_t         += (int) $item->age18;
                $age18_24_t      += (int) $item->age18_24;
                $age25_34_t      += (int) $item->age25_34;
                $age35_44_t      += (int) $item->age35_44;
                $age45_54_t      += (int) $item->age45_54;
                $age55_64_t      += (int) $item->age55_64;
                $age65_t         += (int) $item->age65;
                $unknown_t         += (int) $item->unknown;
            }
            $total_age_no  = $age18_t + $age18_24_t + $age25_34_t + $age35_44_t +  $age45_54_t  + $age55_64_t + $age65_t;
            $total_age  = $age18_t + $age18_24_t + $age25_34_t + $age35_44_t +  $age45_54_t  + $age55_64_t + $age65_t + $unknown_t;
            $age18_t_per = ($age18_t) > 0 ? (float) number_format((($age18_t / ($total_age_no)) * 100), 2, '.', '') : 0;
            $age18_24_t_per = ($age18_24_t) > 0 ? (float) number_format((($age18_24_t / ($total_age_no)) * 100), 2, '.', '') : 0;
            $age25_34_t_per = ($age25_34_t) > 0 ? (float) number_format((($age25_34_t / ($total_age_no)) * 100), 2, '.', '') : 0;
            $age35_44_t_per = ($age35_44_t) > 0 ? (float) number_format((($age35_44_t / ($total_age_no)) * 100), 2, '.', '') : 0;
            $age45_54_t_per = ($age45_54_t) > 0 ? (float) number_format((($age45_54_t / ($total_age_no)) * 100), 2, '.', '') : 0;
            $age55_64_t_per = ($age55_64_t) > 0 ? (float) number_format((($age55_64_t / ($total_age_no)) * 100), 2, '.', '') : 0;
            $age65_t_per = ($age65_t) > 0 ? (float) number_format((($age65_t / ($total_age_no)) * 100), 2, '.', '') : 0;
            try {
                $unknown_t_per = ($unknown_t) > 0 ? (float) number_format((($unknown_t / ($total_age_no)) * 100), 2, '.', '') : 0;
            } catch (\Exception $e) {
                $unknown_t_per = 0;
            }
            
            $total_row_t_per = $total_age_no > 0 ? (float) number_format(100) : 0;
            // $items1[] = array(
            //     'Thời Gian'          =>  '',
            //     'Tất Cả'             => $total_age,
            //     '18-'                => $age18_t,
            //     '18-24'              => $age18_24_t,
            //     '25-34'              => $age25_34_t,
            //     '35-44'              => $age35_44_t,
            //     '45-54'              => $age45_54_t,
            //     '55-64'              => $age55_64_t,
            //     '65+'                => $age65_t,
            //     'Không xác định'     => $unknown_t,
            // );
            $items2[] = array(
                'Thời Gian'          =>  '',
                'Tất Cả'             => $total_row_t_per,
                '18-'                => $age18_t_per,
                '18-24'              => $age18_24_t_per,
                '25-34'              => $age25_34_t_per,
                '35-44'              => $age35_44_t_per,
                '45-54'              => $age45_54_t_per,
                '55-64'              => $age55_64_t_per,
                '65+'                => $age65_t_per,
                // 'Không xác định'     => $unknown_t_per,
            );
            foreach ($items as $item) {
                $age18       = (int) $item->age18;
                $age18_24    = (int) $item->age18_24;
                $age25_34    = (int) $item->age25_34;
                $age35_44    = (int) $item->age35_44;
                $age45_54    = (int) $item->age45_54;
                $age55_64    = (int) $item->age55_64;
                $age65       = (int) $item->age65;
                $unknown       = (int) $item->unknown;
                $total_row    =  $age18 +  $age18_24 +  $age25_34 + $age35_44 +  $age45_54 +  $age55_64 + $age65;
                $items1[] = [];
                // $items1[] = array(
                //     'Thời Gian'          => $item->time_period,
                //     'Tất Cả'             => $total_row,
                //     '18-'                => $age18,
                //     '18-24'              => $age18_24,
                //     '25-34'              => $age25_34,
                //     '35-44'              => $age35_44,
                //     '45-54'              => $age45_54,
                //     '55-64'              => $age55_64,
                //     '65+'                => $age65,
                //     'Không xác định'     => $unknown,
                // );
                try {
                    $age18_per   = ($age18) > 0 ? (float) number_format((($age18 / ($total_age_no)) * 100), 2, '.', '') : 0;
                    $age18_24_per = ($age18_24) > 0 ? (float) number_format((($age18_24 / ($total_age_no)) * 100), 2, '.', '') : 0;
                    $age25_34_per = ($age25_34) > 0 ? (float) number_format((($age25_34 / ($total_age_no)) * 100), 2, '.', '') : 0;
                    $age35_44_per = ($age35_44) > 0 ? (float) number_format((($age35_44 / ($total_age_no)) * 100), 2, '.', '') : 0;
                    $age45_54_per = ($age45_54) > 0 ? (float) number_format((($age45_54 / ($total_age_no)) * 100), 2, '.', '') : 0;
                    $age55_64_per = ($age55_64) > 0 ? (float) number_format((($age55_64 / ($total_age_no)) * 100), 2, '.', '') : 0;
                    $age65_per    = ($age65) > 0 ? (float) number_format((($age65 / ($total_age_no)) * 100), 2, '.', '') : 0;
                    $unknown_per    = ($unknown) > 0 ? (float) number_format((($unknown / ($total_age_no)) * 100), 2, '.', '') : 0;
                    $total_row_per = ($total_row) > 0 ? (float) number_format((($total_row / ($total_age_no)) * 100), 2, '.', '') : 0;
                } catch (\Exception $e) {
                    $age18_per   = 0;
                    $age18_24_per = 0;
                    $age25_34_per = 0;
                    $age35_44_per = 0;
                    $age45_54_per = 0;
                    $age55_64_per = 0;
                    $age65_per = 0;
                    $unknown_per = 0;
                    $total_row_per = 0;
                }
                
                $items2[] = array(
                    'Thời Gian'          => $item->time_period,
                    'Tất Cả'             => $total_row_per,
                    '18-'                => $age18_per,
                    '18-24'              => $age18_24_per,
                    '25-34'              => $age25_34_per,
                    '35-44'              => $age35_44_per,
                    '45-54'              => $age45_54_per,
                    '55-64'              => $age55_64_per,
                    '65+'                => $age65_per,
                    // 'Không xác định'     => $unknown_per,
                );
            }

            $value_header =  $this->get_value_header_gender_age($start_date, $end_date, $start_time, $end_time, $org_name);
            $name = Carbon::today()->format('d-m-Y') . 'v' . rand(1, 1000);

            Excel::create('ACS GENDER AGE ' . $name, function ($excel) use ($items1, $items2, $value_header) {
                $header = array(
                    'dong1' => 'Kết quả thống kê độ tuổi khách ra vào tại: ' . $value_header['value1'] . ' ',
                    'dong2' => 'Kết quả thống kê độ tuổi khách ra vào tại: ' . $value_header['value1'] . ' ',
                    'dong3' => 'Địa điểm: ' . $value_header['value1'] . '',
                    'dong4' => 'Thời gian:  ' . $value_header['value2'] . '  ',
                    'dong5' => 'Ngày:  ' . $value_header['value3'] . '  ',
                );
                // Tiêu đề ngoài file
                $excel->setTitle('Báo cáo kết quả hiệu quả hoạt động');
                $excel->setCreator('ACS')->setCompany('ACS Solution');
                $excel->setDescription('Báo cáo phân tích chỉ số');

                // $title1 = 'Số lượng';
                $title2 = 'Phần trăm';
                // $this->get_sheet_second($title1, $excel, $sheet, $header, $items1);
                $this->get_sheet_second($title2, $excel, $sheet, $header, $items2);
            })->store('xls', public_path('exports'));
            $file_name = 'ACS GENDER AGE ' . $name . '.xls';
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
    public function sp_poc_gender_metrics_comparison_export_excel_v2(Request $request)
    {
        try {
            $request_user = $request->user();
            $user_id = $request_user != null ?  $request_user->id : null;
            if (isset($request->user_id))
                $user_id = $request->user_id;
            $params = json_decode($request->params);
            $organization_id = $params->organization_id;
            $site_id = $params->site_id;
            $start_time = '\'' . $params->start_time . '\'';
            $end_time = '\'' . $params->end_time . '\'';
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $view_by = $params->view_by;
            $items = DB::select("exec sp_poc_gender_metrics_comparison $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date, $end_date, $view_by");

            $organization =  Organization::find($organization_id);
            $org_name =  $organization->organization_name;
            if ($site_id  != 0) {
                $site = Site::find($site_id);
                $org_name  = $site->site_name;
            }
            // Lấy tổng đánh giá để tính phần trăm
            $female_t = 0;
            $male_t = 0;
            $unknown_t = 0;
            $total_gender = 0;
            $total_gender_no = 0;
            foreach ($items as $item) {
                $female_t       += (int) $item->female;
                $male_t         += (int) $item->male;
                $unknown_t      += (int) $item->unknown;
            }
            $total_gender = $female_t + $male_t + $unknown_t;
            $total_gender_no = $female_t + $male_t;
            try {
                $female_t_per   = ($female_t) > 0 ? (float) number_format((($female_t / ($total_gender_no)) * 100), 2, '.', '') : 0;
                $male_t_per = ($male_t) > 0 ? (float) number_format((($male_t / ($total_gender_no)) * 100), 2, '.', '') : 0;
                $unknown_t_per = ($unknown_t) > 0 ? (float) number_format((($unknown_t / ($total_gender_no)) * 100), 2, '.', '') : 0;
            } catch (\Exception $e) {
                $female_t_per = 0;
                $male_t_per = 0;
                $unknown_t_per = 0;
            }
            $total_row_t_per =   $total_gender_no > 0 ? (float) number_format(100) : 0;
            // Dòng tổng excel
            // $items1[] = array(
            //     'Thời Gian'              =>  '',
            //     'Tất Cả'                 => $total_gender,
            //     'Nữ - Female'            => $female_t,
            //     'Nam - Male'             => $male_t,
            //     'Không xác định'         => $unknown_t,
            // );
            $items2[] = array(
                'Thời Gian'              =>  '',
                'Tất Cả'                 => $total_row_t_per,
                'Nữ - Female'            => $female_t_per,
                'Nam - Male'             => $male_t_per,
                // 'Không xác định'         => $unknown_t_per,
            );
            foreach ($items as $item) {
                $female       = (int) $item->female;
                $male         = (int) $item->male;
                $unknown      = (int) $item->unknown;
                $total_row    =  $female +  $male;
                $items1[] = [];
                // $items1[] = array(
                //     'Thời Gian'              => $item->time_period,
                //     'Tất Cả'                 => $total_row,
                //     'Nữ - Female'            => $female,
                //     'Nam - Male'             => $male,
                //     'Không xác định'         => $unknown,
                // );
                try {
                    $female_per   = ($female) > 0 ? (float) number_format((($female / ($total_gender_no)) * 100), 2, '.', '') : 0;
                    $male_per = ($male) > 0 ? (float) number_format((($male / ($total_gender_no)) * 100), 2, '.', '') : 0;
                    $unknown_per = ($unknown) > 0 ? (float) number_format((($unknown / ($total_gender_no)) * 100), 2, '.', '') : 0;
                    $total_row_per = ($total_row) > 0 ? (float) number_format((($total_row / ($total_gender_no)) * 100), 2, '.', '') : 0;
                } catch (\Exception $e) {
                    $female_per   = 0;
                    $male_per = 0;
                    $unknown_per = 0;
                    $total_row_per = 0;
                }
                
                $items2[] = array(
                    'Thời Gian'              => $item->time_period,
                    'Tất Cả'                 => $total_row_per,
                    'Nữ - Female'            => $female_per,
                    'Nam - Male'             => $male_per,
                    // 'Không xác định'         => $unknown_per,
                );
            }
            $value_header =  $this->get_value_header_gender_age($start_date, $end_date, $start_time, $end_time, $org_name);
            $name = Carbon::today()->format('d-m-Y') . 'v' . rand(1, 1000);

            Excel::create('ACS GENDER AGE ' . $name, function ($excel) use ($items1, $items2, $value_header) {
                $header = array(
                    'dong1' => 'Kết quả thống kê giới tính khách ra vào tại: ' . $value_header['value1'] . ' ',
                    'dong2' => 'Kết quả thống kê giới tính khách ra vào tại: ' . $value_header['value1'] . ' ',
                    'dong3' => 'Địa điểm: ' . $value_header['value1'] . '',
                    'dong4' => 'Thời gian:  ' . $value_header['value2'] . '  ',
                    'dong5' => 'Ngày:  ' . $value_header['value3'] . '  ',
                );
                // Tiêu đề ngoài file
                $excel->setTitle('Báo cáo kết quả hiệu quả hoạt động');
                $excel->setCreator('ACS')->setCompany('ACS Solution');
                $excel->setDescription('Báo cáo phân tích chỉ số');

                // $title1 = 'Số lượng';
                $title2 = 'Phần trăm';
                // $this->get_sheet_second($title1, $excel, $sheet, $header, $items1);
                $this->get_sheet_second($title2, $excel, $sheet, $header, $items2);
            })->store('xls', public_path('exports'));
            $file_name = 'ACS GENDER AGE ' . $name . '.xls';
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
    public function sp_poc_gender_age_by_day_export_excel_v2(Request $request)
    {
        try {
            $request_user = $request->user();
            $user_id = $request_user != null ?  $request_user->id : null;
            if (isset($request->user_id))
                $user_id = $request->user_id;
            $params = json_decode($request->params);
            $organization_id = $params->organization_id;
            $site_id = $params->site_id;
            $start_time = '\'' . $params->start_time . '\'';
            $end_time = '\'' . $params->end_time . '\'';
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $view_by = $params->view_by;
            $items = DB::select("exec sp_poc_gender_age_visits $user_id, $organization_id, $site_id, $start_time, $end_time, $start_date, $end_date");
            $item = $items[0];
            $organization =  Organization::find($organization_id);
            $org_name =  $organization->organization_name;
            if ($site_id  != 0) {
                $site = Site::find($site_id);
                $org_name  = $site->site_name;
            }
            // Lấy tổng đánh giá để tính phần trăm
            $age18 = $item->age18_female + $item->age18_male + $item->age18_unknown;
            $age18_24 = $item->age18_24_female + $item->age18_24_male + $item->age18_24_unknown;
            $age25_34 = $item->age25_34_female + $item->age25_34_male + $item->age25_34_unknown;
            $age35_44 = $item->age35_44_female + $item->age35_44_male + $item->age35_44_unknown;
            $age45_54 = $item->age45_54_female + $item->age45_54_male + $item->age45_54_unknown;
            $age55_64 = $item->age55_64_female + $item->age55_64_male + $item->age55_64_unknown;
            $age65 = $item->age65_female + $item->age65_male + $item->age65_unknown;

            $total_female = $item->age18_female + $item->age18_24_female + $item->age25_34_female + $item->age35_44_female + $item->age45_54_female + $item->age55_64_female + $item->age65_female;
            $total_male = $item->age18_male + $item->age18_24_male + $item->age25_34_male + $item->age35_44_male + $item->age45_54_male + $item->age55_64_male + $item->age65_male;
            $total_unknown = $item->age18_unknown + $item->age18_24_unknown + $item->age25_34_unknown + $item->age35_44_unknown + $item->age45_54_unknown + $item->age55_64_unknown + $item->age65_unknown + $item->unknown_unknown;
            $total_unknown_unknown =  $item->unknown_female +   $item->unknown_male  + $item->unknown_unknown;
            $total_age = $total_female + $total_male + $total_unknown + $total_unknown_unknown;
            $total_age_no = $total_female + $total_male;
            $items1 = [];
            // $items1 = array(
            //     array(
            //         'Độ Tuổi'              =>  '',
            //         'Tất Cả'                 => (float) $total_age,
            //         'Nữ - Female'            => (float) $total_female,
            //         'Nam - Male'             => (float) $total_male,
            //         'Không xác định'         => (float) $total_unknown,
            //     ),
            //     array(
            //         'Độ Tuổi'              =>  '18-',
            //         'Tất Cả'                 => (float) $age18,
            //         'Nữ - Female'            => (float) $item->age18_female,
            //         'Nam - Male'             => (float) $item->age18_male,
            //         'Không xác định'         => (float) $item->age18_unknown,
            //     ),
            //     array(
            //         'Độ Tuổi'              =>  '18-24',
            //         'Tất Cả'                 => (float) $age18_24,
            //         'Nữ - Female'            => (float) $item->age18_24_female,
            //         'Nam - Male'             => (float) $item->age18_24_male,
            //         'Không xác định'         => (float) $item->age18_24_unknown,
            //     ),
            //     array(
            //         'Độ Tuổi'              =>  '25-34',
            //         'Tất Cả'                 => (float) $age25_34,
            //         'Nữ - Female'            => (float) $item->age25_34_female,
            //         'Nam - Male'             => (float) $item->age25_34_male,
            //         'Không xác định'         => (float) $item->age25_34_unknown,
            //     ),
            //     array(
            //         'Độ Tuổi'              =>  '35-44',
            //         'Tất Cả'                 => (float) $age35_44,
            //         'Nữ - Female'            => (float) $item->age35_44_female,
            //         'Nam - Male'             => (float) $item->age35_44_male,
            //         'Không xác định'         => (float) $item->age35_44_unknown,
            //     ),
            //     array(
            //         'Độ Tuổi'              =>  '45-54',
            //         'Tất Cả'                 => (float) $age45_54,
            //         'Nữ - Female'            => (float) $item->age45_54_female,
            //         'Nam - Male'             => (float) $item->age45_54_male,
            //         'Không xác định'         => (float) $item->age45_54_unknown,
            //     ),
            //     array(
            //         'Độ Tuổi'              =>  '55-64',
            //         'Tất Cả'                 => (float) $age55_64,
            //         'Nữ - Female'            => (float) $item->age55_64_female,
            //         'Nam - Male'             => (float) $item->age55_64_male,
            //         'Không xác định'         => (float) $item->age55_64_unknown,
            //     ),
            //     array(
            //         'Độ Tuổi'              =>  '65+',
            //         'Tất Cả'                 => (float) $age65,
            //         'Nữ - Female'            => (float) $item->age65_female,
            //         'Nam - Male'             => (float) $item->age65_male,
            //         'Không xác định'         => (float) $item->age65_unknown,
            //     ),
            //     array(
            //         'Độ Tuổi'              =>  'Không xác định',
            //         'Tất Cả'                 => (float) $total_unknown_unknown,
            //         'Nữ - Female'            => (float) $item->unknown_female,
            //         'Nam - Male'             => (float) $item->unknown_male,
            //         'Không xác định'         => (float) $item->unknown_unknown,
            //     ),
            // );
            $items2 = array(
                array(
                    'Độ Tuổi'               =>  '',
                    'Tất Cả'                 => $total_age_no > 0 ? (float) number_format(100) : 0,
                    'Nữ - Female'            => ($total_female) > 0 ? (float) number_format((($total_female / ($total_age_no)) * 100), 2, '.', '') : 0,
                    'Nam - Male'             => ($total_male) > 0 ? (float) number_format((($total_male / ($total_age_no)) * 100), 2, '.', '') : 0,
                    // 'Không xác định'         => ($total_unknown) > 0 ? (float) number_format((($total_unknown / ($total_age_no)) * 100), 2, '.', '') : 0,
                ),
                array(
                    'Độ Tuổi'               =>  '18-',
                    'Tất Cả'                 => ($age18) > 0 ? (float) number_format((($age18 / ($total_age_no)) * 100), 2, '.', '') : 0,
                    'Nữ - Female'            => ($item->age18_female) > 0 ? (float) number_format((($item->age18_female / ($total_age_no)) * 100), 2, '.', '') : 0,
                    'Nam - Male'             => ($item->age18_male) > 0 ? (float) number_format((($item->age18_male / ($total_age_no)) * 100), 2, '.', '') : 0,
                    // 'Không xác định'         => ($item->age18_unknown) > 0 ? (float) number_format((($item->age18_unknown / ($total_age_no)) * 100), 2, '.', '') : 0,
                ),
                array(
                    'Độ Tuổi'               =>  '18-24',
                    'Tất Cả'                 => ($age18_24) > 0 ? (float) number_format((($age18_24 / ($total_age_no)) * 100), 2, '.', '') : 0,
                    'Nữ - Female'            => ($item->age18_24_female) > 0 ? (float) number_format((($item->age18_24_female / ($total_age_no)) * 100), 2, '.', '') : 0,
                    'Nam - Male'             => ($item->age18_24_male) > 0 ? (float) number_format((($item->age18_24_male / ($total_age_no)) * 100), 2, '.', '') : 0,
                    // 'Không xác định'         => ($item->age18_24_unknown) > 0 ? (float) number_format((($item->age18_24_unknown / ($total_age_no)) * 100), 2, '.', '') : 0,
                ),
                array(
                    'Độ Tuổi'               =>  '25-34',
                    'Tất Cả'                 => ($age25_34) > 0 ? (float) number_format((($age25_34 / ($total_age_no)) * 100), 2, '.', '') : 0,
                    'Nữ - Female'            => ($item->age25_34_female) > 0 ? (float) number_format((($item->age25_34_female / ($total_age_no)) * 100), 2, '.', '') : 0,
                    'Nam - Male'             => ($item->age25_34_male) > 0 ? (float) number_format((($item->age25_34_male / ($total_age_no)) * 100), 2, '.', '') : 0,
                    // 'Không xác định'         => ($item->age25_34_unknown) > 0 ? (float) number_format((($item->age25_34_unknown / ($total_age_no)) * 100), 2, '.', '') : 0,
                ),
                array(
                    'Độ Tuổi'               =>  '35-44',
                    'Tất Cả'                 => ($age35_44) > 0 ? (float) number_format((($age35_44 / ($total_age_no)) * 100), 2, '.', '') : 0,
                    'Nữ - Female'            => ($item->age35_44_female) > 0 ? (float) number_format((($item->age35_44_female / ($total_age_no)) * 100), 2, '.', '') : 0,
                    'Nam - Male'             => ($item->age35_44_male) > 0 ? (float) number_format((($item->age35_44_male / ($total_age_no)) * 100), 2, '.', '') : 0,
                    // 'Không xác định'         => ($item->age35_44_unknown) > 0 ? (float) number_format((($item->age35_44_unknown / ($total_age_no)) * 100), 2, '.', '') : 0,
                ),
                array(
                    'Độ Tuổi'               =>  '45-54',
                    'Tất Cả'                 => ($age45_54) > 0 ? (float) number_format((($age45_54 / ($total_age_no)) * 100), 2, '.', '') : 0,
                    'Nữ - Female'            => ($item->age45_54_female) > 0 ? (float) number_format((($item->age45_54_female / ($total_age_no)) * 100), 2, '.', '') : 0,
                    'Nam - Male'             => ($item->age45_54_male) > 0 ? (float) number_format((($item->age45_54_male / ($total_age_no)) * 100), 2, '.', '') : 0,
                    // 'Không xác định'         => ($item->age45_54_unknown) > 0 ? (float) number_format((($item->age45_54_unknown / ($total_age_no)) * 100), 2, '.', '') : 0,
                ),
                array(
                    'Độ Tuổi'               =>  '55-64',
                    'Tất Cả'                 => ($age55_64) > 0 ? (float) number_format((($age55_64 / ($total_age_no)) * 100), 2, '.', '') : 0,
                    'Nữ - Female'            => ($item->age55_64_female) > 0 ? (float) number_format((($item->age55_64_female / ($total_age_no)) * 100), 2, '.', '') : 0,
                    'Nam - Male'             => ($item->age55_64_male) > 0 ? (float) number_format((($item->age55_64_male / ($total_age_no)) * 100), 2, '.', '') : 0,
                    // 'Không xác định'         => ($item->age55_64_unknown) > 0 ? (float) number_format((($item->age55_64_unknown / ($total_age_no)) * 100), 2, '.', '') : 0,
                ),
                array(
                    'Độ Tuổi'               =>  '65+',
                    'Tất Cả'                 => ($age65) > 0 ? (float) number_format((($age65 / ($total_age_no)) * 100), 2, '.', '') : 0,
                    'Nữ - Female'            => ($item->age65_female) > 0 ? (float) number_format((($item->age65_female / ($total_age_no)) * 100), 2, '.', '') : 0,
                    'Nam - Male'             => ($item->age65_male) > 0 ? (float) number_format((($item->age65_male / ($total_age_no)) * 100), 2, '.', '') : 0,
                    // 'Không xác định'         => ($item->age65_unknown) > 0 ? (float) number_format((($item->age65_unknown / ($total_age_no)) * 100), 2, '.', '') : 0,
                ),
                // array(
                //     'Độ Tuổi'              =>  'Không xác định',
                //     'Tất Cả'                 => ($total_unknown_unknown) > 0 ? (float) number_format((($total_unknown_unknown / ($total_age_no)) * 100), 2, '.', '') : 0,
                //     'Nữ - Female'            => ($item->unknown_female) > 0 ? (float) number_format((($item->unknown_female / ($total_age_no)) * 100), 2, '.', '') : 0,
                //     'Nam - Male'             => ($item->unknown_male) > 0 ? (float) number_format((($item->unknown_male / ($total_age_no)) * 100), 2, '.', '') : 0,
                //     // 'Không xác định'         => ($item->unknown_unknown) > 0 ? (float) number_format((($item->unknown_unknown / ($total_age_no)) * 100), 2, '.', '') : 0,
                // ),
            );

            $value_header =  $this->get_value_header_gender_age($start_date, $end_date, $start_time, $end_time, $org_name);
            $name = Carbon::today()->format('d-m-Y') . 'v' . rand(1, 1000);

            Excel::create('ACS GENDER AGE ' . $name, function ($excel) use ($items1, $items2, $value_header) {
                $header = array(
                    'dong1' => 'Kết quả thống kê giới tính, độ tuổi khách ra vào tại: ' . $value_header['value1'] . ' ',
                    'dong2' => 'Kết quả thống kê giới tính, độ tuổi khách ra vào tại: ' . $value_header['value1'] . ' ',
                    'dong3' => 'Địa điểm: ' . $value_header['value1'] . '',
                    'dong4' => 'Thời gian:  ' . $value_header['value2'] . '  ',
                    'dong5' => 'Ngày:  ' . $value_header['value3'] . '  ',
                );
                // Tiêu đề ngoài file
                $excel->setTitle('Báo cáo kết quả hiệu quả hoạt động');
                $excel->setCreator('ACS')->setCompany('ACS Solution');
                $excel->setDescription('Báo cáo phân tích chỉ số');

                // $title1 = 'Số lượng';
                $title2 = 'Phần trăm';
                $this->get_sheet_second($title2, $excel, $sheet, $header, $items2);
                // $this->get_sheet_second($title1, $excel, $sheet, $header, $items1);
            })->store('xls', public_path('exports'));
            $file_name = 'ACS GENDER AGE ' . $name . '.xls';
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
}
