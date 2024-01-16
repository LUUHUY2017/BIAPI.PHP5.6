<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Site;
use DB;

class FbaTablet extends Model
{
    protected $table = 'fba_tablets';
    public $timestamps = false;
    public $keyType = 'string';

    public static function tryGetSettingManager($organization_id) {
        try {
            // $userInfo = Auth::user();
            $returnData = [
                '_site' => Site::tryGetSiteInRole($organization_id)
            ];
            $returnData['_location'] = DB::select("SELECT l.*, m.module_name FROM locations l INNER JOIN modules m ON l.module = m.id WHERE l.organization_id = $organization_id
                AND l.actived = 1 AND l.deleted = 0 AND l.module = 2");
            return $returnData;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
