<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;
use Exception;
class Site extends Model
{
    protected $table = 'sites';
    public $timestamps = false;

    public static function tryGetOrganizationInfo($siteId, $orgId) {
        try {
            if ($siteId == 0) {
                $siteOrgInfo = DB::table('organizations')->where('id', $orgId)->select('organizations.organization_name AS site_name', 'organizations.organization_name')->first();
            } else {
                $siteOrgInfo = DB::table('sites')->rightjoin('organizations', 'sites.organization_id', '=', 'organizations.id')->select('sites.site_name', 'organizations.organization_name')->where('sites.id', $siteId)->where('organizations.id', $orgId)->first();
            }
            return $siteOrgInfo;
        } catch (Exception $e) {
            return null;
        }
    }

    public static function tryGetWithChildrenNode($siteId) {
    	try {
			$userInfo = Auth::user();
			$organizationId = $userInfo->organization_id;
			$userId = $userInfo->id;
			$returnData = DB::select("SELECT * FROM fc_get_site_children_with_organization_v2($siteId, $organizationId)");
			return $returnData;
    	} catch (Exception $e) {
    		throw $e;
    	}
    }
    public static function tryGetSiteInRole($orgId) {
        try {
            $userInfo = Auth::user();
            $userId = $userInfo->id;
            $returnData = DB::select("SELECT * FROM fc_get_site_in_role($orgId, $userId)");
            return $returnData;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function tryGetDataCrud($orgId, $deleted) {
        try {
            $userInfo = Auth::user();
            $userId = $userInfo->id;
            $returnData = DB::select("SELECT s.*, CASE WHEN fc.enables IS NULL THEN 0 ELSE fc.enables END AS enables FROM sites s LEFT JOIN fc_get_list_site_crud($orgId, $userId) fc ON s.id = fc.id WHERE s.organization_id = $orgId AND s.deleted = $deleted");
            return $returnData;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
