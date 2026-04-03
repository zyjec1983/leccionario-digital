<?php

return [
    'environment' => 'development',
    'base_path' => '/leccionario-digital/public',
    
    'database' => [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'leccionario_digital',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => ''
    ],

    'session' => [
        'name' => 'leccionario_session',
        'lifetime' => 900,
        'secure' => false,
        'httponly' => true
    ],

    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => '',
        'password' => '',
        'from_email' => 'noreply@leccionario.local',
        'from_name' => 'Leccionario Digital'
    ],

    'app' => [
        'name' => 'Leccionario Digital',
        'url' => 'http://localhost/leccionario-digital',
        'timezone' => 'America/Guayaquil',
        'debug' => true
    ],

    'pagination' => [
        'per_page' => 15,
        'num_links' => 5
    ]
];
