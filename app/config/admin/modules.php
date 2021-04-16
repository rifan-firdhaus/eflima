<?php

use modules\account\Account;
use modules\address\Address;
use modules\calendar\Calendar;
use modules\core\Core;
use modules\crm\CRM;
use modules\file_manager\FileManager;
use modules\finance\Finance;
use modules\note\Note;
use modules\project\Project;
use modules\quick_access\QuickAccess;
use modules\support\Support;
use modules\task\Task;
use modules\ui\UI;

$modules = [
    'core' => Core::class,
    'ui' => UI::class,
    'account' => Account::class,
    'file_manager' => FileManager::class,
    'quick_access' => QuickAccess::class,
    'note' => Note::class,
    'task' => Task::class,
    'crm' => CRM::class,
    'address' => Address::class,
    'finance' => Finance::class,
    'support' => Support::class,
    'project' => Project::class,
    'calendar' => Calendar::class,
];

if (YII_ENV_DEV) {
    $modules['debug'] = [
        'class' => 'yii\debug\Module',
        'traceLine' => '<a href="phpstorm://open?url={file}&line={line}">{file}:{line}</a>',
    ];
    $modules['gii'] = [
        'class' => 'yii\gii\Module',
        'generators' => [
            'model' => [
                'class' => 'yii\gii\generators\model\Generator',
                'templates' => [
                    'Eflima Model' => '@modules/core/gii-templates',
                ],
            ],
        ],
    ];
}

return $modules;
