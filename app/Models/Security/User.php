<?php

namespace App\Models\Security;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\{HasManyThrough, BelongsTo};
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;

use App\Models\Security\{ProfilePermission, Permission, Profile};
use App\Models\Person;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $table      = 'security.user';
    protected $primaryKey = 'coduser';

    protected $fillable = [
        'codprofile',
        'codperson',
        'username',
        'password',
        'is_active',
        'is_super',
        'must_change_password',
        'login_attempts',
        'locked_until',
        'last_login_at',
        'last_login_ip',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'deleted_at',
    ];

    protected $casts = [
        'must_change_password' => 'boolean',
        'locked_until'         => 'datetime',
        'last_login_at'        => 'datetime',
    ];

    /* ── MUTATORS ── */

    public function setPasswordAttribute($value): void
    {
        if (!empty($value)) {
            if (Hash::needsRehash($value)) {
                $this->attributes['password'] = Hash::make($value);
            } else {
                $this->attributes['password'] = $value;
            }
        }
    }

    /* ── RELACIONES ── */

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'codprofile', 'codprofile');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'codperson', 'codperson');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'coduser');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'coduser');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by', 'coduser');
    }

    public function permissions(): HasManyThrough
    {
        return $this->hasManyThrough(
            Permission::class,
            ProfilePermission::class,
            'codprofile',
            'codpermission',
            'codprofile',
            'codpermission'
        );
    }
}
