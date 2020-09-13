<?php

use modules\account\web\admin\View;
use modules\finance\models\Expense;
use modules\task\models\forms\task\TaskSearch;
use yii\helpers\ReplaceArrayValue;

/**
 * @var View       $this
 * @var Expense    $model
 * @var TaskSearch $taskSearchModel
 */

$this->subTitle = Yii::t('app', 'Task');

$this->beginContent('@modules/finance/views/admin/expense/components/view-layout.php', [
    'model' => $model,
    'active' => 'task',
]);

echo $this->block('@begin');

echo $this->render('@modules/task/views/admin/task/components/data-view', [
    'searchModel' => $taskSearchModel,
    'dataViewOptions' => [
        'searchAction' => new ReplaceArrayValue($taskSearchModel->searchUrl('/finance/admin/expense/view', [
            'id' => $model->id,
            'action' => 'task',
        ], false)),
    ],
]);

echo $this->block('@end');

$this->endContent();
