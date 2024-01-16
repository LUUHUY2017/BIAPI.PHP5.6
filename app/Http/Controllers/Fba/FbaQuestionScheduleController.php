<?php

namespace App\Http\Controllers\Fba;

use App\FbaQuestionSchedule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FbaQuestionScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\FbaQuestionSchedule  $fbaQuestionSchedule
     * @return \Illuminate\Http\Response
     */
    public function show(FbaQuestionSchedule $fbaQuestionSchedule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\FbaQuestionSchedule  $fbaQuestionSchedule
     * @return \Illuminate\Http\Response
     */
    public function edit(FbaQuestionSchedule $fbaQuestionSchedule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\FbaQuestionSchedule  $fbaQuestionSchedule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FbaQuestionSchedule $fbaQuestionSchedule)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\FbaQuestionSchedule  $fbaQuestionSchedule
     * @return \Illuminate\Http\Response
     */
    public function destroy(FbaQuestionSchedule $fbaQuestionSchedule)
    {
        //
    }

    public function get_schedules(Request $request)
    {
        $request_user = $request->user();
        
        $user_id = 0;
        $organization_id = 0;
        // get question default
        if($request_user != null && $request_user->lever>0){
            $user_id = $request_user->id;
            $organization_id  = intval($request_user->organization_id);
        }
        $question_schedules = DB::select("exec sp_fba_get_schedules,  $user_id,  $question_id");
        return response()->json($question_schedules);
    }
}
