<?php

namespace App\Http\Controllers\Admin;

use App\UserPageParametter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Exception;
use App\PageLog;

class UserPageParametterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
    {
        $this->middleware('logviewreport');
    }
    public function index(Request $request)
    {
        $request_user = $request->user();
        $user_id = $request_user->id;
        $page_id = $request->page_id;

        return response()->json(UserPageParametter::where('user_id',  $user_id)->where('page_id',  $page_id)->get());
    }
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request_user = $request->user();
            $user_id = $request_user->id;
            $page_id = $request->page_id;
            $parametter = $request->parametter;

            $obj = UserPageParametter::where('user_id',  $user_id)->where('page_id',  $page_id)->first();
            if ($obj == null) {
                $obj = new UserPageParametter;
            }
            $obj->user_id =  $user_id;
            $obj->page_id =  $page_id;
            $obj->parametter = $parametter;
            $obj->save();
            $pageId = $request->page_id;
            $now = $this->getDateNow();
            $currentPageId = $request->header('page-id');
            if ($currentPageId == null) {
                $this->trySavePageLog($now, $now, $request_user->id, $pageId);
            } else if ($pageId != $currentPageId) {
                $this->trySavePageLog($now, $now, $request_user->id, $pageId);
            }
            DB::commit();
            return response()->json(['insertedData' => $obj, 'page_id' => $pageId]);
        } catch (\Exception $e) {
            $response = [];
            DB::rollback();
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }
    public function trySavePageLog($startTime, $endTime, $userId, $pageId)
    {
        try {
            $newInstance = new PageLog();
            $newInstance->created_at = $startTime;
            $newInstance->start_time = $startTime;
            $newInstance->end_time = $endTime;
            $newInstance->u_id = $userId;
            $newInstance->page_id = $pageId;
            $newInstance->save();
            return $newInstance;
        } catch (Exception $e) {
            return null;
        }
    }
}
