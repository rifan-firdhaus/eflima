<?php

use modules\account\models\AdministratorAccount;
use modules\account\models\StaffAccount;
use modules\account\web\admin\View;
use modules\core\components\Formatter;
use modules\core\components\Setting;
use yii\bootstrap4\BootstrapAsset;
use yii\caching\FileCache;
use yii\i18n\PhpMessageSource;
use yii\log\FileTarget;
use modules\account\rbac\DbManager;
use yii\swiftmailer\Mailer;
use yii\web\AssetManager;
use yii\web\JqueryAsset;
use yii\web\UrlManager;
use yii\web\User;
use yii\web\YiiAsset;

return [
    'request' => [
        'cookieValidationKey' => 'gSKY@nT9xXo1f4g31>G&Y"49z34S1p_new',
        'enableCsrfValidation' => true,
        'enableCookieValidation' => true,
        'enableCsrfCookie' => false
    ],
    'cache' => [
        'class' => FileCache::class,
        'directoryLevel' => 3,
    ],
    'formatter' => [
        'class' => Formatter::class,
        'nullDisplay' => ''
    ],
    'assetManager' => [
        'class' => AssetManager::class,
        'forceCopy' => isset($_GET['c']) ? true : false,
        'linkAssets' => true,
        'bundles' => [
            JqueryAsset::class => [
                'js' => [
                    YII_ENV_DEV ? 'jquery.js' : 'jquery.min.js',
                ],
            ],
            YiiAsset::class => [
                'sourcePath' => '@modules/core/assets/source',
                'js' => [
                    'js/yii.js',
                ],
            ],
            BootstrapAsset::class => [
                'css' => []
            ]
        ],
    ],
    'urlManager' => [
        'class' => UrlManager::class,
        'enablePrettyUrl' => true,
        'showScriptName' => true
    ],
    'mailer' => [
        'class' => Mailer::class,
        'useFileTransport' => true,
    ],
    'log' => [
        'traceLevel' => YII_DEBUG ? 3 : 0,
        'targets' => [
            [
                'class' => FileTarget::class,
                'levels' => ['error', 'warning']
            ],
        ],
    ],
    'db' => require(__DIR__ . '/../db.php'),
    'session' => [
        'timeout' => 43200,
        'name' => 'eflima-3-1',
    ],
    'setting' => [
        'class' => Setting::class,
    ],
    'view' => [
        'class' => View::class
    ],
    'user' => [
        'class' => User::class,
        'identityClass' => StaffAccount::class,
        'loginUrl' => ['/account/admin/staff/login']
    ],
    'authManager' => [
        'class' => DbManager::class,
        'itemTable' => "{{%account_auth_item}}",
        'itemChildTable' => "{{%account_auth_item_child}}",
        'assignmentTable' => "{{%account_auth_assignment}}",
        'ruleTable' => "{{%account_auth_rule}}",
    ],
    'i18n' => [
        'translations' => [
            'app*' => [
                'class' => PhpMessageSource::class,
                'basePath' => '@app/translations',
                'sourceLanguage' => 'en-US',
                'fileMap' => [
                    'app' => 'app.php',
                ],
            ],
        ],
    ],
];