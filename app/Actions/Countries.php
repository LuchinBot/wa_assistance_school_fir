<?php

namespace App\Actions;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;


class countries
{
    public $countries;

    public function __construct()
    {
        try {
            $response = Http::get('https://country.io/names.json');

            if ($response->successful()) {
                $this->countries = $response->json();
            } else {
                $this->countries = [];
            }
        } catch (\Exception $e) {
            $this->countries = [];
        }
    }

    public function getCountries(){
        return $this->countries;
    }

}
