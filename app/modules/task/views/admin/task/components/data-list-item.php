<?php

use modules\account\web\admin\View;
use modules\task\models\Task;
use modules\task\models\TaskPriority;
use modules\task\models\TaskStatus;
use modules\task\widgets\inputs\TaskPriorityDropdown;
use modules\task\widgets\inputs\TaskStatusDropdown;
use yii\bootstrap4\Progress;
use yii\helpers\Html;

/**
 * @var View           $this
 * @var Task           $model
 * @var TaskStatus[]   $statusModels
 * @var TaskPriority[] $priorityModels
 */
?>
<div class="task-list-item">

    <div class="title"><?= Html::a(Html::encode($model->title), ['/task/admin/task/view', 'id' => $model->id]) ?></div>

    <div class="assignees">
        <?php
        foreach ($model->assignees AS $index => $assignee) {
            echo Html::tag('div', Html::img('@web/public/img/avatar.jpg'), [
                'class' => 'task-avatar',
                'data-toggle' => 'tooltip',
                'title' => $assignee->name,
            ]);
        }
        ?>
    </div>

    <div class="d-flex align-items-center mb-2">
        <div class="task-list-progress-label mr-3"><?= $model->progress * 100 ?>%</div>
        <?= Progress::widget([
            'percent' => $model->progress * 100,
            'options' => [
                'style' => 'height:4px',
                'class' => 'flex-grow-1',
            ],
        ]);
        ?>
    </div>

    <div class="metas d-flex">

        <div class="column-detail mb-3">
            <div class="label"><?= Yii::t('app', 'Status') ?></div>
            <div class="value">
                <?= TaskStatusDropdown::widget([
                    'value' => $model->status_id,
                    'models' => isset($statusModels) ? $statusModels : null,
                    'buttonOptions' => [
                        'class' => 'd-block',
                    ],
                    'url' => function ($status) use ($model) {
                        return ['/task/admin/task/change-status', 'id' => $model->id, 'status' => $status['id']];
                    },
                ]); ?>
            </div>
        </div>

        <div class="column-detail mb-3">
            <div class="label"><?= Yii::t('app', 'Priority') ?></div>
            <div class="value">
                <?= TaskPriorityDropdown::widget([
                    'value' => $model->priority_id,
                    'models' => isset($priorityModels) ? $priorityModels : null,
                    'buttonOptions' => [
                        'class' => 'd-block',
                    ],
                    'url' => function ($priority) use ($model) {
                        return ['/task/admin/task/change-priority', 'id' => $model->id, 'priority' => $priority['id']];
                    },
                ]); ?>
            </div>
        </div>

    </div>

    <div class="metas d-flex">

        <div class="column-detail">
            <div class="label"><?= Yii::t('app', 'Started Date') ?></div>
            <div class="value">
                <div><?= Yii::$app->formatter->asDate($model->started_date) ?></div>
                <small><?= Yii::$app->formatter->asTime($model->started_date) ?></small>
            </div>
        </div>

        <div class="column-detail">
            <div class="label"><?= Yii::t('app', 'Deadline Date') ?></div>
            <div class="value">
                <div><?= Yii::$app->formatter->asDate($model->started_date) ?></div>
                <small><?= Yii::$app->formatter->asTime($model->started_date) ?></small>
            </div>
        </div>

    </div>
</div>