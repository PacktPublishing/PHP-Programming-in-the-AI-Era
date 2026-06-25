<?php
$config = [
    'dsn'  => 'mysql:host=mysql.local:3306;dbname=php8cookbook',
    'db_usr'     => 'cookbook',
    'db_pwd'     => 'password',
    'db_host'    => 'mysql.local',
    'db_opts'    => [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
];
return new PDO($config['dsn'], $config['db_usr'], $config['db_pwd'], ($config['db_opts'] ?? null));
