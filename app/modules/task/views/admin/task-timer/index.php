<?php

use modules\account\web\admin\View;
use modules\task\models\forms\task_timer\TaskTimerSearch;

/**
 * @var View            $this
 * @var TaskTimerSearch $searchModel
 */


$active = 'timer';
$this->title = Yii::t('app', 'Task');
$this->menu->active = "main/task";
$this->subTitle = Yii::t('app', 'Timesheet');

echo $this->block('@begin');
$this->beginContent('@modules/task/views/admin/task/components/index-layout.php', compact('active'));

echo $this->render('components/data-view', compact('searchModel'));

$this->endContent();
echo $this->block('@end');
