<?php

return [

    // Algoritmo por defecto (puedes sobreescribirlo con HASHING_DRIVER en .env)
    'driver' => env('HASHING_DRIVER', 'argon2id'),

    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 12),
    ],

    'argon' => [
        // Ajusta según tu servidor (64–128 MB, 2–4 threads, 3–6 time)
        'memory'  => env('ARGON_MEMORY', 65536), // 64 MB
        'threads' => env('ARGON_THREADS', 2),
        'time'    => env('ARGON_TIME', 4),
    ],

];
