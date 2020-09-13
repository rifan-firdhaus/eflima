<?php
require_once(__DIR__ . '/init.php');

$config = [
    'name' => 'Eflima',
    'sourceLanguage' => 'en-US',
    'language' => 'en-US',
    'id' => 'snc-eflima-v3.1',
    'vendorPath' => '@vendor',
    'basePath' => '@app',
    'params' => [
        'isAdmin' => false,
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
];

return $config;