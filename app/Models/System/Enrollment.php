<?php
// app/Models/System/GradeSchedule.php

namespace App\Models\System;

use App\Models\Grade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enrollment extends Model
{
    use SoftDeletes;

    protected $table      = 'system.enrollment';
    protected $primaryKey = 'codenrollment';

    protected $fillable = [
        'codstudent',
        'codgrade_schedule',
        'codperiod',
        'status',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'codstudent', 'codstudent');
    }

    public function grade_schedule()
    {
        return $this->belongsTo(GradeSchedule::class, 'codgrade_schedule', 'codgrade_schedule');
    }

    public function period()
    {
        return $this->belongsTo(Period::class, 'codperiod', 'codperiod');
    }

    public function assistances()
    {
        return $this->hasMany(Assistance::class, 'codenrollment', 'codenrollment');
    }
}
