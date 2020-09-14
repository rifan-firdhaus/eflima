<?php

use modules\account\web\admin\View;
use modules\crm\models\Lead;
use modules\task\models\forms\task\TaskSearch;
use yii\helpers\ReplaceArrayValue;

/**
 * @var Lead       $model
 * @var TaskSearch $taskSearchModel
 * @var View       $this
 */

$active = 'task';
$this->subTitle = Yii::t('app', 'Task');

$this->beginContent('@modules/crm/views/admin/lead/components/view-layout.php', compact('model', 'active'));
echo $this->block('@begin');

echo $this->render('@modules/task/views/admin/task/components/data-view', [
    'searchModel' => $taskSearchModel,
    'dataViewOptions' => [
        'searchAction' => new ReplaceArrayValue($taskSearchModel->searchUrl('/crm/admin/lead/view', [
            'id' => $model->id,
            'action' => 'task',
        ], false)),
    ],
]);

echo $this->block('@end');
$this->endContent();
