<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classroom extends Model
{
    use SoftDeletes;

    protected $table = 'system.classroom';
    protected $primaryKey = 'codclassroom';

    protected $fillable = [
        'name_large',
        'name_short',
    ];
}
