<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use DB;

class Role extends Model
{
    protected $table = 'roles';
    public $timestamps = false;

    public static function tryCheckInParentRole($roleId, $orgId) {
        try {
        	$userInfo = User::findOrFail($userId);
        	$orgId = $userInfo->organization_id;
            $roleData = DB::select("SELECT fc.* FROM fc_get_parent_role($orgId) fc WHERE fc.id = $roleId");
            return $roleData;
        } catch (Exception $e) {
            throw $e;
        }
    }
    public static function tryGetCurrentUserRole($userId) {
        try {
            $roleUser = DB::select("SELECT r.*, u.name AS username, u.id AS user_id FROM roles r INNER JOIN role_user ru ON r.id = ru.role_id INNER JOIN users u ON ru.user_id = u.id WHERE ru.user_id = $userId");
            return $roleUser;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
