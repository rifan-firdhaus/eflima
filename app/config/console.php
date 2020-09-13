<?php

use modules\account\Account;
use modules\core\components\Setting;
use modules\core\Core;
use yii\log\FileTarget;
use modules\account\rbac\DbManager;

$config = [
    'name' => 'Eflima',
    'id' => 'snc-eflima-v3.1',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
        '@tests' => '@app/tests',
        '@modules' => '@app/modules',
        '@webroot' => dirname(dirname(__DIR__)),
    ],
    'modules' => [
        'core' => Core::class,
        'account' => Account::class,
    ],
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationPath' => null, // disable non-namespaced migrations if app\migrations is listed below
            'migrationNamespaces' => [
                'modules\core\migrations',
                'modules\ui\migrations',
                'modules\address\migrations',
                'modules\account\migrations',
                'modules\crm\migrations',
                'modules\task\migrations',
                'modules\finance\migrations',
                'modules\project\migrations',
                'modules\support\migrations',
                'modules\note\migrations',
                'modules\calendar\migrations',
                'modules\file_manager\migrations',
            ],
        ],
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'setting' => [
            'class' => Setting::class,
        ],
        'authManager' => [
            'class' => DbManager::class,
            'itemTable' => "{{%account_auth_item}}",
            'itemChildTable' => "{{%account_auth_item_child}}",
            'assignmentTable' => "{{%account_auth_assignment}}",
            'ruleTable' => "{{%account_auth_rule}}",
        ],
        'db' => require_once 'db.php',
    ],
];
return $config;
