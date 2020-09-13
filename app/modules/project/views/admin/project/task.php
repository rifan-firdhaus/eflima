<?php

use modules\account\web\admin\View;
use modules\project\models\Project;
use modules\task\models\forms\task\TaskSearch;
use yii\data\ActiveDataProvider;
use yii\helpers\ReplaceArrayValue;

/**
 * @var View               $this
 * @var Project            $model
 * @var TaskSearch         $searchModel
 * @var ActiveDataProvider $dataProvider
 */

$this->subTitle = Yii::t('app', 'Task');

$this->beginContent('@modules/project/views/admin/project/components/view-layout.php', [
    'model' => $model,
    'active' => 'task',
]);

echo $this->block('@begin');

echo $this->render('@modules/task/views/admin/task/components/data-view', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
    'dataViewOptions' => [
        'searchAction' => new ReplaceArrayValue($searchModel->searchUrl('/project/admin/project/view', [
            'id' => $model->id,
            'action' => 'task',
        ], false)),
    ],
]);

echo $this->block('@end');

$this->endContent();
