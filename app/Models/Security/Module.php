<?php

namespace App\Models\Security;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use SoftDeletes;

    protected $table = 'security.modules';
    protected $primaryKey = 'codmodule';

    protected $fillable = [
        'codmodule_parent',
        'cod_system',
        'name_large',
        'name_short',
        'order',
        'route',
        'icon',
        'created_at'
    ];

    // 🔹 Relación con el módulo padre
    public function parent()
    {
        return $this->belongsTo(Module::class, 'codmodule_parent', 'codmodule');
    }

    // 🔹 Relación con módulos hijos
    public function children()
    {
        return $this->hasMany(Module::class, 'codmodule_parent', 'codmodule');
    }

    // 🔹 Relación con permisos
    public function permissions()
    {
        return $this->hasMany(Permission::class, 'codmodule', 'codmodule');
    }
    
}
