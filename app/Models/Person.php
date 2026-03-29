<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use App\Models\Security\User;


class Person extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table        = "main.person";
    protected $primaryKey   = "codperson";
    protected $fillable     =
    [
        'codtd_identify',
        'codubigeo',
        'codgender',
        'codcivil_status',
        'identify_number',
        'identify_emission',
        'firstname',
        'lastname_father',
        'lastname_mom',
        'email',
        'birthday',
        'phone',
        'address',
        'department',
        'province',
        'district',
        'nationality',
        'photo',
        'firma',
    ];
    protected $hidden       = ['created_at', 'updated_at'];
    protected $appends = ['photo_url', 'firma_url'];

    public function users()
    {
        return $this->hasMany(User::class, 'codperson', 'codperson');
    }

    public function getPhotoUrlAttribute()
    {
        if (!$this->photo) {

            return match ((int) $this->codgender) {
                1 => asset('img/man_student.png'),
                2 => asset('img/woman_student.png'),
                default => asset('img/person.jpg')
            };
        }

        return config('app.files_url') . '/storage/' . $this->photo;
    }
    public function getFirmaUrlAttribute()
    {
        if (!$this->firma) {
            return null;
        }

        return config('app.files_url') . '/storage/' . $this->firma;
    }
}
