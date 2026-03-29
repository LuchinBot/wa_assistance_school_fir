<?php
// app/Models/System/Schedules.php

namespace App\Models\System;

use App\Models\Grade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedules extends Model
{
    use SoftDeletes;

    protected $table      = 'system.schedules';
    protected $primaryKey = 'codschedule';

    protected $fillable = [
        'turn',
        'time_start',
        'time_end',
    ];

    // Grados asignados a este horario
    public function grades()
    {
        return $this->belongsToMany(
            Grade::class,
            'system.grade_schedule',
            'codschedule',
            'codgrade',
            'codschedule',
            'codgrade'
        )->withTimestamps()->wherePivotNull('deleted_at');
    }
}