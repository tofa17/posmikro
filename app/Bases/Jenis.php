<?php

namespace App\Bases;

use Illuminate\Database\Eloquent\Model;

class Jenis extends Model
{
	protected $table = 'jenises';
    protected $primaryKey = 'id';
	protected $fillable = ['id', 'jenisusaha'];
	protected $hidden = ['created_at', 'updated_at'];
}
