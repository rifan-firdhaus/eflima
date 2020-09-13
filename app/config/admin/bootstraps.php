<?php
$bootstraps = [
    'setting',
    'core',
    'ui',
    'account',
    'file_manager',
    'quick_access',
    'note',
    'task',
    'address',
    'crm',
    'finance',
    'support',
    'project',
    'calendar',
];

if (YII_ENV_DEV) {
    $bootstraps[] = 'debug';
    $bootstraps[] = 'gii';
}


return $bootstraps;