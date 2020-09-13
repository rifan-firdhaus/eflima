<?php

use modules\account\web\admin\View;
use modules\project\models\Project;
use modules\task\models\forms\task_timer\TaskTimerSearch;
use yii\data\ActiveDataProvider;
use yii\helpers\ReplaceArrayValue;

/**
 * @var View               $this
 * @var Project            $model
 * @var TaskTimerSearch    $searchModel
 * @var ActiveDataProvider $dataProvider
 */

$this->subTitle = Yii::t('app', 'Timesheet');

$this->beginContent('@modules/project/views/admin/project/components/view-layout.php', [
    'model' => $model,
    'active' => 'task-timer',
]);

echo $this->block('@begin');

echo $this->render('@modules/task/views/admin/task-timer/components/data-view', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
    'dataViewOptions' => [
        'searchAction' => new ReplaceArrayValue($searchModel->searchUrl('/project/admin/project/view', [
            'id' => $model->id,
            'action' => 'task-timer',
        ], false)),
    ],
]);

echo $this->block('@end');

$this->endContent();
