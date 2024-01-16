<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Exception;
use DB;
use App\Site;

class UserEmailModule extends Model
{
    //
    protected $table = 'user_email_module';
    public $timestamps = false;
    public static function tryGetSpecificDataWithPageId($pageId, $userId) {
        try {
            // $currentData = self::where('page_id', $pageId)->where('user_id', $userId)->orderBy('created_at', 'DESC')->get();
            $currentData = DB::select("SELECT * FROM user_email_module uem WHERE uem.page_id = $pageId AND uem.user_id = $userId ORDER BY uem.created_at DESC");
            if (count($currentData) > 0) {
               foreach ($currentData as $value) {
                    try {
                        $parsedParam = json_decode($value->params);
                        $siteId = $parsedParam->site_id;
                        $orgId = $parsedParam->organization_id;
                        $siteOrgInfo = Site::tryGetOrganizationInfo($siteId, $orgId);
                        if ($siteOrgInfo === null) {
                            $value->site_name = null;
                            $value->organization_name = null;
                        } else {
                            $value->site_name = $siteOrgInfo->site_name;
                            $value->organization_name = $siteOrgInfo->organization_name;
                        }
                    } catch (Exception $e) {
                        $value->site_name = null;
                        $value->organization_name = null;
                    }
                } 
            }
            return $currentData;
        } catch (Exception $e) {
            throw $e;
        }
    }
    // Quan tri thiet bi
    public function tryGetDataWithPageId($org_id) {
        try {
            $currentData = DB::select("SELECT uem.*, u.name, u.email FROM user_email_module uem INNER JOIN users u ON uem.user_id = u.id WHERE uem.report_type = 0 AND uem.organization_id = $org_id ORDER BY uem.created_at DESC");
            if (count($currentData) > 0) {
               foreach ($currentData as $value) {
                    try {
                        $parsedParam = json_decode($value->params);
                        $siteId = $parsedParam->site_id;
                        $orgId = $parsedParam->organization_id;
                        $siteOrgInfo = Site::tryGetOrganizationInfo($siteId, $orgId);
                        if ($siteOrgInfo === null) {
                            $value->site_name = null;
                            $value->organization_name = null;
                        } else {
                            $value->site_name = $siteOrgInfo->site_name;
                            $value->organization_name = $siteOrgInfo->organization_name;
                        }
                    } catch (Exception $e) {
                        $value->site_name = null;
                        $value->organization_name = null;
                    }
                } 
            }
            return $currentData;
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function tryGetDataWithPageIdAndModuleId($module_id, $organization_id) {
    	try {
            $currentData = DB::select("SELECT uem.*, u.name, u.email, p.page_name FROM user_email_module uem INNER JOIN users u ON uem.user_id = u.id INNER JOIN pages p ON uem.page_id = p.id WHERE uem.report_type != 0 AND uem.organization_id = $organization_id AND uem.module_id = $module_id ORDER BY uem.created_at DESC");
            // if (count($currentData) > 0) {
            //    foreach ($currentData as $value) {
            //         try {
            //             $parsedParam = json_decode($value->params);
            //             $siteId = $parsedParam->site_id;
            //             $orgId = $parsedParam->organization_id;
            //             $siteOrgInfo = Site::tryGetOrganizationInfo($siteId, $orgId);
            //             if ($siteOrgInfo === null) {
            //                 $value->site_name = null;
            //                 $value->organization_name = null;
            //             } else {
            //                 $value->site_name = $siteOrgInfo->site_name;
            //                 $value->organization_name = $siteOrgInfo->organization_name;
            //             }
            //         } catch (Exception $e) {
            //             $value->site_name = null;
            //             $value->organization_name = null;
            //         }
            //     } 
            // }
            return $currentData;
        } catch (Exception $e) {
            throw $e;
        }
     //    try {
     //        return DB::select("SELECT uem.*, u.name, u.email, o.organization_name, s.site_name, p.page_name FROM $this->table uem INNER JOIN users u ON uem.user_id = u.id LEFT JOIN sites s ON uem.site_id = s.id INNER JOIN organizations o ON uem.organization_id = o.id INNER JOIN pages p ON p.id = uem.page_id
     //            WHERE uem.report_type != 0 AND uem.module_id = $module_id AND uem.organization_id = $organization_id");
    	// } catch (Exception $e) {
    	// 	throw $e;
    	// }
    }

    public function tryGetDataWithUserId($user_id, $module_id) {
        try {
            if ($user_id == 0) {
                return DB::select("SELECT uem.*, u.name, u.email, o.organization_name, s.site_name, p.page_name FROM $this->table uem INNER JOIN users u ON uem.user_id = u.id INNER JOIN sites s ON uem.site_id = s.id INNER JOIN organizations o ON uem.organization_id = o.id INNER JOIN pages p ON p.id = uem.page_id WHERE uem.module_id = $module_id");
            } else {
                return DB::select("SELECT uem.*, u.name, u.email, o.organization_name, s.site_name, p.page_name FROM $this->table uem INNER JOIN users u ON uem.user_id = u.id INNER JOIN sites s ON uem.site_id = s.id INNER JOIN organizations o ON uem.organization_id = o.id INNER JOIN pages p ON p.id = uem.page_id WHERE uem.user_id = $user_id AND uem.module_id = $module_id");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
}
