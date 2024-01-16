<?php

namespace App\Http\Controllers\Admin;

use App\PagePermistion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PagePermistionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request_user = $request->user();
        $role_id = 1;
    
        $pp = DB::table('page_permission')
            ->join('pages', 'pages.id', '=', 'page_permission.page_id')
            ->join('permissions', 'permissions.id', '=', 'page_permission.permission_id')
            ->select('page_permission.id', 'page_id', 'page_name', 'page_detail', 'permission_id', 'permission_name', 'permission_detail')
            ->get();
        return response()->json($pp);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\PagePermistion  $pagePermistion
     * @return \Illuminate\Http\Response
     */
    public function show(PagePermistion $pagePermistion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\PagePermistion  $pagePermistion
     * @return \Illuminate\Http\Response
     */
    public function edit(PagePermistion $pagePermistion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PagePermistion  $pagePermistion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PagePermistion $pagePermistion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PagePermistion  $pagePermistion
     * @return \Illuminate\Http\Response
     */
    public function destroy(PagePermistion $pagePermistion)
    {
        //
    }
}
