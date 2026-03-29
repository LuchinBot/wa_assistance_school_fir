<?php

namespace App\Models\System;

use App\Models\Security\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Justification extends Model
{
    use SoftDeletes;

    protected $table = 'system.justification';
    protected $primaryKey = 'codjustification';
    protected $fillable = [
        'codjustification',
        'codenrollment',
        'codassistance_session', // nullable, por que no todos tendrán
        'coduser_responsible',
        'type', // JT= Justificacion temporal (Esto solo de acuerdo a la session que defina el usuario) / JI= Justificación Indefinida (esto debe permitir registrar siempre cualquier asistencia como justificado)
        'reason',
    ];

    public function assistance_session()
    {
        return $this->belongsTo(AssistanceSession::class, 'codassistance_session', 'codassistance_session');
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class, 'codenrollment', 'codenrollment');
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'coduser_responsible', 'coduser');
    }
}
