<?php

use modules\core\components\Setting;
use modules\core\web\View;
use modules\crm\models\CustomerContactAccount;
use yii\caching\FileCache;
use yii\i18n\PhpMessageSource;
use yii\log\FileTarget;
use yii\swiftmailer\Mailer;
use yii\web\JqueryAsset;
use yii\web\User;
use yii\web\YiiAsset;

return [
    'request' => [
        'cookieValidationKey' => 'gSKY@nT9xXo1f4g31>G&Y"49z34S1p_client',
        'enableCsrfValidation' => true,
        'enableCookieValidation' => true,
        'enableCsrfCookie' => false,
    ],
    'cache' => [
        'class' => FileCache::class,
        'directoryLevel' => 3,
    ],
    'assetManager' => [
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
        ],
    ],
    'urlManager' => [
        'enablePrettyUrl' => true,
        'showScriptName' => false,
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
                'levels' => ['error', 'warning'],
            ],
        ],
    ],
    'view' => [
        'class' => View::class
    ],
    'db' => require(__DIR__ . '/db.php'),
    'session' => [
        'timeout' => 43200,
        'name' => 'eflima-v3-1-client',
    ],
    'user' => [
        'class' => User::class,
        'identityClass' => CustomerContactAccount::class,
        'loginUrl' => ['/crm/customer/customer/login'],
    ],
    'setting' => [
        'class' => Setting::class,
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
