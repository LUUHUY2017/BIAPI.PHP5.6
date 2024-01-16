<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Carbon\Carbon;
use DB;
use Auth;
use Exception;

class User extends Authenticatable
{
    //use Notifiable;
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public $timestamps = false;

    public static function generateTokenEmailUrl($email, $token, $sourceUrl) {
        $url = "$sourceUrl/#/active-mail/$email/$token";
        return $url;
    }


    public function activeUserFromTokenEmail() {
        try {
            // if ($this->token_email == null || $this->token_email_expired_time == null) {
            //     throw new Exception(json_encode(['Đường link không tồn tại']), 770);
            // }
            // $date_compare = DB::select("SELECT CASE WHEN CONVERT(DATE, GETDATE()) <= CONVERT(DATE, '$this->token_email_expired_time') THEN 1 ELSE 0
            //     END AS date_compare")[0]->date_compare;
            // if (!$date_compare) {
            //     throw new Exception(json_encode(['Token đã hết hạn']), 770);
            // }
            $this->token_email = null;
            $this->token_email_expired_time = null;
            // $this->actived = 1;
            $this->save();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function userGetRoleSiteForUpdateRole($orgId, $userCompareId) {
        try {
            $userInfo = Auth::user();
            $currentUserId = $userInfo->id;
            $returnData = DB::select("SELECT a.id, a.parent_id, a.site_name, a.enables, CASE WHEN fc.id IS NULL THEN 0 ELSE 1 END AS permission_in_site FROM
            (
                SELECT s.*, CASE WHEN fc.enables IS NULL THEN 0 ELSE fc.enables END AS enables
                FROM sites s LEFT JOIN fc_get_list_site_crud($orgId, $userCompareId) fc ON s.id = fc.id
                WHERE s.organization_id = $orgId AND s.deleted = 0 AND s.actived = 1
            ) a LEFT JOIN fc_get_list_site_crud($orgId,$currentUserId) fc ON a.id = fc.id");
            return $returnData;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
