<?php
// app/Models/System/GradeSchedule.php

namespace App\Models\System;

use App\Models\Grade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GradeSchedule extends Model
{
    use SoftDeletes;

    protected $table      = 'system.grade_schedule';
    protected $primaryKey = 'codgrade_schedule';

    protected $fillable = [
        'codgrade',
        'codschedule',
        'section', 
    ];

    public function grade()
    {
        return $this->belongsTo(Grade::class, 'codgrade', 'codgrade');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedules::class, 'codschedule', 'codschedule');
    }
}
