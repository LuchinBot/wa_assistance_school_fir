<?php

namespace App\Models\Security;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Security\Profile;

class ProfilePermission extends Model
{
    use SoftDeletes;

    protected $table = 'security.profile_permissions';
    protected $primaryKey = 'codprofile_permission';

    protected $fillable = [
        'codprofile',
        'codpermission',
    ];

    // 🔹 Relación con perfil
    public function profile()
    {
        return $this->belongsTo(Profile::class, 'codprofile', 'codprofile');
    }

    // 🔹 Relación con permiso
    public function permission()
    {
        return $this->belongsTo(Permission::class, 'codpermission', 'codpermission');
    }
}
