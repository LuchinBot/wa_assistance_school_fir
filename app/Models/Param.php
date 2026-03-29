<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Param extends Model
{
    use SoftDeletes;

    protected $table = 'main.param';
    protected $primaryKey = 'codparam';

    protected $fillable = [
        'param',
        'value',
        'description'
    ];
}
