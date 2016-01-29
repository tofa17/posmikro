<?php

namespace App\Bases;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
	protected $table = 'tokens';
    protected $primaryKey = 'id';
	protected $fillable = ['token'];
	protected $hidden = ['created_at', 'updated_at'];
}
