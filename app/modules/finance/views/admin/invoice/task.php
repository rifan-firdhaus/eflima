<?php

use modules\account\web\admin\View;
use modules\finance\models\Invoice;
use modules\task\models\forms\task\TaskSearch;
use yii\helpers\ReplaceArrayValue;

/**
 * @var View       $this
 * @var Invoice    $model
 * @var TaskSearch $taskSearchModel
 */

$this->subTitle = Yii::t('app', 'Tasks');


$this->beginContent('@modules/finance/views/admin/invoice/components/view-layout.php', [
    'model' => $model,
    'active' => 'task',
]);

echo $this->render('@modules/task/views/admin/task/components/data-view', [
    'searchModel' => $taskSearchModel,
    'dataViewOptions' => [
        'searchAction' => new ReplaceArrayValue($taskSearchModel->searchUrl('/finance/admin/invoice/view', [
            'id' => $model->id,
            'action' => 'task',
        ], false)),
    ],
]);

$this->endContent();
