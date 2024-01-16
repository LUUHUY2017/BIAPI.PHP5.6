<?php

namespace App\Http\Controllers\Admin;

use App\RolePagePermistion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class RolePagePermistionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request_user = $request->user();
        $role_id = $request->role_id;
        
        $pp = DB::table('role_page_permission')
            ->join('page_permission', 'page_permission.id', '=', 'role_page_permission.page_permission_id')
            ->join('pages', 'pages.id', '=', 'page_permission.page_id')
            ->join('permissions', 'permissions.id', '=', 'page_permission.permission_id')
            ->where('role_id', '=', $role_id)
            ->select('role_page_permission.id', 'page_id', 'page_name', 'page_detail', 'permission_id', 'permission_name', 'permission_detail')
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
        $request_user = $request->user();
        
        foreach ($request->page_permission_ids as $value) {
            $org = new RolePagePermistion;
            $org->created_by = $request_user->id;
            $org->organization_id = $request->organization_id;
            $org->role_id = $request->role_id;
            $org->page_permission_id = $value;


            $org->actived = true;//$request->actived;
            $org->save();
        }
        return response()->json(array('OK'=>'OK'));        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\RolePagePermistion  $rolePagePermistion
     * @return \Illuminate\Http\Response
     */
    public function show(RolePagePermistion $rolePagePermistion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\RolePagePermistion  $rolePagePermistion
     * @return \Illuminate\Http\Response
     */
    public function edit(RolePagePermistion $rolePagePermistion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\RolePagePermistion  $rolePagePermistion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RolePagePermistion $rolePagePermistion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\RolePagePermistion  $rolePagePermistion
     * @return \Illuminate\Http\Response
     */
    public function destroy(RolePagePermistion $rolePagePermistion)
    {
        
    }
    public function delete(Request $request)
    {
        try{
            RolePagePermistion::where('id', $request->id)->delete();
            return response()->json(array('OK'=>'OK'));
        }
        catch(\Exception $exception){
            return $exception;
        }
    }
    //lay danh sach users de add moi cho role
    public function get_pagepermistion(Request $request)
    {
        $request_user = $request->user();
        $role_id = $request->role_id;        
        
        $rpp = DB::table('role_page_permission')
        ->where('role_id', '=', $role_id)
        ->pluck('page_permission_id');
        // return response()->json($rpp);
        $pp = DB::table('page_permission')
            ->join('pages', 'pages.id', '=', 'page_permission.page_id')
            ->join('permissions', 'permissions.id', '=', 'page_permission.permission_id')
            ->select('page_permission.id', 'page_id', 'page_name', 'page_detail', 'permission_id', 'permission_name', 'permission_detail')
            ->whereNotIn('page_permission.id', $rpp)
            ->get();
        return response()->json($pp);
    }

}
