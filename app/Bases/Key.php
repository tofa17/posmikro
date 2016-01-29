<?php

namespace App\Bases;

use Illuminate\Database\Eloquent\Model;

class Key extends Model
{
    protected $table = 'keys';
    protected $fillable = ['key','ignore_limits'];
    protected $hidden = ['created_at', 'updated_at'];
}
