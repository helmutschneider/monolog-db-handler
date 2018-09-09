<?php
/**
 * Created by PhpStorm.
 * User: johan
 * Date: 2018-09-09
 * Time: 16:18
 */

return [

    'mysql' => [
        'dsn' => 'mysql:host=127.0.0.1;dbname=logger;charset=utf8mb4',
        'username' => '',
        'password' => '',
        'schema' => __DIR__ . '/../schema/mysql.sql',
    ],

    'sqlite' => [
        'dsn' => 'sqlite::memory:',
        'username' => '',
        'password' => '',
        'schema' => __DIR__ . '/../schema/sqlite.sql',
    ],

];
