<?php

namespace App\Models\System;

use App\Models\Security\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assistance extends Model
{
    use SoftDeletes;

    protected $table = 'system.assistance';
    protected $primaryKey = 'codassistance';
    protected $fillable = [
        'codassistance_session',
        'codenrollment',
        'time_entry',
        'status',
        'observation',
        'coduser_responsible'
    ];

    public function assistance_session()
    {
        return $this->belongsTo(AssistanceSession::class,'codassistance_session','codassistance_session');
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class,'codenrollment','codenrollment');
    }

    
    public function user()
    {
        return $this->belongsTo(User::class,'coduser','coduser_responsible');
    }
}
