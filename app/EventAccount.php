<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Exception;
use DB;

class EventAccount extends Model
{
    protected $table = 'event_account';
    public $timestamps = false;

    public static function tryGetAllAccountZalo($orgId) {
    	try {
    		$returnData = DB::select("SELECT z.id, z.display_name, z.gender, e.event_name, e.event_code, eaf.id AS eaf_id FROM events e INNER JOIN event_account ea ON e.id = ea.event_id INNER JOIN oa_zalo oa ON ea.account_id = oa.id INNER JOIN event_account_follower eaf ON eaf.event_account_id = ea.id INNER JOIN zalo_follower z ON eaf.follower_id = z.id WHERE oa.organization_id = $orgId");
    		return $returnData;
    	} catch (Exception $e) {
    		throw $e;
    	}
    }
}
