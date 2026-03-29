<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Person;
use App\Models\Profession;

class Period extends Model
{
    use SoftDeletes;

    protected $table = 'system.period';
    protected $primaryKey = 'codperiod';

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];
}
