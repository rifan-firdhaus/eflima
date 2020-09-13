<?php

use yii\db\Connection;

return [
    'class' => Connection::class,
    'enableQueryCache' => true,
    'enableSchemaCache' => true,
    'schemaCacheDuration' => 3600 * 24 * 30,
    'dsn' => 'mysql:host=localhost;dbname=snc_eflima_v3.1;port=3307',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
];
