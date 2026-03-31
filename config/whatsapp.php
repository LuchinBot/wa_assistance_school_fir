<?php
return [
    'api_url'    => env('WHATSAPP_API_URL', 'http://167.86.96.207:3000'),
    'directivos' => array_filter(explode(',', env('WHATSAPP_DIRECTIVOS', ''))),
];