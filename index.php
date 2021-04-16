<?php
// comment out the following two lines when deployed to production
 defined('YII_DEBUG') or define('YII_DEBUG', true);
 defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/app/vendor/autoload.php';
require __DIR__ . '/app/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/app/config/main.php';

(new app\modules\core\web\Application($config))->run();
