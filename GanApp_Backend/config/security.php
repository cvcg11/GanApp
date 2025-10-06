<?php

return [
    // Pepper actual (OBLIGATORIO en prod)
    'password_pepper_current' => env('PASSWORD_PEPPER', null),

    // Pepper previo (opcional) para rotaciones sin romper logins
    'password_pepper_old' => env('PASSWORD_PEPPER_OLD', null),

    // Algoritmo HMAC para el pepper
    'password_pepper_algo' => env('PASSWORD_PEPPER_ALGO', 'sha256'),
];
