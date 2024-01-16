<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Tag;
use App\Help;
use Carbon\Carbon;

class HelpController extends Controller
{

    // lấy ra danh sách 15 thẻ tag theo A-Z
    public function sp_get_tag_list(Request $request) {
        try {
            $request_user = $request->user();
            $data = DB::table('tags')->select('tags.*')->orderby('tag_name', 'asc')->take(15)->get();
            return response()->json(['data' => $data]);
        }
        catch (\Exception $e) {
            return response()->json(['message' => $e]);
        }
    }

    // lấy ra danh sách 15 bài trợ giúp theo A-Z
    public function sp_get_help_list(Request $request) {
        try {
            $request_user = $request->user();
            $data = DB::table('helps')->orderby('title_content', 'asc')->take(15)->get();
            return response()->json(['data' => $data]);
        }
        catch (\Exception $e) {
            return response()->json(['message' => $e]);
        }
    }

    // lấy danh sách các help theo tag

    public function sp_get_help_list_from_tag_id(Request $request) {
        try {
            $request_user = $request->user();
            $data = DB::select("exec sp_get_help_in_tag_id $request->id");
            $data = $this->merge_array($data);
            return response()->json(['data' => $data]);
        }
        catch (\Exception $e) {
            return response()->json(['message' => $e]);
        }
    }

    // lấy ra các list thẻ tag

    public function sp_get_help_list_get_tag(Request $request) {
        try {
            $data = DB::table('tags')->select('id AS item_id','tag_name AS item_label')->get();
            return response()->json(['tag_array' => $data]);
        }
        catch (\Exception $e) {
            return response()->json(['message' => $e]);
        }
    }


    // lấy chi tiết help theo tag
    public function sp_get_help_list_get_detail(Request $request) {
        try {
            $id = $request->id;
            // lấy thông tin về helps
            $data = DB::table('tags')->join('help_tag','help_tag.tag_id','=','tags.id')->join('helps','help_tag.help_id','=','helps.id')->select('helps.*','tags.id AS tag_id','tags.tag_name')->where('helps.id',$id)->get()->toArray();
            $data = $this->merge_array_with_id($data);
            return response()->json(['data' => $data]);
        }
        catch (\Exception $e) {
            return response()->json(['message' => $e]);
        }
    }


    // function merge array, thuat toan dang sai
    public function merge_array($array) {
        $get_array = [];
        // duyệt tất cả phần tử trong mảng
        for($i = 0; $i <= count($array) - 1; $i++) {
            // bắt đằu từ a[0] nếu có phần tử nào trùng thì ghép
            for($j = $i + 1; $j < count($array); $j++) {
                if(isset($array[$j]) && $array[$i]->id == $array[$j]->id) {
                    $array[$i]->tag_name = $array[$i]->tag_name . ',' . $array[$j]->tag_name;
                    unset($array[$j]); // xóa phần tử trong mảng
                }
            }
            // echo '<pre>';
            // print_r($array);
            // echo '</pre>';
            // break;
            // sắp xếp lại mảng
            $array = array_values($array);
        }
        $get_array = $array;
        return $get_array;
    }


    public function merge_array_with_id($array) {
        $get_array = [];
        if(count($array) > 0) {
            // nếu có 1 phần tử trở lên
            $array[0]->tag_array = [];
            $newarray = array(
                'item_id' => $array[0]->tag_id
                , 'item_label' => $array[0]->tag_name
            );
            $array[0]->tag_array[] = $newarray;
            for($j = 1; $j < count($array); $j++) {
                if(isset($array[$j]) && $array[0]->id === $array[$j]->id) {
                    $array[0]->tag_array[] = array(
                        'item_id' => $array[$j]->tag_id
                        , 'item_label' => $array[$j]->tag_name
                    );
                    // $array[0]->tag_name = $array[0]->tag_name . ',' . $array[$j]->tag_name;
                }
            }
            $get_array[] = $array[0];
        }
        return $get_array;
    }

    public function get_update(Request $request) {
        try {
            $id = $request->id;
            // lấy thông tin về helps
            $data = DB::table('tags')->join('help_tag','help_tag.tag_id','=','tags.id')->join('helps','help_tag.help_id','=','helps.id')->select('helps.*','tags.id AS tag_id','tags.tag_name')->where('helps.id',$id)->get()->toArray();
            $data = $this->merge_array_with_id($data);
            // lấy danh sách các tag
            $tag_array = DB::table('tags')->select('id AS item_id','tag_name AS item_label')->get();
            return response()->json(['help_info' => $data, 'tag_array' => $tag_array]);
        }
        catch (\Exception $e) {
            return response()->json(['message' => $e]);
        }
    }

    public function insert(Request $request) {
        DB::beginTransaction();
        try {
            // Lưu bài viết trợ giúp
            $request_user = $request->user();
            $data = json_decode($request->data);
            $help_ob = new Help();
            $help_ob->created_by = $request_user->id;
            $help_ob->title_content = $data->title_content;
            $help_ob->help_content = $data->help_content;
            $help_ob->help_description = $data->help_description;
            $help_ob->save();
            // gắn thẻ bài viết
            if(isset($data->tag_name)) {
                $tag_array = $data->tag_name;
                foreach ($tag_array as $value) {
                    $tag_object = new Tag();
                    $tag_object->tag_name = $value->value;
                    $tag_object->save();
                    DB::table('help_tag')->insert([
                        'tag_id' => $tag_object->id
                        , 'help_id' => $help_ob->id
                    ]);
                }
            }
            // chọn theo select
            if(isset($data->tag_select)) {
                $tag_select = $data->tag_select;
                foreach ($tag_select as $value) {
                    DB::table('help_tag')->insert([
                        'tag_id' => $value->item_id
                        , 'help_id' => $help_ob->id
                    ]);
                }
            }
            DB::commit();
            return response()->json(['message' => 1]);
        }
        catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e]);
        }
    }

    public function post_update(Request $request) {
        DB::beginTransaction();
        // try {
            // Lưu bài viết trợ giúp
            $request_user = $request->user();
            $id = $request->id;
            $data = json_decode($request->data);
            $help_ob = Help::find($id);
            $help_ob->updated_by = $request_user->id;
            $help_ob->updated_at = Carbon::now()->format('Y-m-d');
            $help_ob->title_content = $data->title_content;
            $help_ob->help_content = $data->help_content;
            $help_ob->help_description = $data->help_description;
            $help_ob->save();
            // Xoa gan the cũ
            DB::table('help_tag')->where('help_tag.help_id', $help_ob->id)->delete();
            // gắn thẻ bài viết
            if(isset($data->tag_name)) {
                $tag_array = $data->tag_name;
                foreach ($tag_array as $value) {
                    $tag_object = new Tag();
                    $tag_object->tag_name = $value->value;
                    $tag_object->save();
                    DB::table('help_tag')->insert([
                        'tag_id' => $tag_object->id
                        , 'help_id' => $help_ob->id
                    ]);
                }
            }
            // chọn theo select
            if(isset($data->tag_select)) {
                $tag_select = $data->tag_select;
                foreach ($tag_select as $value) {
                    DB::table('help_tag')->insert([
                        'tag_id' => $value->item_id
                        , 'help_id' => $help_ob->id
                    ]);
                }
            }
            DB::commit();
            return response()->json(['message' => 1]);
        // }
        // catch (\Exception $e) {
        //     DB::rollback();
        //     return response()->json(['message' => $e]);
        // }
    }

    public function delete(Request $request) {
        try {
            $request_user = $request->user();
            DB::table('helps')->where('id', $request->id)->delete();
            DB::table('help_tag')->where('help_id', $request->id)->delete();
            return response()->json(['message' => 1]);
        }
        catch (\Exception $e) {
            return response()->json(['message' => $e]);
        }
    }
}
