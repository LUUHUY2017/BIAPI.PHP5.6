<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    //
    protected $table = 'notification';

    private $message;

    public function _setMessage(string $msg) {
    	$this->message = $msg;
    	return true;
    }
}
