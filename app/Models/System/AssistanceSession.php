<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssistanceSession extends Model
{
    use SoftDeletes;

    protected $table = 'system.assistance_session';
    protected $primaryKey = 'codassistance_session';

    protected $fillable = [
        'codschedule',
        'date',
        'time_opening',
        'time_ending',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedules::class,'codschedule','codschedule');
    }

}
