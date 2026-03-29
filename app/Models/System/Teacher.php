<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Person;
use App\Models\Profession;

class Teacher extends Model
{
    use SoftDeletes;

    protected $table = 'system.teacher';
    protected $primaryKey = 'codteacher';

    protected $fillable = [
        'codperson',
        'codprofession',
    ];

    // Relacion con la tabla person
    public function person()
    {
        return $this->belongsTo(Person::class, 'codperson', 'codperson');
    }

    public function profession()
    {
        return $this->belongsTo(Profession::class, 'codprofession', 'codprofession');
    }

}
