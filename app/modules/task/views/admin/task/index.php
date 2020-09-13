<?php

use modules\account\web\admin\View;
use modules\core\components\SettingRenderer;
use modules\task\models\forms\task\TaskSearch;

/**
 * @var View            $this
 * @var TaskSearch      $searchModel
 */

$this->title = Yii::t('app', 'Task');
$this->subTitle = Yii::t('app', 'List');
$this->menu->active = "main/task";

echo $this->block('@begin');

$this->beginContent('@modules/task/views/admin/task/components/index-layout.php');
echo $this->render('components/data-view', compact('searchModel'));
$this->endContent();

echo $this->block('@end');
