<?php
// app/Models/Grade.php

namespace App\Models;

use App\Models\System\Student;
use App\Models\System\GradeSchedule;
use App\Models\System\Schedules;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grade extends Model
{
    use SoftDeletes;

    protected $table      = 'main.grade';
    protected $primaryKey = 'codgrade';

    protected $fillable = [
        'codlevel',
        'name_large',
        'name_short',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function level()
    {
        return $this->belongsTo(Level::class, 'codlevel', 'codlevel');
    }

    // Relación con horarios via pivote
    public function schedules()
    {
        return $this->belongsToMany(
            Schedules::class,
            'system.grade_schedule',
            'codgrade',
            'codschedule',
            'codgrade',
            'codschedule'
        )->withTimestamps()->wherePivotNull('deleted_at');
    }

    // Estudiantes del grado
    // app/Models/Grade.php
    public function students()
    {
        return $this->hasManyThrough(
            Student::class,
            GradeSchedule::class,
            'codgrade',          // FK en grade_schedule → grade
            'codgrade_schedule', // FK en student → grade_schedule
            'codgrade',          // PK en grade
            'codgrade_schedule'  // PK en grade_schedule
        );
    }
}
