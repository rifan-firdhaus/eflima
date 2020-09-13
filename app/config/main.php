<?php
$config = require(__DIR__ . '/app.php');

$config['components'] = require(__DIR__ . '/components.php');
$config['bootstrap'] = require(__DIR__ . '/bootstraps.php');
$config['modules'] = require(__DIR__ . '/modules.php');

return $config;