<?php
namespace App\Http\Controllers;
use Exception;
use ErrorException;
use InvalidArgumentException;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
class ErrorHandleController extends Controller
{
    public static function get_error_message(Exception $e) {
        $default_error = 'Ðã có lỗi xảy ra ';
        $syntax_error = 'Lỗi cú pháp hoặc dữ liệu tại dòng ';
        $suffic = ' tại biapi';
        if($e instanceof QueryException) {
            // return $e->getMessage();
            return 'Lỗi truy vấn cơ sở dữ liệu với SQL Statement là: '. $e->getMessage();
        }
        if($e instanceof ValidationException) {
            return 'Lỗi kiểu dữ liệu không phù hợp tại dòng '. $e->getLine() . ' trong file ' . $e->getFile() .$suffic;
        }
        if($e instanceof InvalidArgumentException) {
            return 'Lỗi tại '. $e->getMessage() . ' trong file ' . $e->getFile();
        }
        if($e instanceof ErrorException) {
            return 'Lỗi tại dòng '. $e->getLine() . ' trong file ' . $e->getFile() . ' ' . $e->getMessage();
        }
        // nếu là lỗi từ mình throw ra
        if($e->getCode() === 296) {
            return $e->getMessage();
        }
        return $default_error . $suffic;
        // catch (Error $er) {
        //     return response()->json($er->getMessage());
        // }
    }
}
