<?php

use modules\account\web\admin\View;
use modules\finance\models\Proposal;
use modules\task\models\forms\task\TaskSearch;
use yii\helpers\ReplaceArrayValue;

/**
 * @var Proposal   $model
 * @var TaskSearch $taskSearchModel
 * @var View       $this
 */

$active = 'task';
$this->subTitle = Yii::t('app', 'Task');

$this->beginContent('@modules/finance/views/admin/proposal/components/view-layout.php', compact('model', 'active'));
echo $this->block('@begin');

echo $this->render('@modules/task/views/admin/task/components/data-view', [
    'searchModel' => $taskSearchModel,
    'dataViewOptions' => [
        'searchAction' => new ReplaceArrayValue($taskSearchModel->searchUrl('/finance/admin/proposal/view', [
            'id' => $model->id,
            'action' => 'task',
        ], false)),
    ],
]);

echo $this->block('@end');
$this->endContent();
