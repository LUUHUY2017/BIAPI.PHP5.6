
<?php
/*
	AUTHOR: NGHIANT
	DATE: 25/06/2019
*/

use Illuminate\Http\Request;
use App\User;

Route::group(['middleware' => 'auth:api'], function () {
    // Thiết bị đếm
    Route::post('getListTerminalWithConfig', 'Terminal\TerminalController@getListTerminalWithConfig');
    Route::post('createNewTerminal', 'Terminal\TerminalController@createNewTerminal');
    Route::post('softDeleteTerminal', 'Terminal\TerminalController@softDeleteTerminal');
    Route::post('deleteTerminal', 'Terminal\TerminalController@deleteTerminal');
    Route::post('updateTerminal', 'Terminal\TerminalController@updateTerminal');
    // End
    Route::get('userEmailGetConfig', 'Common\CommonController@userEmailGetConfig');

    Route::post('getPageMailData', 'Admin\EmailController@getPageMailData');
    Route::post('getUserMailData', 'Admin\EmailController@getUserMailData');
    // manager Email
    Route::post('pageMailGetConfig', 'Common\CommonController@pageMailGetConfig');
    /********** Lấy thông tin tổ chức **********/
    Route::get('/userGetOrg', 'Common\CommonController@userGetOrg');
    /********** Lấy thông tin site thuộc role ******/
    Route::post('userGetSite', 'Admin\UserController@userGetSite');
    /********** End lấy thông tin tổ chức **********/
    /**************** CRUD Organizations****************/
    Route::post('get_organization_filter', 'Admin\OrganizationController@get_organization_filter');
    Route::post('insert_organization', 'Admin\OrganizationController@insert_organization');
    Route::post('update_organization_get', 'Admin\OrganizationController@get_update');
    Route::post('update_organization', 'Admin\OrganizationController@update_organization');
    Route::post('delete_organization', 'Admin\OrganizationController@delete');
    /************ END ************/
    /**************** CRUD Sites ****************/
    Route::post('get_site_tablets', 'Admin\SiteController@get_site_tablets');
    Route::post('insert_site', 'Admin\SiteController@insert_site');
    Route::post('update_site', 'Admin\SiteController@update_site');
    Route::post('delete_site', 'Admin\SiteController@delete');
    /************ END ************/
    /**************** CRUD location****************/
    Route::group(['prefix' => 'terminal'], function() {
        Route::post('getData', 'Terminal\TerminalController@getData');
        Route::post('insert', 'Terminal\TerminalController@insert');
        Route::post('update', 'Terminal\TerminalController@update');
        Route::post('delete', 'Terminal\TerminalController@delete');
        Route::group(['prefix' => 'usermail'], function() {
            Route::post('getData', 'Common\CommonController@terminalUserGetData');
            Route::post('update', 'Terminal\TerminalUserMailController@update');
        });
    });
    /**************** CRUD location****************/
    Route::group(['prefix' => 'organization'], function() {
        Route::post('getData', 'Admin\OrganizationController@getData');
        Route::post('insert', 'Admin\OrganizationController@insert');
        Route::post('update', 'Admin\OrganizationController@update');
        // Route::post('softDelete', 'Admin\OrganizationController@softDelete');
        Route::post('delete', 'Admin\OrganizationController@delete');
        Route::post('getRole', 'Admin\OrganizationController@getRole');
        Route::post('updateRole', 'Admin\OrganizationController@updateRole');
    });
    /**************** CRUD location****************/
    Route::group(['prefix' => 'categories'], function() {
        Route::post('getData', 'Admin\CategoriesController@getData');
        Route::post('insert', 'Admin\CategoriesController@insert');
        Route::post('update', 'Admin\CategoriesController@update');
        // Route::post('softDelete', 'Admin\CategoriesController@softDelete');
        Route::post('delete', 'Admin\CategoriesController@delete');
    });
    /**************** CRUD user****************/
    Route::group(['prefix' => 'user'], function() {
        Route::post('getData', 'Admin\UserController@getData');
        Route::post('insert', 'Admin\UserController@insert');
        Route::post('update', 'Admin\UserController@update');
        Route::post('softDelete', 'Admin\UserController@softDelete');
        Route::post('delete', 'Admin\UserController@delete');
        Route::post('sendNewPassword', 'Admin\UserController@sendNewPassword');
        Route::post('changePassword', 'Admin\UserController@changePassword');
        // lay phan quyen
        Route::post('getCurrentRoleUser', 'Admin\UserController@getCurrentRoleUser');
        Route::post('updateCurrentRoleUser', 'Admin\RoleController@update_user_single_role');
    });
    /**************** CRUD location****************/
    Route::group(['prefix' => 'location'], function() {
        Route::post('getData', 'Admin\LocationController@getData');
        Route::post('insert', 'Admin\LocationController@insert');
        Route::post('update', 'Admin\LocationController@update');
        Route::post('softDelete', 'Admin\LocationController@softDelete');
        Route::post('delete', 'Admin\LocationController@delete');
    });
    /************ END ************/
    /**************** CRUD location****************/
    Route::group(['prefix' => 'site'], function() {
        Route::post('getData', 'Admin\SiteController@getData');
        Route::post('insert', 'Admin\SiteController@insert');
        Route::post('update', 'Admin\SiteController@update');
        Route::post('softDelete', 'Admin\SiteController@softDelete');
        // Route::post('delete', 'Admin\SiteController@delete');
    });
    /************ END ************/
    /**************** CRUD client****************/
    Route::group(['prefix' => 'client'], function() {
        Route::post('getData', 'Admin\ClientController@getData');
        Route::post('insert', 'Admin\ClientController@insert');
        Route::post('update', 'Admin\ClientController@update');
        // Route::post('softDelete', 'Admin\ClientController@softDelete');
        Route::post('delete', 'Admin\ClientController@delete');
    });
    /************ END ************/
    /**************** CRUD client****************/
    Route::group(['prefix' => 'zaloFollower'], function() {
        Route::post('getData', 'Admin\ZaloFollowerController@getData');
        Route::post('insert', 'Admin\ZaloFollowerController@insert');
        Route::post('update', 'Admin\ZaloFollowerController@update');
        Route::post('softDelete', 'Admin\ZaloFollowerController@softDelete');
        Route::post('delete', 'Admin\ZaloFollowerController@delete');
    });
    /************ END ************/

    Route::group(['prefix' => 'mail_configuration'], function() {
        Route::post('getData', 'Admin\MailConfigController@getData');
        Route::post('insert', 'Admin\MailConfigController@insert');
        Route::post('update', 'Admin\MailConfigController@update');
        Route::post('softDelete', 'Admin\MailConfigController@softDelete');
        Route::post('delete', 'Admin\MailConfigController@delete');
    });
    Route::group(['prefix' => 'userInfo'], function() {
        Route::get('getMailScheduleInfo', 'Common\CommonController@userGetMailScheduleInfo');
        Route::post('getSpecificPageSchedule', 'Common\CommonController@getSpecificPageSchedule');
    });
    //
    Route::group(['prefix' => 'userMailReport'], function() {
        Route::post('insert', 'Admin\UserMailReportController@insert');
        Route::post('update', 'Admin\UserMailReportController@update');
        Route::post('softDelete', 'Admin\UserMailReportController@softDelete');
        Route::post('delete', 'Admin\UserMailReportController@delete');
        Route::post('userMailScheduleGetData', 'Common\CommonController@userMailScheduleGetData');
        Route::post('checkExistParam', 'Admin\UserMailReportController@checkExistParam');

    });
    /**************** CRUD application settings****************/
    Route::group(['prefix' => 'fbaAppSetting'], function() {
        Route::post('getData', 'Fba\FbaAppicationSettingController@getData');
        Route::post('insert', 'Fba\FbaAppicationSettingController@insert');
        Route::post('update', 'Fba\FbaAppicationSettingController@update');
        Route::post('getUpdate', 'Fba\FbaAppicationSettingController@getUpdate');
        Route::post('softDelete', 'Fba\FbaAppicationSettingController@softDelete');
        Route::post('delete', 'Fba\FbaAppicationSettingController@delete');
    });
    /**************** CRUD application settings****************/
    Route::group(['prefix' => 'fbaTablet'], function() {
        Route::post('getData', 'Admin\FbaTabletController@getData');
        Route::post('insert', 'Admin\FbaTabletController@insert');
        Route::post('update', 'Admin\FbaTabletController@update');
        Route::post('softDelete', 'Admin\FbaTabletController@softDelete');
        Route::post('delete', 'Admin\FbaTabletController@delete');

    });

     // Nghĩa thêm Resful API cho bảng application settings
     Route::resource('fba_application_settings', 'Fba\FbaAppicationSettingController');
     Route::post('fba_application_settings_delete', 'Fba\FbaAppicationSettingController@delete');
     Route::get('fba_application_settings_update/{id}', 'Fba\FbaAppicationSettingController@get_update');
     Route::post('fba_application_settings_update/{id}', 'Fba\FbaAppicationSettingController@post_update');

    /**************** CRUD user role****************/
    Route::post('update_user_single_role', 'Admin\RoleController@update_user_single_role');
    /************ END ************/

    /**************** CRUD application settings****************/
    /**************** CRUD ZALO OFFICIAL ACCOUNT***************/
    Route::group(['prefix' => 'oaZalo'], function() {
        Route::post('getData', 'Admin\ZaloOAController@getData');
        // Route::post('insert', 'Admin\ZaloOAController@insert');
        Route::post('update', 'Admin\ZaloOAController@update');
        // Route::post('softDelete', 'Admin\ZaloOAController@softDelete');
        // Route::post('delete', 'Admin\ZaloOAController@delete');
        Route::group(['prefix' => 'event'], function() {
            Route::post('getData', 'Admin\ZaloOAController@eventGetData');
            Route::post('insert', 'Admin\ZaloOAController@eventInsert');
            Route::post('update', 'Admin\ZaloOAController@eventUpdate');
            Route::post('delete', 'Admin\ZaloOAController@eventDelete');
            Route::post('eventGetFollower', 'Admin\ZaloOAController@eventGetFollower');
            Route::post('updateEventAndFollower', 'Admin\ZaloOAController@updateEventAndFollower');
            Route::post('eventAccFollowerDelete', 'Admin\ZaloOAController@eventAccFollowerDelete');
        });
    });
    //
    Route::post('sp_get_oa_zalo', 'Admin\ZaloOAController@sp_get_oa_zalo');
    Route::post('sp_get_oa_zalo_insert', 'Admin\ZaloOAController@postCreate');
    Route::post('sp_get_oa_zalo_update', 'Admin\ZaloOAController@postUpdate');
    Route::post('sp_get_oa_zalo_delete', 'Admin\ZaloOAController@softDelete');

    Route::post('sp_get_oa_zalo_get_event_module', 'Admin\ZaloOAController@get_event_module');
    Route::post('sp_get_oa_zalo_get_follower_in_event', 'Admin\ZaloOAController@get_follower_in_event');
    Route::post('sp_get_oa_zalo_insert_event_and_follower', 'Admin\ZaloOAController@insert_event_and_follower');
    /************ END ************/

    /**************** CRUD ZALO FOLLOWER settings****************/
    Route::post('sp_get_zalo_follower', 'Admin\ZaloFollowerController@sp_get_zalo_follower');
    Route::post('sp_get_zalo_follower_get_oa', 'Admin\ZaloFollowerController@get_oa_zalo');
    Route::post('sp_get_zalo_follower_update', 'Admin\ZaloFollowerController@post_update');
    Route::post('sp_get_zalo_follower_soft_delete', 'Admin\ZaloFollowerController@soft_delete');
    /************ END ************/

    // Thiết bị tablets
    Route::post('get_tablet_filter', 'Fba\FbaTabletController@get_tablet_filter');
    Route::post('insert_tablet', 'Fba\FbaTabletController@insert_tablet');
    Route::post('update_tablet', 'Fba\FbaTabletController@update_tablet');
    Route::post('update_tablet_monitor', 'Fba\FbaTabletController@update_tablet_monitor');
    Route::post('delete_tablet', 'Fba\FbaTabletController@delete_tablet');
    // Thiết bị terminals
    Route::post('fba_get_terminals', 'Fba\FbaTerminalController@fba_get_terminals');
    Route::post('fba_get_terminals_update', 'Fba\FbaTerminalController@fba_get_terminals_update');
    Route::post('fba_get_terminals_insert', 'Fba\FbaTerminalController@fba_get_terminals_insert');
    Route::post('fba_get_terminals_delete', 'Fba\FbaTerminalController@fba_get_terminals_delete');
    // Nghĩa thêm function lấy quyền người dùng
    Route::get('get_users_filter_get_insert', 'Admin\UserController@get_insert');
    // Location cho thiết bị
    Route::post('get_location_tablets', 'Admin\LocationController@get_location_tablets');
    /**************** CRUD webhook****************/
    // Route::post('sp_get_web_hook', 'Admin\WebhookController@sp_get_web_hook');
    // Route::post('sp_get_web_hook_insert', 'Admin\WebhookController@post_add');
    // Route::post('sp_get_web_hook_update', 'Admin\WebhookController@post_edit');
    // Route::post('sp_get_web_hook_soft_delete', 'Admin\WebhookController@soft_delete');
    // Route::post('sp_get_web_hook_delete', 'Admin\WebhookController@delete');
    /************ END ************/

    // Tool C#
    Route::post('staff_user_get', 'Admin\StaffUploadController@staff_user_get');
    Route::post('staff_post_database', 'Admin\StaffUploadController@staff_post_database');
    Route::post('staff_get_info_with_phone_number', 'Admin\StaffUploadController@get_info_with_phone_number');

    /**************** CRUD BLACKLIST****************/
    Route::post('black_list', 'Admin\BlacklistController@sp_get_black_list_organization');
    Route::post('black_list_insert', 'Admin\BlacklistController@insert');
    Route::post('black_list_update', 'Admin\BlacklistController@update');

    // Tool C#
    Route::post('black_list_user_get', 'Admin\BlacklistController@black_list_user_get');
    Route::post('black_list_post_database', 'Admin\BlacklistController@black_list_post_database');
    Route::post('black_list_get_info_with_phone_number', 'Admin\BlacklistController@get_info_with_phone_number');

    /**************** CRUD VIP****************/
    Route::post('vip_user', 'Admin\VipcustomerController@sp_get_vip_customer_organization');
    Route::post('vip_user_insert', 'Admin\VipcustomerController@insert');
    Route::post('vip_user_update', 'Admin\VipcustomerController@update');
    Route::post('insert_vip_img_api', 'Admin\VipcustomerController@insert_vip_img_api');
    Route::post('insert_vip_customer_api', 'Admin\VipcustomerController@insert_vip_customer_api');

    // Tool C#
    Route::post('vip_user_get', 'Admin\VipcustomerController@sp_get_vip_customer');
    Route::post('vip_customer_post_database', 'Admin\VipcustomerController@vip_customer_post_database');
    Route::post('vip_customer_get_info_with_phone_number', 'Admin\VipcustomerController@get_info_with_phone_number');

});
