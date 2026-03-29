<?php

namespace App\Models\Security;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Security\Profile;

class Permission extends Model
{
    use SoftDeletes;

    protected $table = 'security.permissions';
    protected $primaryKey = 'codpermission';

    protected $fillable = [
        'codmodule',
        'name',
        'description',
    ];

    // Mutadores
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtolower($value);
    }


    // 🔹 Pertenece a un módulo
    public function module()
    {
        return $this->belongsTo(Module::class, 'codmodule', 'codmodule');
    }

    // 🔹 Relación con perfiles
    public function profiles()
    {
        return $this->belongsToMany(Profile::class, 'security.profile_permissions', 'codpermission', 'codprofile')
            ->withTimestamps()
            ->withPivot('deleted_at');
    }
}
