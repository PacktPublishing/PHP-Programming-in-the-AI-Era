<?php
return [
    'dsn'     => 'mysql:host=localhost;dbname=your_database;charset=utf8mb4',
    'db_usr'  => 'your_username',
    'db_pwd'  => 'your_password',
    'db_opts' => [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
];
