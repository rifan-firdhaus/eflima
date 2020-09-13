<?php

use modules\account\web\admin\View;
use modules\calendar\models\forms\event\EventSearch;
use modules\project\models\Project;
use yii\data\ActiveDataProvider;
use yii\helpers\ReplaceArrayValue;

/**
 * @var View               $this
 * @var Project            $model
 * @var EventSearch        $searchModel
 */

$this->subTitle = Yii::t('app', 'Task');

$this->beginContent('@modules/project/views/admin/project/components/view-layout.php', [
    'model' => $model,
    'active' => 'event',
]);

echo $this->block('@begin');

echo $this->render('@modules/calendar/views/admin/event/components/data-view', [
    'searchModel' => $searchModel,
    'dataViewOptions' => [
        'searchAction' => new ReplaceArrayValue($searchModel->searchUrl('/project/admin/project/view', [
            'id' => $model->id,
            'action' => 'event',
        ], false)),
    ],
]);

echo $this->block('@end');

$this->endContent();
