<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Person;
use App\Models\Profession;

class Assignee extends Model
{
    use SoftDeletes;

    protected $table = 'system.assignee';
    protected $primaryKey = 'codassignee';

    protected $fillable = [
        'codperson',
        'relationship',
    ];

    // Relacion con la tabla person
    public function person()
    {
        return $this->belongsTo(Person::class, 'codperson', 'codperson');
    }

}
