<?php

use modules\account\web\admin\View;
use modules\project\models\Project;
use modules\support\models\forms\ticket\TicketSearch;
use yii\data\ActiveDataProvider;
use yii\helpers\ReplaceArrayValue;

/**
 * @var View               $this
 * @var Project            $model
 * @var TicketSearch       $searchModel
 */

$this->subTitle = Yii::t('app', 'Invoice');

$this->beginContent('@modules/project/views/admin/project/components/view-layout.php', [
    'model' => $model,
    'active' => 'ticket',
]);

echo $this->block('@begin');

echo $this->render('@modules/support/views/admin/ticket/components/data-view', [
    'searchModel' => $searchModel,
    'dataViewOptions' => [
        'searchAction' => new ReplaceArrayValue($searchModel->searchUrl('/project/admin/project/view', [
            'id' => $model->id,
            'action' => 'ticket',
        ], false)),
    ],
]);

echo $this->block('@end');

$this->endContent();
