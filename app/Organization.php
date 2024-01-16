<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Exception;
use Auth;
use DB;
class Organization extends Model
{
    protected $table = 'organizations';
    public $timestamps = false;

    // for config
    public function tryGetAllData($columnArray, $desc = 'ASC') {
    	try {
    		$userInfo = Auth::user();
    		$orgId = (int) $userInfo->organization_id;
    		$lever = (int) $userInfo->lever;
    		if ($lever === 0 && $orgId === 0) {
    			$returnData = $this::select($columnArray)->orderBy('created_at', $desc)->where('actived', 1)->where('deleted', 0)->get();
    		} else {
    			$returnData = $this::select($columnArray)->orderBy('created_at', $desc)->where('actived', 1)->where('deleted', 0)->where('id', $orgId)->get();
    		}
    		return $returnData;
    	} catch (Exception $e) {
    		throw $e;
    	}
    }
    // for CRUD
    public function tryGetAllDataCrud($columnArray, $desc = 'ASC') {
        try {
            $userInfo = Auth::user();
            $orgId = (int) $userInfo->organization_id;
            $lever = (int) $userInfo->lever;
            if ($lever === 0 && $orgId === 0) {
                $returnData = $this::select($columnArray)->orderBy('created_at', $desc)->get();
            } else {
                $returnData = $this::select($columnArray)->orderBy('created_at', $desc)->where('id', $orgId)->get();
            }
            return $returnData;
        } catch (Exception $e) {
            throw $e;
        }
    }

    // lấy role

    public static function tryGetCurrentRole($orgId) {
        try {
            $roleData = DB::select("SELECT im.*, ri.expire_date FROM fc_get_parent_role($orgId) fc INNER JOIN role_index ri ON fc.id = ri.role_id INNER JOIN index_module im ON ri.index_id = im.id WHERE fc.role_type = 0");
            return $roleData;
        } catch (Exception $e) {
            throw $e;
        }
    }

}
