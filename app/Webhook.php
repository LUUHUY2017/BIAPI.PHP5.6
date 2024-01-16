<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    protected $table = 'web_hooks';

    public $timestamps = false;
}
