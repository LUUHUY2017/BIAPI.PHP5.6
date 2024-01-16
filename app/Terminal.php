<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Organization;
use App\Location;
use App\Site;
use Auth;
class Terminal extends Model
{
    protected $table = 'terminals';
    public $timestamps = false;

    public static function tryGetAllWithStatus($user_id, $organization_id) {
    	try {
    		$returnData = DB::select("exec sp_footfall_get_terminal_stats_2 $user_id, $organization_id");
    		return $returnData;
    	} catch (\Exception $e) {
    		throw $e;
    	}
    }
    public static function tryGetSettingManager($organization_id) {
        try {
            // $userInfo = Auth::user();
            $returnData = [
                '_site' => Site::tryGetSiteInRole($organization_id)
            ];
            $returnData['_location'] = DB::select("SELECT l.*, m.module_name FROM locations l INNER JOIN modules m ON l.module = m.id WHERE l.organization_id = $organization_id 
                AND l.actived = 1 AND l.deleted = 0");
            return $returnData;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function softDelete($deleteOption) {
        try {
            if ($deleteOption == 0) {
                $this->deleted = 1;
            } else if ($deleteOption == 1) {
                $this->deleted = 0;
            }
            return $this->save();
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
