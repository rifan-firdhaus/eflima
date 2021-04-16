<?php

use modules\account\web\admin\View;
use modules\project\models\ProjectMilestone;
use modules\task\models\Task;
use modules\ui\widgets\Icon;
use modules\ui\widgets\lazy\Lazy;
use yii\bootstrap4\ButtonDropdown;
use yii\helpers\Html;

/**
 * @var View             $this
 * @var ProjectMilestone $model
 * @var Task[]|array     $tasks
 */

$tasks = $model->taskDataProvider->models;
$colors = '';

if (Yii::$app->user->can('admin.project.view.milestone.update')) {
    foreach (ProjectMilestone::colors() AS $colorId => $color) {
        $colors .= Html::a([
            'label' => Html::tag('div', '', ['class' => "project-milestone-color project-milestone-color-{$colorId}"]),
            'url' => ['/project/admin/project-milestone/change-color', 'id' => $model->id, 'color' => $colorId],
            'class' => 'dropdown-item',
            'data-toggle' => 'tooltip',
            'title' => $color,
            'data-lazy-options' => ['method' => "POST"],
        ]);
    }

    $colors = Html::tag('div', $colors, ['class' => 'd-flex flex-wrap project-milestone-colors']);
}


?>
<div class="project-milestone-item  h-100 d-flex flex-column" data-id="<?= $model->id ?>">
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
                            'visible' => Yii::$app->user->can('admin.project.view.milestone.task'),
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
                            'visible' => Yii::$app->user->can('admin.project.view.milestone.update'),
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
                                'title' => Yii::t('app', 'Delete'),
                                'data-lazy-options' => ['method' => "DELETE"],
                            ],
                            'visible' => Yii::$app->user->can('admin.project.view.milestone.delete'),
                        ],
                    ],
                ],
            ]) ?>
        </div>
    </div>
    <div class="project-milestone-item-content h-100 overflow-hidden">
        <?php Lazy::begin([
            'id' => "project-milestone-items-{$model->id}",
            'options' => [
                'class' => ' h-100 overflow-auto',
            ],
        ]); ?>
        <a href="#" class="btn btn-outline-primary btn-block btn-load-more"><?= Yii::t('app', 'Load More'); ?></a>
        <?php Lazy::end(); ?>
    </div>
</div>


