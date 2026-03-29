<?php

namespace App\Models\Security;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Security\Permission;
use App\Models\Security\ProfilePermission;

class Profile extends Model
{
    use SoftDeletes;

    protected $table = 'security.profile';
    protected $primaryKey = 'codprofile';

    protected $fillable = [
        'name_large',
        'name_short',
        'created_at'
    ];



    public function profilePermissions()
    {
        return $this->hasMany(ProfilePermission::class, 'codprofile', 'codprofile');
    }
    // 🔹 Relación con permisos
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'security.profile_permissions', 'codprofile', 'codpermission')
            ->withTimestamps()
            ->withPivot('deleted_at');
    }
}
