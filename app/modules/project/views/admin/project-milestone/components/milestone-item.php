<?php

use modules\account\web\admin\View;
use modules\project\models\ProjectMilestone;
use modules\task\models\Task;
use modules\task\widgets\inputs\TaskStatusDropdown;
use modules\ui\widgets\Icon;
use yii\bootstrap4\ButtonDropdown;
use yii\bootstrap4\Progress;
use yii\helpers\Html;

/**
 * @var View             $this
 * @var ProjectMilestone $model
 * @var Task[]|array     $tasks
 */

$tasks = $model->taskDataProvider->models;
$colors = '';

foreach (ProjectMilestone::colors() AS $colorId => $color) {
    $colors .= Html::a([
        'label' => Html::tag('div', '', ['class' => "project-milestone-color project-milestone-color-{$colorId}"]),
        'url' => ['/project/admin/project-milestone/change-color', 'id' => $model->id, 'color' => $colorId],
        'class' => 'dropdown-item',
        'data-toggle' => 'tooltip',
        'title' => $color,
    ]);
}

$colors = Html::tag('div', $colors, ['class' => 'd-flex flex-wrap project-milestone-colors'])
?>

<div data-id="<?= $model->id; ?>" class="project-milestone-item  h-100 d-flex flex-column">
    <div class="project-milestone-item-header align-items-center <?= "project-milestone-color-{$model->color}" ?> flex-shrink-0 flex-grow-0 d-flex">
        <div class="handle d-flex align-items-center"><?= Icon::show('i8:move', ['class' => 'icon icons8-size']) ?></div>
        <div class="project-milestone-item-information">
            <?= Html::a(Html::encode($model->name), ['/project/admin/project-milestone/update', 'id' => $model->id], [
                'class' => 'project-milestone-item-title d-block',
                'data-lazy-modal' => 'project-milestone-form',
                'data-lazy-container' => '#main-container',
                'data-lazy-modal-size' => 'modal-md',
            ]); ?>
            <div class="font-size-sm" data-toggle="tooltip" title="<?= Yii::$app->formatter->asRelativeTime($model->started_date) ?>">
                <?= Yii::t('app', 'Start: {date}', [
                    'date' => Yii::$app->formatter->asDatetime($model->started_date),
                ]) ?>
            </div>
            <div class="font-size-sm" data-toggle="tooltip" title="<?= Yii::$app->formatter->asRelativeTime($model->deadline_date) ?>">
                <?= Yii::t('app', 'Deadline: {date}', [
                    'date' => Yii::$app->formatter->asDatetime($model->deadline_date),
                ]) ?>
            </div>
        </div>
        <div class="ml-auto">
            <?= ButtonDropdown::widget([
                'label' => Icon::show('i8:double-down', ['class' => 'icon icons8-size']),
                'buttonOptions' => [
                    'class' => ['btn btn-link btn-menu btn-icon px-0', 'toggle' => ''],
                ],
                'encodeLabel' => false,
                'dropdown' => [
                    'items' => [
                        [
                            'encode' => false,
                            'label' => Icon::show('i8:plus', ['class' => 'icon mr-2']) . Yii::t('app', 'Add Task'),
                            'url' => ['/task/admin/task/add', 'milestone_id' => $model->id, 'model' => 'project', 'model_id' => $model->project_id],
                            'linkOptions' => [
                                'data-lazy-modal' => 'task-form',
                                'data-lazy-container' => '#main-container',
                            ],
                        ],
                        [
                            'encode' => false,
                            'label' => Icon::show('i8:edit', ['class' => 'icon mr-2']) . Yii::t('app', 'Update'),
                            'url' => ['/project/admin/project-milestone/update', 'id' => $model->id],
                            'linkOptions' => [
                                'data-lazy-modal' => 'project-milestone-form',
                                'data-lazy-container' => '#main-container',
                                'data-lazy-modal-size' => 'modal-md',
                            ],
                        ],
                        '-',
                        $colors,
                        '-',
                        [
                            'encode' => false,
                            'label' => Icon::show('i8:trash', ['class' => 'icon mr-2']) . Yii::t('app', 'Delete'),
                            'url' => ['/project/admin/project-milestone/delete', 'id' => $model->id],
                            'linkOptions' => [
                                'class' => 'text-danger',
                                'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure', [
                                    'object_name' => $model->name,
                                ]),
                            ],
                        ],
                    ],
                ],
            ]) ?>
        </div>
    </div>
    <div class="project-milestone-item-content h-100 overflow-auto">
        <?php foreach ($tasks AS $task): ?>
            <div data-id="<?= $task->id ?>" class="project-milestone-item-task" style="background: <?= Html::hex2rgba($task->status->color_label, 0.05) ?>">
                <div class="project-milestone-item-task-header  d-flex">
                    <?= Html::a(Html::encode($task->title), ['/task/admin/task/view', 'id' => $task->id], [
                        'data-lazy-modal' => 'task-view-modal',
                        'data-lazy-container' => '#main-container',
                        'class' => 'project-milestone-item-task-title',
                    ]) ?>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <div class="task-list-progress-label mr-3 text-primary font-weight-bold"><?= $task->progress * 100 ?>%</div>
                    <?= Progress::widget([
                        'percent' => $task->progress * 100,
                        'options' => [
                            'style' => 'height:4px',
                            'class' => 'flex-grow-1',
                        ],
                    ]);
                    ?>
                </div>
                <div class="project-milestone-item-task-content">
                    <div data-toggle="tooltip" title="<?= Html::encode($task->status->label) ?>" class="project-milestone-item-task-status" style="background: <?= $task->status->color_label ?>"></div>
                    <div class="d-flex align-items-center">
                        <div class="project-milestone-item-task-assignee">
                            <?php
                            $assignees = $task->assignees;
                            $result = [];
                            $more = count($assignees) - 6;

                            foreach ($assignees AS $index => $assignee) {
                                $result[] = Html::tag('div', Html::img($assignee->account->getFileVersionUrl('avatar', 'thumbnail')), [
                                    'class' => 'task-avatar',
                                    'data-toggle' => 'tooltip',
                                    'title' => $assignee->name,
                                ]);

                                if ($index === 1 && $more > 0) {
                                    $result[] = Html::tag('div', "+{$more}", [
                                        'class' => 'task-avatar-more',
                                        'data-toggle' => 'tooltip',
                                    ]);

                                    break;
                                }
                            }

                            echo implode('', $result);
                            ?>
                        </div>
                        <div class="ml-auto">
                            <?= TaskStatusDropdown::widget([
                                'value' => $task->status_id,
                                'url' => function ($status) use ($task) {
                                    return ['/task/admin/task/change-status', 'id' => $task->id, 'status' => $status['id']];
                                },
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>


