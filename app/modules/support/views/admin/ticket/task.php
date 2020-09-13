<?php

use modules\account\web\admin\View;
use modules\support\models\Ticket;
use modules\task\models\forms\task\TaskSearch;
use yii\data\ActiveDataProvider;
use yii\helpers\ReplaceArrayValue;

/**
 * @var View               $this
 * @var Ticket             $model
 * @var TaskSearch         $searchModel
 * @var ActiveDataProvider $dataProvider
 */

$this->subTitle = Yii::t('app', 'Tasks');

$this->beginContent('@modules/support/views/admin/ticket/components/view-layout.php', [
    'model' => $model,
    'active' => 'task',
]);

echo $this->block('@begin');

echo $this->render('@modules/task/views/admin/task/components/data-view', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
    'dataViewOptions' => [
        'searchAction' => new ReplaceArrayValue($searchModel->searchUrl('/support/admin/ticket/view', [
            'id' => $model->id,
            'action' => 'task',
        ], false)),
    ],
]);

echo $this->block('@end');

$this->endContent();