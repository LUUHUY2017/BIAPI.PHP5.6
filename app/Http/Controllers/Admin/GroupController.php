<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\User;
use App\Site;
use App\Group;
class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request_user = $request->user();
        $user_id = $request_user->id;
        
        $organization_id = 0;
        
        if($request_user->organization_id)
           $organization_id = (int)$request_user->organization_id;
        $deleted = 0;
        $object = DB::select("exec sp_general_group $user_id, $organization_id, $deleted");
        return response()->json(['data' => $object]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request_user = $request->user();
        $organization_id = (int)$request_user->organization_id;
        $data = json_decode($request->data);
        DB::beginTransaction();
        try {
            $group = new Group();
            if(isset($data[0]->organization_id)) {
                $organization_id = $data[0]->organization_id;
            }
            $group->organization_id = $organization_id;
            $group->actived = $data[0]->actived;
            $group->group_name = $data[0]->group_name;
            $group->group_description = $data[0]->group_description;
            $group->save();
            if($data[0]->site_array !== []) {
                foreach ($data[0]->site_array as $value) {
                    $group_site = DB::select("SELECT id, site_id, group_id FROM group_site WHERE group_id = $group->id AND site_id = $value");
                    if($group_site === []) {
                        $array = DB::table('group_site')->insert([
                            'group_id' => $group->id,
                            'site_id' => $value
                        ]);
                    }
                }    
            }
            if($data[0]->user_array !== []) {
                foreach ($data[0]->user_array as $value) {
                    $group_user = DB::select("SELECT id, user_id, group_id FROM group_user WHERE group_id = $group->id AND user_id = $value");
                    if($group_user === []) {
                        $array = DB::table('group_user')->insert([
                            'group_id' => $group->id,
                            'user_id' => $value
                        ]);
                    }
                }    
            }
            DB::commit();
            return response()->json(['message' => 1]);
        }
        catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 0]);
        }
    }

    public function get_user_and_site(Request $request, $id)
    {
        $org_id = (int)$id;
        if($org_id === 0) {
            $org_id = 6;
        }
        $users = User::where('organization_id', $org_id)->get();
        $sites = Site::where('organization_id', $org_id)->get();
        return response()->json(['users' => $users,'sites' => $sites]);
        /*if($request->user()) {
        }*/
    }

    public function delete(Request $request)
    {
        try {
            DB::beginTransaction();
            DB::table('group_user')->where('group_id',$request->id)->delete();
            DB::table('group_site')->where('group_id',$request->id)->delete();
            DB::table('groups')->where('id',$request->id)->delete();
            DB::commit();
            return response()->json(['message' => 1]);
        }
        catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 0]);
        }
    }
    public function get_update($id) {
        DB::beginTransaction();
        try {
            $group = DB::table('groups')->where('id',$id)->get();
            $group_site = DB::table('group_site')->where('group_id',$id)->get();
            $group_user = DB::table('group_user')->where('group_id',$id)->get();
            DB::commit();
            return response()->json(['group' => $group, 'group_site' => $group_site, 'group_user' => $group_user]);
        }
        catch (\Exception $e) {
            DB::rollback();
            return response()->json(['data' => 0]);
        }
    }

    public function post_update(Request $request, $id) {
        DB::beginTransaction();
        try {
            // xóa group_site và group_user theo group_id
            DB::table('group_user')->where('group_id',$id)->delete();
            DB::table('group_site')->where('group_id',$id)->delete();
            // end xóa
            // sửa group
            $request_user = $request->user();
            $organization_id = (int)$request_user->organization_id;
            $data = json_decode($request->data);
            $object = Group::find($id);
            $object->group_name = $data[0]->group_name;
            if(isset($data[0]->organization_id)) {
                $organization_id = $data[0]->organization_id;
            }
            $object->organization_id = $organization_id;
            $object->group_description = $data[0]->group_description;
            $object->actived = $data[0]->actived;
            $object->save();
            // end sửa group
            // sửa mới group_site
            if($data[0]->site_array !== []) {
                foreach ($data[0]->site_array as $value) {
                    DB::table('group_site')->insert([
                        'group_id' => $object->id,
                        'site_id' => $value
                    ]);
                }
            }
            // end sửa mới
            // sửa mới group_user
            if($data[0]->user_array !== []) {
                foreach ($data[0]->user_array as $value) {
                    DB::table('group_user')->insert([
                        'group_id' => $object->id,
                        'user_id' => $value
                    ]);
                }
            }
            // end sửa mới
            DB::commit();
            return response()->json(['message' => 1]);
        }
        catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 0]);
        }
    }
}
