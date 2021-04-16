<?php

use modules\account\web\admin\View;
use modules\crm\models\Customer;
use modules\task\models\forms\task\TaskSearch;
use yii\data\ActiveDataProvider;
use yii\helpers\ReplaceArrayValue;

/**
 * @var Customer   $model
 * @var TaskSearch $taskSearchModel
 * @var View       $this
 */

$active = 'task';
$this->subTitle = Yii::t('app', 'Task');

$this->beginContent('@modules/crm/views/admin/customer/components/view-layout.php', compact('model', 'active'));
echo $this->block('@begin');

echo $this->render('@modules/task/views/admin/task/components/data-view', [
    'searchModel' => $taskSearchModel,
    'dataViewOptions' => [
        'searchAction' => new ReplaceArrayValue($taskSearchModel->searchUrl('/crm/admin/customer/view', [
            'id' => $model->id,
            'action' => 'task',
        ], false)),
    ],
]);

echo $this->block('@end');
$this->endContent();
