<?php

namespace App\Actions;
use Illuminate\Support\Facades\Cache;


class GenerateCode
{
    public function __construct()
    {
        $code = random_int(100000, 999999);
        Cache::put('verification_code', $code, 60);
    }

}
