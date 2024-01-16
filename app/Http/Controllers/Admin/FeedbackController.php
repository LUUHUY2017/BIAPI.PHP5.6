<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

class FeedbackController extends Controller
{
    public function sp_get_feedback_list(Request $request) {
        try {
            $request_user = $request->user();
            $deleted = 0;
            $data = DB::select("exec sp_get_feedback_list $deleted");
            return response()->json(['data' => $data]);
        }
        catch (\Exception $e) {
            return response()->json(['message' => $e]);
        }
    }

    public function insert(Request $request) {
        DB::beginTransaction();
        try {
            $request_user = $request->user();
            $data = json_decode($request->data);
            $record = [
                'created_by' => $request_user->id
                , 'title' => $data->title
                , 'feedback_content' => $data->feedback_content
            ];
            if ($request->hasFile('img_source')) {
                $file = $request->file('img_source');
                $extension = $file->getClientOriginalName();
                $img_source_name = time() . '.' . $extension;
                $path = public_path() . '/images/feedbacks';
                $upload = $file->move($path, $img_source_name);
                $record['img_source'] = $img_source_name;
            }
            DB::table('feedbacks')->insert($record);
            DB::commit();
            return response()->json(['message' => 1]);
        }
        catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e]);
        }
    }

    public function delete(Request $request) {
        // $msg = 0;
        try {
            $request_user = $request->user();
            DB::table('feedbacks')->where('id', $request->id)->update([
                'deleted' => 1
            ]);
            return response()->json(['message' => 1]);
        }
        catch (\Exception $e) {
            return response()->json(['message' => $e]);
        }
        // if($action == 1)
        // {
        //     if(File::exists($company_logo))
        //     {
        //         File::delete($company_logo);
        //     }
        //     if(File::exists($application_logo))
        //     {
        //         File::delete($application_logo);
        //     }
        // }
    }
}
