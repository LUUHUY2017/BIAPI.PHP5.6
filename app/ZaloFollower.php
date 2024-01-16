<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Exception;

class ZaloFollower extends Model
{
    protected $table = 'zalo_follower';

    public $timestamps = false;

    public function tryGetData($orgId) {
    	try {
    		return DB::select("SELECT z.*, s.site_name, o.organization_name, oa.display_name as oa_name FROM $this->table z INNER JOIN oa_zalo oa ON z.oa_id = oa.id LEFT JOIN sites s ON z.site_id = s.id INNER JOIN organizations o ON z.organization_id = o.id WHERE z.organization_id = $orgId");
    	} catch (Exception $e) {
    		throw $e;
    	}
    }
}
