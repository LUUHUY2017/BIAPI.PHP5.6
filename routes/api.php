<?php

use Illuminate\Http\Request;
use App\User;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::group(['middleware'=>'auth:api', 'middleware' => ['cors']], function(){
Route::group(['middleware' => 'auth:api'], function () {
    Route::post('/apiGetSourceDataInOut',  'Report\FootFallController@sp_api_get_poc_data_in_out');
    Route::get('/logout', 'Admin\UserController@logout');
    Route::get('/user', 'Admin\UserController@getInfoFromLoginWeb');
    /********************* ROUTER DÙNG CHUNG *********************/
    // CRUD helps
    Route::get('sp_get_help_list_tag', 'Admin\HelpController@sp_get_tag_list');
    Route::get('sp_get_help_list', 'Admin\HelpController@sp_get_help_list');
    Route::post('sp_get_help_list_insert', 'Admin\HelpController@insert');
    Route::get('sp_get_help_list_get_tag', 'Admin\HelpController@sp_get_help_list_get_tag');
    Route::post('sp_get_help_list_get_detail', 'Admin\HelpController@sp_get_help_list_get_detail');
    Route::post('sp_get_help_list_from_tag_id', 'Admin\HelpController@sp_get_help_list_from_tag_id');
    Route::post('sp_get_help_list_get_update', 'Admin\HelpController@get_update');
    Route::post('sp_get_help_list_post_update', 'Admin\HelpController@post_update');
    // Nghĩa thêm api lấy site theo quyền trong quản trị site
    Route::post('sites_get_site_with_permission', 'Admin\SiteController@sp_get_site_with_permission');
    // Nghĩa thêm api lấy site theo quyền truy cập
    Route::post('sites_get_site_for_report', 'Admin\SiteController@get_site_for_report');
    // Thêm mới bản ghi vào bảng messages
    Route::get('update_status_message_truncates', 'Admin\MessageController@update_status_message_truncates');
    Route::post('update_status_message_delete', 'Admin\MessageController@update_status_message_delete');
    // Lấy danh sách thông báo của người dùng
    Route::get('get_message_with_user', 'Admin\MessageController@get_message_with_user');
    Route::post('update_status_message', 'Admin\MessageController@update_status_message');
    // Bao cao
    // Footfall
    // Visits
    // Dashboard
    // Hệ thống Footfall
    Route::post('sp_poc_data_in_out_sum_by_site_export_excel', 'Report\FootFallExportController@sp_poc_data_in_out_sum_by_site_export_excel');
    Route::post('sp_poc_data_in_out_sum_by_site_delete_excel', 'Report\FootFallExportController@delete_excel');
    Route::post('sp_poc_data_in_out_sum_by_site_import_data', 'Report\FootFallController@sp_poc_data_in_out_sum_by_site_import_data');
    // time comparision

    Route::post('sp_footfall_time_comparison_export_excel', 'Report\FootFallExportController@sp_footfall_time_comparison_export_excel');
    // store comparision

    Route::post('sp_footfall_store_comparison_export_excel', 'Report\FootFallExportController@sp_footfall_store_comparison_export_excel');

    // Báo cáo Hiệu quả hoạt động
    Route::post('sp_footfall_performance_data_by_site_export_excel', 'Report\PerformanceExportController@sp_footfall_performance_data_by_site_export_excel');
    Route::post('sp_footfall_performance_time_comparison_export_excel', 'Report\PerformanceExportController@sp_footfall_performance_time_comparison_export_excel');
    Route::post('sp_footfall_performance_store_comparison_export_excel', 'Report\PerformanceExportController@sp_footfall_performance_store_comparison_export_excel');
    Route::post('sp_footfall_performance_boston_export_excel', 'Report\PerformanceExportController@sp_footfall_heatmap_treemap_coloraxis_sum_export_excel');
    Route::post('sp_footfall_performance_boston_reporting_export_excel', 'Report\PerformanceExportController@sp_footfall_performance_boston_reporting_export_excel');
    Route::post('sp_footfall_performance_insert_data', 'Report\PerformanceController@sp_footfall_performance_insert_data');
    Route::post('sp_footfall_performance_download_file_import', 'Report\PerformanceController@sp_footfall_performance_download_file_import');


    // Heatmap
    Route::post('sp_footfall_heatmap_treemap_coloraxis_sum_export_excel', 'Report\FootFallExportController@sp_footfall_heatmap_treemap_coloraxis_sum_export_excel');
    Route::post('sp_footfall_heatmap_treemap_coloraxis_sum_reporting_export_excel', 'Report\FootFallExportController@sp_footfall_heatmap_treemap_coloraxis_sum_reporting_export_excel');

    // Law Data For Customer
    Route::post('sp_report_poc_raw_data_by_day', 'Report\FootFallController@sp_report_poc_raw_data_by_day');
    Route::post('sp_report_poc_raw_data_by_month', 'Report\FootFallController@sp_report_poc_raw_data_by_month');
    Route::post('sp_report_poc_raw_data_by_year', 'Report\FootFallController@sp_report_poc_raw_data_by_year');


    Route::post('sp_report_poc_raw_data_by_day_export_excel', 'Report\FootFallExportController@sp_report_poc_raw_data_by_day_export_excel');
    Route::post('sp_report_poc_raw_data_by_month_export_excel', 'Report\FootFallExportController@sp_report_poc_raw_data_by_month_export_excel');
    Route::post('sp_report_poc_raw_data_by_year_export_excel', 'Report\FootFallExportController@sp_report_poc_raw_data_by_year_export_excel');


    // live view
    Route::post('sp_footfall_get_traffic_live', 'Report\FootFallController@sp_footfall_get_traffic_live');
    Route::post('sp_footfall_get_traffic', 'Report\FootFallController@sp_footfall_get_traffic');
    Route::post('sp_footfall_get_traffic_export_excel', 'Report\FootFallExportController@sp_footfall_get_traffic_export_excel');

    //  Common
    Route::get('get_start_time', 'Common\CommonController@get_start_time');
    Route::get('get_end_time', 'Common\CommonController@get_end_time');
    Route::get('get_traffic_index', 'Common\CommonController@get_traffic_index');
    Route::get('get_day_of_week', 'Common\CommonController@get_day_of_week');
    Route::get('get_date_time', 'Common\CommonController@get_date_time');

    Route::post('get_user_page_parametter', 'Common\CommonController@get_user_page_parametter');
    // Route::post('user_page_parametter', 'Common\CommonController@user_page_parametter');
    Route::post('get_user_page_parametter_v2', 'Common\CommonController@get_user_page_parametter_v2');

    Route::post('user_page_parametter', 'Admin\UserPageParametterController@store');


    // FBA
    // fba_questions
    // Lấy câu hỏi theo tổ chức khi làm báo cáo
    Route::post('get_question_for_report', 'Fba\FbaQuestionController@get_question_for_report');

    Route::post('get_fba_questions', 'Fba\FbaQuestionController@index');
    //Lấy câu hỏi cho thiết bị
    Route::post('get_fba_questions_for_terminal', 'Fba\FbaQuestionController@get_for_terminal');
    Route::post('get_fba_question_curent', 'Fba\FbaQuestionController@question_curent');
    Route::post('get_fba_questions_next', 'Fba\FbaQuestionController@questions_next');
    Route::post('fba_question_feedback', 'Fba\FbaQuestionController@question_tablet_feedback');

    Route::post('/fba_application_seting', 'Fba\FbaAppicationSettingController@get_for_tablet');

    // FBA Report Overview
    Route::post('sp_fba_overview_total', 'Fba\Report\FbaReportOverviewController@sp_fba_overview_total');

    // Lấy thông tin chung cấu hình trang theo người dùng
    Route::post('fba_report_get_config_by_user', 'Common\CommonController@fba_report_get_config_by_user');

    // Báo cáo phân tích chỉ số
    Route::post('fba_report_metrics_analytic', 'Fba\Report\FbaReportMetricsAnalyticController@fba_report_metrics_analytic');
    Route::post('export_metrics_analytic', 'Fba\Report\FbaReportMetricsAnalyticController@export_metrics_analytic');
    Route::post('export_metrics_analytic_delete_excel', 'Fba\Report\FbaReportMetricsAnalyticController@delete_excel');
    // 21/11/2018
    // Quyết thêm API cho báo cáo lý do fba_report_reason
    Route::post('fba_report_reason', 'Fba\Report\FbaReportReasonController@fba_report_reason');
    Route::post('export_report_reason', 'Fba\Report\FbaReportReasonController@export_report_reason');
    Route::post('export_report_reason_delete_excel', 'Fba\Report\FbaReportReasonController@delete_excel');
    // 17/12/2018 Api customer Info
    Route::post('fba_customer_info', 'Fba\Report\FbaReportCustomerInfoController@fba_customer_info');
    Route::post('export_customer_info', 'Fba\Report\FbaReportCustomerInfoController@export_customer_info');
    Route::post('export_customer_info_delete_excel', 'Fba\Report\FbaReportCustomerInfoController@delete_excel');
    // 15/06/2020 Api customer Info
    Route::post('fba_customer_info_response', 'Fba\Report\FbaReportCustomerInfoController@fba_customer_info_response');
    Route::post('export_customer_info_response', 'Fba\Report\FbaReportCustomerInfoController@export_customer_info_response');


    //Huy: Quản trị câu hỏi  30/11/2018
    Route::get('get_status_question_default', 'Fba\FbaQuestionController@get_status_question_default');
    Route::post('sp_ad_question', 'Fba\FbaQuestionController@sp_ad_question');
    Route::post('insert_question', 'Fba\FbaQuestionController@insert_question');
    Route::post('get_question_edit', 'Fba\FbaQuestionController@get_question_edit');
    Route::post('update_question', 'Fba\FbaQuestionController@update_question');
    Route::post('delete_question', 'Fba\FbaQuestionController@delete_question');
    // API Metrics Comparation 19/12
    Route::post('get_category', 'Fba\Report\FbaReportMetricsComparationController@get_category');
    Route::post('fba_report_get_metrics_comparison', 'Fba\Report\FbaReportMetricsComparationController@get_metrics_comparison');
    Route::post('export_metrics_comparison', 'Fba\Report\FbaReportMetricsComparationController@export_metrics_comparison');
    Route::post('export_metrics_comparison_delete_excel', 'Fba\Report\FbaReportMetricsComparationController@delete_excel');
    // Huy:26/12/2018 Lấy thông tin thiết bị
    Route::post('fba_get_tablet', 'Fba\FbaTabletController@fba_get_tablet');
    Route::post('fba_get_tablet_follow', 'Fba\FbaTabletController@fba_get_tablet_follow');

    Route::post('sp_footfall_heatmap_treemap_coloraxis_sum_metrics_boston', 'Report\FootFallController@sp_footfall_heatmap_treemap_coloraxis_sum_metrics_boston');
    Route::post('sp_poc_data_in_out_sum_by_site', 'Report\FootFallController@sp_poc_data_in_out_sum_by_site');
    Route::post('sp_poc_data_in_out_sum_by_site_and_fba_metrics', 'Report\FootFallController@sp_poc_data_in_out_sum_by_site_and_fba_metrics');
    Route::post('sp_footfall_time_comparison', 'Report\FootFallController@sp_footfall_time_comparison');
    Route::post('sp_footfall_store_comparison', 'Report\FootFallController@sp_footfall_store_comparison');
    Route::post('sp_poc_data_in_out_sum_by_site_overview', 'Report\FootFallController@sp_poc_data_in_out_sum_by_site_overview');
    // Performance Controller
    Route::post('sp_footfall_performance_data_by_site', 'Report\PerformanceController@sp_footfall_performance_data_by_site');
    Route::post('sp_footfall_performance_time_comparison', 'Report\PerformanceController@sp_footfall_performance_time_comparison');
    Route::post('sp_footfall_performance_store_comparison', 'Report\PerformanceController@sp_footfall_performance_store_comparison');
    Route::post('sp_poc_data_in_out_sum_by_site_reporting_store', 'Report\FootFallController@sp_poc_data_in_out_sum_by_site_reporting_store');
    Route::post('sp_footfall_heatmap_poc_data_in_out_sum', 'Report\FootFallController@sp_footfall_heatmap_poc_data_in_out_sum');
    Route::post('sp_footfall_heatmap_treemap_coloraxis_sum', 'Report\FootFallController@sp_footfall_heatmap_treemap_coloraxis_sum');

    // Cấu hình user nhận mail báo cáo
    Route::post('user_send_email_poc', 'Poc\Administration\UsersController@config_user_email');
    Route::post('user_send_email_fba', 'Fba\Administration\UsersController@config_user_email');
    Route::post('user_send_email_age', 'GenderAge\Administration\UsersController@config_user_email');
    Route::post('user_send_email_hqhd', 'HQHD\Administration\UsersController@config_user_email');



    Route::post('sp_fba_report_user_email_logs', 'Fba\Administration\UsersController@sp_fba_report_user_email_logs');
    Route::post('sp_fba_report_user_email_logs_delete', 'Fba\Administration\UsersController@sp_fba_report_user_email_logs_delete');
    Route::post('sp_poc_report_user_email_logs', 'Poc\Administration\UsersController@sp_poc_report_user_email_logs');
    Route::post('sp_poc_report_user_email_logs_delete', 'Poc\Administration\UsersController@sp_poc_report_user_email_logs_delete');
    Route::post('sp_poc_report_user_email_logs_gender', 'GenderAge\Administration\UsersController@sp_poc_report_user_email_logs');
    Route::post('sp_poc_report_user_email_logs_delete_gender', 'GenderAge\Administration\UsersController@sp_poc_report_user_email_logs_delete');
    Route::post('sp_poc_report_user_email_logs_hqhd', 'HQHD\Administration\UsersController@sp_hqhd_report_user_email_logs');
    Route::post('sp_poc_report_user_email_logs_delete_hqhd', 'HQHD\Administration\UsersController@sp_hqhd_report_user_email_logs_delete');


    // Cấu hình thông tin người gửi email
    Route::post('mail_configuration_get_info', 'Admin\UserController@mail_configuration_get_info');
    Route::post('mail_configuration_update_info', 'Poc\Administration\UsersController@update_mail_configuration');
    Route::post('mail_configuration_send_email_test', 'Admin\UserController@mail_configuration_send_email_test');

    // Báo cáo thiết bị gender age
    Route::post('sp_poc_gender_metric_analytic', 'GenderAge\Terminals\TerminalController@sp_poc_gender_metric_analytic');
    Route::post('sp_poc_gender_metric_analytic_export_excel', 'GenderAge\Terminals\TerminalExportController@sp_poc_gender_metric_analytic_export_excel');
    Route::post('sp_poc_gender_metrics_comparison', 'GenderAge\Terminals\TerminalController@sp_poc_gender_metrics_comparison');
    Route::post('sp_poc_gender_metrics_comparison_export_excel', 'GenderAge\Terminals\TerminalExportController@sp_poc_gender_metrics_comparison_export_excel');

    Route::post('poc_send_email_dayly', 'Poc\Administration\EmailController@send_email_dayly');
    Route::post('sp_poc_gender_age_by_day', 'GenderAge\Terminals\TerminalController@sp_poc_gender_age_by_day');
    Route::post('sp_poc_gender_age_by_day_export_excel',  'GenderAge\Terminals\TerminalExportController@sp_poc_gender_age_by_day_export_excel');
});

Route::get('get_notifications', 'Fba\FbaNotificationController@get_notification');
Route::options('{any}', ['middleware' => ['cors'], function () {
    return response(['status' => 'success']);
}])->where('any', '.*');

Route::post('/fba_smile_touch_get_question_default', 'Fba\FbaQuestionController@question_curent');
Route::post('/fba_application_seting_default', 'Fba\FbaAppicationSettingController@get_for_tablet');

// kiểm soát quyền machine to machine
Route::group(['middleware' => 'client'], function () {

    // Notifications group
    Route::post('fba_tablet_connect_notification', 'Admin\MessageController@fba_tablet_connect_notification');
    Route::post('fba_tablet_disconnect_notification', 'Admin\MessageController@fba_tablet_disconnect_notification');

    // end group
    // Lấy câu hỏi cho tablet
    Route::post('fba_tablet_get_data', 'Fba\FbaQuestionController@tablet_get_data');
    Route::post('fba_tablet_get_data_v2', 'Fba\FbaQuestionController@tablet_get_data_v2');

    Route::post('fba_tablet_update_status', 'Fba\FbaTabletController@update_status');
    Route::post('fba_question_tablet_feedback', 'Fba\FbaQuestionController@question_tablet_feedback');

    Route::post('fba_terminal_update_status', 'Fba\FbaTabletController@terminal_update_status');

    // Lấy thông tin chi tiết của thiết bị để trả về cho fba tablet
    Route::post('fba_tablet_get_info', 'Fba\FbaTabletController@fba_tablet_get_info');

    // UY Test ket noi
    Route::post('fba_tablet_get_data_client', 'Fba\FbaQuestionController@tablet_get_data');

    //10/01/2019 Gửi email tự động FBA, POC và ghi logs
    Route::post('fba_send_email_dayly', 'Fba\Administration\EmailController@send_email_dayly');
    Route::post('fba_send_email_weekly', 'Fba\Administration\EmailController@send_email_weekly');
    Route::post('fba_send_email_monthly', 'Fba\Administration\EmailController@send_email_monthly');

    Route::post('poc_send_email_dayly', 'Poc\Administration\EmailController@send_email_dayly');
    Route::post('poc_send_email_weekly', 'Poc\Administration\EmailController@send_email_weekly');
    Route::post('poc_send_email_monthly', 'Poc\Administration\EmailController@send_email_monthly');

    Route::post('age_send_email_dayly', 'GenderAge\Administration\EmailController@send_email_dayly');
    Route::post('age_send_email_weekly', 'GenderAge\Administration\EmailController@send_email_weekly');
    Route::post('age_send_email_monthly', 'GenderAge\Administration\EmailController@send_email_monthly');

    Route::post('hqhd_send_email_dayly', 'HQHD\Administration\EmailController@send_email_dayly');
    Route::post('hqhd_send_email_weekly', 'HQHD\Administration\EmailController@send_email_weekly');
    Route::post('hqhd_send_email_monthly', 'HQHD\Administration\EmailController@send_email_monthly');

    // Route::post('sendmail','HQHD\Administration\EmailController@sendmail');
    /************  07/02/2020 _ API_  Footfall  MC to MC **************/
    // Module_Footfall
    // dang dung cho email


    // end
    Route::post('sp_footfall_time_comparison_export_excel_v2', 'Report\FootFallExportController@sp_footfall_time_comparison_export_excel');

    Route::post('sp_footfall_heatmap_treemap_coloraxis_sum_export_excel_v2', 'Report\FootFallExportController@sp_footfall_heatmap_treemap_coloraxis_sum_export_excel');
    Route::post('sp_footfall_get_traffic_export_excel_v2', 'Report\FootFallExportController@sp_footfall_get_traffic_export_excel');

    Route::group(['middleware' => 'GetOrgUrl'], function () {
        // Footfall
        Route::post('sp_footfall_heatmap_treemap_coloraxis_sum_reporting_export_excel_v2', 'Export\FootfallExcelController@sp_footfall_heatmap_treemap_coloraxis_sum_reporting_export_excel_v2');
        Route::post('sp_footfall_store_comparison_export_excel_v2', 'Export\FootfallExcelController@sp_footfall_store_comparison_export_excel_v2');
        Route::post('sp_poc_data_in_out_sum_by_site_export_excel_v2', 'Export\FootfallExcelController@sp_poc_data_in_out_sum_by_site_export_excel_v2');

        // Footfall - Customer
        Route::post('sp_report_poc_raw_data_by_day_export_excel_v2', 'Export\FootfallExcelController@sp_report_poc_raw_data_by_day_export_excel_v2');
        Route::post('sp_report_poc_raw_data_by_month_export_excel_v2', 'Export\FootfallExcelController@sp_report_poc_raw_data_by_month_export_excel_v2');
        Route::post('sp_report_poc_raw_data_by_year_export_excel_v2', 'Export\FootfallExcelController@sp_report_poc_raw_data_by_year_export_excel_v2');

        // Fba
        // dang dung cho email
        Route::post('export_customer_info_v2', 'Export\FbaExcelController@export_customer_info_v2');
        Route::post('export_report_reason_v2', 'Export\FbaExcelController@export_report_reason_v2');
        Route::post('export_metrics_analytic_v2', 'Export\FbaExcelController@export_metrics_analytic_v2');
        Route::post('export_metrics_comparison_v2', 'Export\FbaExcelController@export_metrics_comparison_v2');
        // end

        //Module_GenderAge
        // dang dung cho email
        Route::post('sp_poc_gender_metric_analytic_export_excel_v2', 'Export\AgeExcelController@sp_poc_gender_metric_analytic_export_excel_v2');
        Route::post('sp_poc_gender_metrics_comparison_export_excel_v2', 'Export\AgeExcelController@sp_poc_gender_metrics_comparison_export_excel_v2');
        Route::post('sp_poc_gender_age_by_day_export_excel_v2',  'Export\AgeExcelController@sp_poc_gender_age_by_day_export_excel_v2');
        // end

        // Performance
        Route::post('sp_footfall_performance_boston_export_excel_v2', 'Export\PerformanceExcelController@sp_footfall_heatmap_treemap_coloraxis_sum_export_excel_v2'); // Mô hình boston
        Route::post('sp_footfall_performance_boston_reporting_export_excel_v2', 'Export\PerformanceExcelController@sp_footfall_performance_boston_reporting_export_excel_v2'); // Hiệu quả hoạt động / Cửa hàng
        Route::post('sp_footfall_performance_data_by_site_export_excel_v2', 'Export\PerformanceExcelController@sp_footfall_performance_data_by_site_export_excel_v2'); // Hiệu quả hoạt động / Chỉ số
        Route::post('sp_footfall_performance_store_comparison_export_excel_v2', 'Export\PerformanceExcelController@sp_footfall_performance_store_comparison_export_excel_v2'); // Báo cáo so sánh / Cửa hàng --
    });
    // Module_HQHĐ
    // dang dung cho email


    // end
    Route::post('sp_footfall_performance_time_comparison_export_excel_v2', 'Report\PerformanceExportController@sp_footfall_performance_time_comparison_export_excel');



    //Module_FBA



});
