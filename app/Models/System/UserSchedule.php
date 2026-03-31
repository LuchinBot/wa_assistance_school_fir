<?php

namespace App\Models\System;

use App\Models\Grade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Person;
use App\Models\Security\User;

class UserSchedule extends Model
{
    use SoftDeletes;

    protected $table      = 'system.user_schedule';
    protected $primaryKey = 'coduser_schedule';

    protected $fillable = [
        'codschedule',
        'coduser',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedules::class, 'codschedule', 'codschedule');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'coduser', 'coduser');
    }
}
