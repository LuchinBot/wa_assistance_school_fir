<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Security\Permission;
use App\Models\Security\ProfilePermission;

class TypeDocumentIdentify extends Model
{
    use SoftDeletes;

    protected $table = 'main.td_identify';
    protected $primaryKey = 'codtd_identify';

    protected $fillable = [
        'codsunat',
        'name_large',
        'name_short',
        'length',
    ];

    protected $hidden       = ['created_at', 'updated_at'];
}
