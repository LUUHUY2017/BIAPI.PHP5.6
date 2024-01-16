<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Hash;

class Controller extends BaseController
{
    public $code_err = 296;
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function stripUnicode($str)
    {
        if (!$str) return false;
        $unicode = array(
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
        );
        foreach ($unicode as $nonUnicode => $uni) $str = preg_replace("/($uni)/i", $nonUnicode, $str);
        return trim($str);
    }

    public function getDefaultModule()
    {
        $moduleArray = array(
            array('label' => 'Hệ thống footfall', 'value' => '1'), array('label' => 'Trải nghiệm khách hàng', 'value' => '2'), array('label' => 'Giới tính độ tuổi', 'value' => '3'), array('label' => 'Hiệu quả hoạt động', 'value' => '4')
        );
        return $moduleArray;
    }

    public function randomMd5String($length = 16)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }
    public function getDateNow()
    {
        return Carbon::now()->format('Y-m-d H:i:s');
    }

    public function isSuperAdmin($userInfo) {
        return $userInfo->organization_id == 0 && $userInfo->lever == 0;
    }

    public function isOrgAdmin($userInfo) {
        try {
            $orgId = $userInfo->organization_id;
            $adminRole = DB::select("SELECT * FROM fc_get_admin_organization($orgId)");
            foreach ($adminRole as $key => $value) {
                if ($value->id == $userInfo->id) {
                    return true;
                }
            }
            return false;
        } catch (Exception $e) {
            throw $e;
        }
    }

    function changeSecondsToformatTime($seconds)
    {
        $t = round($seconds);
        return sprintf('%02d:%02d:%02d', ($t / 3600), ($t / 60 % 60), $t % 60);
    }

    public function saveImageFile($file, $pathSaveFile) {
        try {
            $extension = $file->getClientOriginalName();
            $imageName = $extension;
            $file->move($pathSaveFile, $imageName);
            // $thumbnailpath = $path . "/$extension";
            // $img = Image::make($thumbnailpath)->resize(125, 125)->save($thumbnailpath);
            return $imageName; 
        } catch (\Exception $e) {
            throw $e;
        }
        
    }

    public function getRandomStringHashed($str) {
        return Hash::make($str);
    }
}
