<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Security\Permission;
use App\Models\Security\ProfilePermission;

class Level extends Model
{
    use SoftDeletes;

    protected $table = 'main.level';
    protected $primaryKey = 'codlevel';

    protected $fillable = [
        'name_large',
        'name_short',
    ];

    protected $hidden       = ['created_at', 'updated_at'];
}
