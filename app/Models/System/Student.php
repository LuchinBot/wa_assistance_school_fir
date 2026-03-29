<?php

namespace App\Models\System;

use App\Models\Grade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Person;

class Student extends Model
{
    use SoftDeletes;

    protected $table      = 'system.student';
    protected $primaryKey = 'codstudent';

    protected $fillable = [
        'codperson',
        'codassignee',
        'carnet',
    ];

    protected $appends = ['carnet_url'];

    // Persona
    public function person()
    {
        return $this->belongsTo(Person::class, 'codperson', 'codperson');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'codstudent', 'codstudent');
    }

    public function currentEnrollment()
    {
        return $this->hasOne(Enrollment::class, 'codstudent', 'codstudent')
            ->whereHas('period', function ($q) {
                $q->where('is_active', 'Y');
            });
    }

    // Apoderado
    public function assignee()
    {
        return $this->belongsTo(Assignee::class, 'codassignee', 'codassignee');
    }

    public function getCarnetUrlAttribute()
    {
        if (!$this->carnet) {
            return asset('img/carnet.jpg');
        }

        return config('app.files_url') . '/storage/' . $this->carnet;
    }
}
