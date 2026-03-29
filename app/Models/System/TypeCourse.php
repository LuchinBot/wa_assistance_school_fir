<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeCourse extends Model
{
    use SoftDeletes;

    protected $table = 'system.type_course';
    protected $primaryKey = 'codtype_course';

    protected $fillable = [
        'name_large',
        'name_short',
    ];

    public function courses()
    {
        return $this->hasMany(Course::class, 'codtype_course', 'codtype_course');
    }
}
