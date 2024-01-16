<?php
/*
	AUTHOR: NGHIANT
	DATE: 13/08/2019
*/
use Illuminate\Http\Request;
// Route::post('openapi/v1/smart_data/upload', 'GenderAge\Terminals\TerminalController@open_api_get_data');
// Nghĩa thêm function lấy logo cho login page
Route::post('/get_organization_for_login', 'Admin\OrganizationController@get_organization_for_login');

Route::post('zalo_save_user_info', 'Admin\ZaloOAController@zalo_save_user_info');
Route::post('get_zalo_follower_selected', 'Admin\ZaloOAController@get_zalo_follower_selected');
Route::post('web_hook', 'Admin\WebhookController@send_to_callback_url');
Route::post('send_terminal_status', 'Terminal\TerminalController@checkStatusAndSendEmail');
Route::post('retrieve_vip_data', 'Admin\VipcustomerController@retrieve_vip_data');

// Đẩy thông tin thay đổi monitor vào database Sokcet IO
Route::post('update_tablet_socket_monitor', 'Fba\FbaTabletController@update_tablet_monitor');

// Thiết bị terminal
Route::post('fba_terminal_get_info', 'Fba\FbaTerminalController@fba_terminal_get_info');
Route::post('fba_terminal_change_power', 'Fba\FbaTerminalController@fba_terminal_change_power');
Route::post('fba_terminal_get_data', 'Fba\FbaTerminalController@fba_terminal_get_data');
Route::post('fba_terminal_set_configuration', 'Fba\FbaTerminalController@set_configuration');
Route::post('fba_terminal_set_token', 'Fba\FbaTerminalController@set_token');
Route::post('fba_terminal_set_time', 'Fba\FbaTerminalController@set_time');
// Người dùng
Route::post('userActiveEmail', 'Admin\UserController@userActiveEmail');
Route::post('forgotPassword', 'Admin\UserController@forgotPassword');

// schedule notification
Route::group(['prefix' => 'userMailReport'], function() {
	Route::post('descriptionMail', 'Admin\UserMailReportController@descriptionMail');
	Route::post('unsubcribeSchedule', 'Admin\UserMailReportController@unsubcribeSchedule');
	Route::post('unsubcribeNotification', 'Admin\UserMailReportController@unsubcribeNotification');
});