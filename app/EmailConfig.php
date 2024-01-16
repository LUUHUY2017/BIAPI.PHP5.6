<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Exception;

class EmailConfig extends Model
{
    //
    protected $table = 'email_configuration';
    public $timestamps = false;

    public static function tryGetConfigByOrgId($orgId) {
    	try {
    		$returnData = self::where('organization_id', $orgId)->first();
    		if ($returnData === null) {
    			$returnData = self::where('organization_id', 0)->first();
    		}
    		return $returnData;
    	} catch (Exception $e) {
    		throw $e;
    	}
    }
}
