<?php

use modules\account\web\admin\View;
use modules\task\models\forms\task_timer\TaskTimerSearch;
use modules\task\models\Task;
use yii\data\ActiveDataProvider;

/**
 * @var View               $this
 * @var Task               $model
 * @var TaskTimerSearch    $timerSearchModel
 */

$active = 'timer'; // Set timer tab to active

$this->subTitle = Yii::t('app', 'Timer');

$this->beginContent('@modules/task/views/admin/task/components/view-layout.php', compact('model', 'active'));
echo $this->render('/admin/task-timer/components/data-view', [
    'searchModel' => $timerSearchModel,
]);

$this->endContent();
