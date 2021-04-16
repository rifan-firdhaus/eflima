<?php

use modules\account\web\admin\View;
use modules\task\models\forms\task\TaskSearch;
use modules\task\widgets\inputs\TaskStatusDropdown;
use modules\ui\widgets\lazy\Lazy;
use yii\bootstrap4\Progress;
use yii\helpers\Html;

/**
 * @var View       $this
 * @var TaskSearch $taskSearchModel
 */

foreach ($taskSearchModel->dataProvider->models AS $task): ?>
    <?php
    Lazy::begin([
        'id' => "project-milestone-item-task-lazy-{$task->id}",
        'options' => [
            'data-id' => $task->id,
            'class' => 'project-milestone-item-task-container'
        ],
    ])
    ?>
    <div class="project-milestone-item-task" style="background: <?= Html::hex2rgba($task->status->color_label, 0.05) ?>">
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
                            'class' => 'avatar-list-item',
                            'data-toggle' => 'tooltip',
                            'title' => $assignee->name,
                        ]);

                        if ($index === 1 && $more > 0) {
                            $result[] = Html::tag('div', "+{$more}", [
                                'class' => 'avatar-list-item-more',
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
    <?php Lazy::end(); ?>
<?php endforeach; ?>
