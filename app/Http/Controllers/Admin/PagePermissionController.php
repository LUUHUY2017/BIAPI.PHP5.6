<?php

namespace App\Http\Controllers\Admin;

use App\PagePermission;
use App\Page;
use App\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

class PagePermissionController extends Controller
{
    public function index(Request $request)
    {
        $request_user = $request->user();
        // Nghĩa thêm page
        $page_arr = DB::table('pages')->select('id AS value', 'page_name AS label')->get();
        $id = $request->id ? $request->id : $page_arr[0]->value;
        $pp = $this->get_page_permission($id);
        return response()->json(['data' => $pp, 'page_arr' => $page_arr]);
    }

    public function get_page_from_id(Request $request)
    {
        $request_user = $request->user();
        $id = $request->id;
        $pp = $this->get_page_permission($id);
        return response()->json(['data' => $pp]);
    }
    public function get_page_permission($id = NULL)
    {
        $page_permission = DB::table('page_permission')
            ->join('pages', 'pages.id', '=', 'page_permission.page_id')
            ->join('permissions', 'permissions.id', '=', 'page_permission.permission_id')
            ->select('page_permission.id', 'page_id', 'page_name', 'page_detail', 'permission_id', 'permission_name', 'permission_detail');
        if ($id !== NULL) {
            $page_permission->where('page_id', '=', $id);
        }
        return $page_permission->get();
    }
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            if ($request->permission_array !== [] && $request->page_id) {
                foreach ($request->permission_array as $value) {
                    $permission_id = $value['item_id'];
                    DB::table('page_permission')->insert([
                        'page_id' => $request->page_id, 'permission_id' => $permission_id
                    ]);
                }
            }
            DB::commit();
            return response()->json(['message' => 1]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 0]);
        }
    }

    public function get_insert()
    {
        try {
            $pages = DB::select("SELECT id AS value,page_name AS label FROM pages");
            $permissions = Permission::All();
            $page_arr = $pages;
            return response()->json(['pages' => $page_arr, 'permissions' => $permissions]);
        } catch (\Exception $e) {
            return response()->json(['message' => 0]);
        }
    }

    public function get_update($id)
    {
        try {
            // $get_page = DB::select("SELECT page_permission.page_id, page_permission.permission_id, permissions.permission_name FROM page_permission INNER JOIN permissions ON page_permission.permission_id = permissions.id INNER JOIN pages ON page_permission.page_id = pages.id WHERE page_permission.id = $id");
            // $pages = DB::select("SELECT id AS value,page_name AS label FROM pages");
            // $permissions = DB::select("SELECT id AS value, permission_name AS label FROM permissions");
            // return response()->json(['pages' => $pages,'permissions' => $permissions,'get_page' => $get_page]);
            $pages = DB::select("SELECT id AS value,page_name AS label FROM pages");
            $permissions = Permission::All();
            $page_arr = $pages;
            return response()->json(['pages' => $page_arr, 'permissions' => $permissions]);
        } catch (\Exception $e) {
            return response()->json(['message' => 0]);
        }
    }
    public function merge_array(&$get_page)
    {
        $per_id = $get_page[0]->permission_id;
        $get_page[0]->permission_id = array();
        $get_page[0]->permission_id[] = $per_id;
        for ($i = 1; $i <= count($get_page); $i++) {
            $get_page[0]->permission_id[] = $get_page[$i]->permission_id;
            unset($get_page[$i]);
        }
    }
    public function post_update(Request $request, $id)
    {
        //
    }
    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try {
            $id = (int)$request->id;
            DB::table('page_permission')->where('id', $id)->delete();
            DB::commit();
            return response()->json(['message' => 1]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 0]);
        }
    }
    public function get_module_user(Request $request)
    {
        $request_user = $request->user();
        $user_id = $request_user->id;
        $organization_id = (int)$request_user->organization_id;
        $pages = DB::select("SELECT * FROM [fc_get_module_for_user] ($organization_id, $user_id )");
        return response()->json($pages);
    }
}
