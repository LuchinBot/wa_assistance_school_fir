<?php

namespace App\Models\Audit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Vetoed extends Model
{
    use SoftDeletes;

    protected $table = 'audit.vetoed';
    protected $primaryKey = 'codvetoed';

    protected $fillable = [
        'identify_number',
        'reason',
    ];

}
