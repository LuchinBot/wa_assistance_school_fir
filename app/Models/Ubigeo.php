<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Security\Permission;
use App\Models\Security\ProfilePermission;

class Ubigeo extends Model
{
    use SoftDeletes;

    protected $table = 'main.ubigeo';
    protected $primaryKey = 'codubigeo';

    protected $fillable = [
        'coddepartment',
        'codprovince',
        'coddistrict',
        'codccpp',
        'name'
    ];

    protected $hidden       = ['created_at', 'updated_at'];
}
