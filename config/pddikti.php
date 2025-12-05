<?php

return [
    'url' => env('PDDIKTI_URL', 'http://localhost:3003/ws/sandbox2.php'),
    'username' => env('PDDIKTI_USERNAME'),
    'password' => env('PDDIKTI_PASSWORD'),
    'ttl' => env('PDDIKTI_TOKEN_TTL', 3000), // misal ±50 menit
];
