<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class SmtpEmail extends Model
{
	protected $table = 'send_emails';
    public $timestamps = false;
}
