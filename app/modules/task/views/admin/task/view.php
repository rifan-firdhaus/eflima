<?php

use modules\account\models\StaffAccount;
use modules\account\web\admin\View;
use modules\account\widgets\inputs\StaffInput;
use modules\core\helpers\Common;
use modules\file_manager\helpers\ImageVersion;
use modules\task\assets\admin\TaskViewAsset;
use modules\task\models\forms\task_interaction\TaskInteractionSearch;
use modules\task\models\Task;
use modules\task\models\TaskAssignee;
use modules\task\models\TaskInteraction;
use modules\task\widgets\inputs\TaskCheckListInput;
use modules\task\widgets\inputs\TaskPriorityDropdown;
use modules\task\widgets\inputs\TaskStatusDropdown;
use modules\ui\widgets\Card;
use modules\ui\widgets\data_table\columns\ActionColumn;
use modules\ui\widgets\data_table\columns\DateColumn;
use modules\ui\widgets\data_table\DataTable;
use modules\ui\widgets\Icon;
use modules\ui\widgets\inputs\RangeInput;
use modules\ui\widgets\lazy\Lazy;
use modules\ui\widgets\table\cells\Cell;
use yii\bootstrap4\Progress;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * @var View                  $this
 * @var Task                  $model
 * @var TaskInteraction       $interactionModel
 * @var TaskInteractionSearch $interactionSearchModel
 * @var StaffAccount          $account
 */

$formatter = Yii::$app->formatter;
$account = Yii::$app->user->identity;
$isTimerStarted = $model->isTimerStarted($account->profile->id);
$totalRecordedTime = $model->totalRecordedTime;

TaskViewAsset::register($this);

$isChecklistExists = $model->getChecklists()->exists();

$this->beginContent('@modules/task/views/admin/task/components/view-layout.php', compact('model'));

echo $this->block('@begin');

if (Yii::$app->user->can('admin.task.delete')) {
    $this->toolbar['delete-task'] = Html::a([
        'url' => ['/task/admin/task/delete', 'id' => $model->id],
        'class' => 'btn btn-outline-danger btn-icon',
        'icon' => 'i8:trash',
        'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure', [
            'object_name' => Html::tag('strong', $model->title),
        ]),
        'data-placement' => 'bottom',
        'title' => Yii::t('app', 'Delete'),
        'data-toggle' => 'tooltip',
        'data-lazy-options' => ['method' => 'DELETE'],
    ]);
}

if ($model->is_timer_enabled) {
    if (Yii::$app->user->can('admin.task.timer.toggle')) {
        $this->toolbar['toggle-timer'] = Html::a([
            'url' => ['/task/admin/task/toggle-timer', 'id' => $model->id, 'start' => !$isTimerStarted],
            'icon' => !$isTimerStarted ? 'i8:play' : 'i8:stop',
            'title' => !$isTimerStarted ? Yii::t('app', 'Start Timer') : Yii::t('app', 'Stop Timer'),
            'class' => 'btn btn-outline-primary btn-icon',
            'data-toggle' => 'tooltip',
            'data-lazy-options' => ['method' => 'POST'],
        ]);
    }
}

if (Yii::$app->user->can('admin.task.update')) {
    $this->toolbar['update-task'] = Html::a([
        'label' => Html::tag('span', Yii::t('app', 'Update'), ['class' => 'btn-label']),
        'url' => ['/task/admin/task/update', 'id' => $model->id],
        'class' => 'btn btn-icon-sm btn-outline-secondary',
        'icon' => 'i8:edit',
        'data-lazy-modal' => 'task-form-modal',
        'data-lazy-container' => '#main-container',
    ]);
}

if (Yii::$app->user->can('admin.task.add')) {
    $this->toolbar['duplicate-task'] = Html::a([
        'label' => Html::tag('span', Yii::t('app', 'Duplicate'), ['class' => 'btn-label']),
        'url' => ['/task/admin/task/add', 'duplicate_id' => $model->id],
        'class' => 'btn btn-icon-sm btn-outline-secondary',
        'icon' => 'i8:copy',
        'data-lazy-modal' => 'task-form-modal',
        'data-lazy-container' => '#main-container',
    ]);
}
?>
<div class="d-flex h-100">

    <?php Lazy::begin([
        'id' => 'task-view-wrapper-lazy',
        'options' => [
            'class' => 'h-100 py-3 w-100 overflow-auto container-fluid task-view-wrapper-overflow',
        ],
    ]); ?>

    <div id="task-view-wrapper-<?= $this->uniqueId ?>" class="task-view-wrapper">

        <div class="row border-bottom">
            <?= $this->block('@main:begin') ?>

            <div class="col">
                <?= $this->block('@main/left:begin') ?>

                <?php Card::begin([
                    'title' => Yii::t('app', 'Task Detail'),
                    'icon' => 'i8:checked',
                    'options' => [
                        'class' => 'card sticky-top border-bottom-0',
                    ],
                    'bodyOptions' => [
                        'class' => 'card-body px-0',
                    ],
                    'headerOptions' => [
                        'class' => 'card-header px-0',
                    ],
                ]); ?>

                <table class="table table-detail-view m-0">
                    <?= $this->block('@detail:begin') ?>

                    <tr>
                        <th class="border-top-0"><?= Yii::t('app', 'Title') ?></th>
                        <td class="border-top-0"><?= Html::encode($model->title) ?></td>
                    </tr>

                    <?php if (!empty($model->model)): ?>
                        <tr>
                            <th><?= Yii::t('app', 'Related to') ?></th>
                            <td>
                                <?php
                                $relatedName = $model->relatedObject->getLink($model->relatedModel);

                                if (is_null($relatedName)) {
                                    $relatedName = $model->relatedObject->getName($model->relatedModel);
                                }

                                echo $relatedName;
                                ?>
                                <div class="font-size-sm">
                                    <?= $model->relatedObject->getLabel() ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <tr>
                        <th><?= Yii::t('app', 'Status') ?></th>
                        <td>
                            <?= TaskStatusDropdown::widget([
                                'value' => $model->status_id,
                                'url' => function ($status) use ($model) {
                                    return ['/task/admin/task/change-status', 'status' => $status['id'], 'id' => $model->id];
                                },
                            ]) ?>
                        </td>
                    </tr>

                    <tr>
                        <th><?= Yii::t('app', 'Priority') ?></th>
                        <td>
                            <?= TaskPriorityDropdown::widget([
                                'value' => $model->priority_id,
                                'url' => function ($priority) use ($model) {
                                    return ['/task/admin/task/change-priority', 'priority' => $priority['id'], 'id' => $model->id];
                                },
                            ]); ?>
                        </td>
                    </tr>

                    <tr>
                        <th><?= Yii::t('app', 'Created by') ?></th>
                        <td>
                            <?= Html::a(Html::encode($model->creator->profile->name), ['/account/admin/staff/view', 'id' => $model->creator_id]); ?>
                            <div class="font-size-sm">
                                <?= Html::encode($model->creator->username) ?>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th><?= Yii::t('app', 'Created at') ?></th>
                        <td>
                            <?= Yii::$app->formatter->asDatetime($model->created_at) ?>
                            <div class="font-size-sm">
                                <?= Yii::$app->formatter->asRelativeTime($model->created_at) ?>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th><?= Yii::t('app', 'Start Date') ?></th>
                        <td>
                            <?= Yii::$app->formatter->asDatetime($model->started_date) ?>
                            <div class="font-size-sm">
                                <?= Yii::$app->formatter->asRelativeTime($model->started_date) ?>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th><?= Yii::t('app', 'Deadline') ?></th>
                        <td>
                            <?= Yii::$app->formatter->asDatetime($model->deadline_date) ?>
                            <div class="font-size-sm">
                                <?= Yii::$app->formatter->asRelativeTime($model->deadline_date) ?>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th><?= Yii::t('app', 'Progress') ?></th>
                        <td class="align-middle">
                            <?php
                            $progressInPercent = $model->progress * 100;
                            $progressLabel = Html::tag('div', $progressInPercent . "%", [
                                'class' => 'mr-2 text-primary task-progress-label data-table-primary-text',
                            ]);

                            if ($model->progress_calculation === Task::PROGRESS_CALCULATION_OWN) {
                                $slider = RangeInput::widget([
                                    'id' => 'task-progress-input',
                                    'name' => 'progress',
                                    'max' => 100,
                                    'value' => $model->progress ? $model->progress * 100 : 0,
                                    'jsOptions' => [
                                        'prefix' => Yii::t('app', 'Set progress to:'),
                                        'extra_classes' => 'task-progress-slider flex-grow-1',
                                        'postfix' => '%',
                                        'hide_min_max' => true,
                                        'hide_from_to' => false,
                                        'force_edges' => true,
                                        'grid_margin' => false,
                                        'onFinish' => new JsExpression("function(data,a){\$('#task-view-wrapper-{$this->uniqueId}').taskView('setProgress',data.from)}")
                                    ],
                                ]);

                                echo Html::tag('div', $progressLabel . $slider, [
                                    'class' => 'd-flex task-progress align-items-center',
                                    'style' => 'max-width: 10rem',
                                ]);
                            } else {
                                $progressBar = Progress::widget([
                                    'percent' => $progressInPercent,
                                    'options' => [
                                        'class' => 'flex-grow-1',
                                        'style' => 'height: 4px',
                                    ],
                                ]);
                                echo Html::tag('div', $progressLabel . $progressBar, [
                                    'class' => 'd-flex task-progress align-items-center',
                                    'style' => 'max-width: 10rem',
                                ]);
                            }
                            ?>
                            <div class="font-size-sm">
                                <?php if ($model->progress_calculation === Task::PROGRESS_CALCULATION_CHECKLIST): ?>
                                    <?= $model->progressCalculationDisplay; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>

                    <?php if ($model->is_timer_enabled): ?>
                        <tr>
                            <th><?= Yii::t('app', 'Logged Time') ?></th>
                            <td>
                                <span data-toggle="tooltip" title="<?= Yii::$app->formatter->asDuration($totalRecordedTime) ?>">
                                  <?= Yii::$app->formatter->asShortDuration($totalRecordedTime) ?>
                                </span>
                                <?php if ($model->estimation && $totalRecordedTime > $model->estimationSecond): ?>
                                    <div class="font-size-sm text-danger">
                                        <?= Yii::t('app', '{time} late from estimation', [
                                            'time' => Yii::$app->formatter->asShortDuration($model->estimationSecond),
                                        ]) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?= $this->block('@detail:end') ?>
                </table>

                <?php Card::end(); ?>

                <?= $this->block('@main/left:end') ?>
            </div>

            <div class="col-md-7">
                <div class="sticky-top">
                    <?= $this->block('@main/right:begin'); ?>

                    <?php if (!Common::isEmpty($model->description) || $isChecklistExists || !empty($model->attachments)): ?>

                        <?php Card::begin([
                            'title' => Yii::t('app', 'Task Content'),
                            'icon' => 'i8:file',
                            'options' => [
                                'class' => 'card border-bottom',
                            ],
                            'bodyOptions' => [
                                'class' => 'card-body px-0',
                            ],
                            'headerOptions' => [
                                'class' => 'card-header px-0',
                            ],
                        ]); ?>

                        <?= $this->block('@content:begin') ?>

                        <?php if (!Common::isEmpty($model->description)): ?>
                            <div class="tesk-description mb-3">
                                <?= $this->block('@description:begin') ?>

                                <?= $formatter->asHtml($model->description) ?>

                                <?= $this->block('@description:end') ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($model->attachments): ?>
                            <h5><?= Icon::show('i8:link', ['class' => 'icons8-size text-primary icon mr-2']) . Yii::t('app', 'Attachments') ?>:</h5>
                            <div class="attachments mb-3">
                                <?= $this->block('@attachment:begin') ?>

                                <?php foreach ($model->attachments AS $attachment): ?>
                                    <?php
                                    $metaData = $attachment->getFileMetaData('file');
                                    ?>
                                    <a href="<?= $metaData['url'] ?>" target="_blank" data-lazy="0" class="attachment shadow-sm" data-toggle="tooltip" title="<?= Html::encode($metaData['name']) ?>">
                                        <div class="attachment-preview">
                                            <?php
                                            if (ImageVersion::isImage($attachment->getFilePath('file'))) {
                                                echo Html::img($metaData['src']);
                                            } else {
                                                echo Html::tag('div', Html::tag('div', pathinfo($attachment->getFilePath('file'), PATHINFO_EXTENSION)), [
                                                    'class' => 'attachment-extension',
                                                ]);
                                            }
                                            ?>
                                        </div>
                                        <div class="attachment-name"><?= $metaData['name'] ?></div>
                                    </a>
                                <?php endforeach; ?>

                                <?= $this->block('@attachment:begin') ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($isChecklistExists): ?>
                            <div class="task-checklist">
                                <div class="d-flex align-items-center">
                                    <h5><?= Icon::show('i8:checked', ['class' => 'icons8-size text-primary icon mr-2']) . Yii::t('app', 'Checklist') ?>:</h5>
                                    <div class="task-checklist-progress ml-auto">
                                        <?php
                                        $progressInPercent = round($model->getChecklists()->checked()->count() / $model->getChecklists()->count() * 100);
                                        $progressLabel = Html::tag('div', $progressInPercent . "%", [
                                            'class' => 'mr-2 text-primary data-table-primary-text',
                                        ]);
                                        $progressBar = Progress::widget([
                                            'percent' => $progressInPercent,
                                            'options' => [
                                                'class' => 'flex-grow-1',
                                                'style' => 'height: 4px',
                                            ],
                                        ]);
                                        echo Html::tag('div', $progressLabel . $progressBar, [
                                            'class' => 'd-flex task-progress align-items-center ml-auto',
                                            'style' => 'width: 10rem',
                                        ]);
                                        ?>
                                    </div>
                                </div>

                                <?= TaskCheckListInput::widget([
                                    'task_id' => $model->id,
                                    'name' => 'task-checklist',
                                    'jsOptions' => [
                                        'url' => Url::to(['/task/admin/task-checklist/change', 'task_id' => $model->id]),
                                        'sortUrl' => Url::to(['/task/admin/task-checklist/sort', 'task_id' => $model->id]),
                                    ],
                                ]) ?>
                            </div>
                        <?php endif; ?>

                        <?= $this->block('@content:end') ?>

                        <?php Card::end(); ?>

                    <?php endif; ?>

                    <?php
                    $assigneeCard = Card::begin([
                        'title' => Yii::t('app', 'Assignee'),
                        'icon' => 'i8:account',
                        'headerOptions' => [
                            'class' => 'card-header px-0',
                        ],
                        'bodyOptions' => false,
                    ]);

                    echo $this->block('@assignee:begin');

                    if ($model->visibility !== Task::VISIBILITY_PRIVATE && Yii::$app->user->can('admin.task.assignee')) {
                        $addAssigneeButton = Html::a(Icon::show('i8:paper-plane') . Yii::t('app', 'Invite'), '#', [
                            'class' => 'btn btn-outline-primary btn-sm btn-task-assignee',
                        ]);
                        $addAssigneeInput = StaffInput::widget([
                            'name' => 'assignee',
                            'url' => ['/task/admin/task/staff-assignable-auto-complete', 'id' => $model->id],
                            'id' => 'task-assignee-input',
                            'options' => [
                                'class' => 'task-assignee-input',
                            ],
                        ]);

                        $assigneeCard->addToHeader(
                            Html::tag('div', $addAssigneeInput . $addAssigneeButton, [
                                'class' => 'task-assignee-input-container',
                            ])
                        );
                    }

                    echo DataTable::widget([
                        'dataProvider' => new ArrayDataProvider([
                            'allModels' => $model->getAssigneesRelationship()
                                ->with([
                                    'assignee' => function ($query) {
                                        return $query->with('account');
                                    },
                                ])
                                ->all(),
                            'pagination' => false,
                        ]),
                        'id' => 'task-assignee-list',
                        'idAttribute' => 'id',
                        'columns' => [
                            [
                                'attribute' => 'avatar',
                                'format' => 'raw',
                                'label' => '',
                                'contentCell' => [
                                    'vAlign' => Cell::V_ALIGN_CENTER,
                                    'options' => [
                                        'style' => ['width' => '4rem'],
                                        'class' => 'pr-0',
                                    ],
                                ],
                                'content' => function ($model) {
                                    /** @var TaskAssignee $model */

                                    return Html::img($model->assignee->account->getFileVersionUrl('avatar', 'thumbnail', Yii::getAlias('@web/public/img/avatar.png')), [
                                        'class' => 'w-100 rounded-circle',
                                    ]);
                                },
                            ],
                            [
                                'attribute' => 'staff.name',
                                'label' => Yii::t('app', 'Staff'),
                                'format' => 'raw',
                                'content' => function ($model) {
                                    /** @var TaskAssignee $model */

                                    $name = Html::a([
                                        'label' => Html::encode($model->assignee->name),
                                        'url' => ['/account/admin/staff/update', 'id' => $model->id],
                                        'class' => 'data-table-main-text',
                                        'data-lazy-modal' => 'staff-form-modal',
                                        'data-lazy-container' => '#main-container',
                                    ]);

                                    $username = Html::tag(
                                        'div',
                                        Html::encode($model->assignee->account->username),
                                        ['class' => 'data-table-secondary-text']
                                    );

                                    return $name . $username;
                                },
                            ],
                            [
                                'attribute' => 'assigned_at',
                                'label' => Yii::t('app', 'Assigned At'),
                                'class' => DateColumn::class,
                            ],
                            [
                                'class' => ActionColumn::class,
                                'controller' => '/task/admin/task',
                                'buttons' => [
                                    'view' => false,
                                    'update' => false,
                                    'delete' => false,
                                    'unassign' => [
                                        'visible' => $model->visibility !== Task::VISIBILITY_PRIVATE && Yii::$app->user->can('admin.task.assignee'),
                                        'value' => function ($key, $model, $id, $index) {
                                            return [
                                                'icon' => 'i8:trash',
                                                'label' => Yii::t('app', 'Unassign'),
                                                'data-confirmation' => Yii::t('app', 'You are about to unassign {object_name}, are you sure', [
                                                    'object_name' => Yii::t('app', 'this staff'),
                                                ]),
                                                'url' => Url::to(['/task/admin/task/unassign', 'id' => $model->task_id, 'staff_id' => $model->assignee_id]),
                                                'class' => 'text-danger',
                                                'data-lazy-container' => false,
                                                'data-lazy-options' => ['scroll' => false, 'method' => 'POST'],
                                            ];
                                        },
                                    ],
                                ],
                            ],
                        ],
                    ]);

                    echo $this->block('@assignee:begin');

                    Card::end();
                    ?>


                    <?= $this->block('@main/right:end'); ?>
                </div>
            </div>

            <?= $this->block('@main:end') ?>
        </div>

        <div class="interactions row">
            <div class="col-12">
                <h3>
                    <?= Icon::show('i8:chat', ['class' => 'text-primary mr-2 icon icons8-size']) . Yii::t('app', 'Discussion ({number})', [
                        'number' => Html::tag('strong', Yii::$app->formatter->asDecimal($interactionSearchModel->getQuery()->count())),
                    ]) ?>
                </h3>
                <div class="task-interaction-form">
                    <?= $this->render('/admin/task-interaction/components/form', ['model' => $interactionModel]) ?>
                </div>
                <div class="task-interaction-list-wrapper">
                    <?= $this->render('/admin/task-interaction/components/data-list', ['dataProvider' => $interactionSearchModel->dataProvider]) ?>
                </div>
            </div>
        </div>

    </div>


    <?php

    $jsOptions = Json::encode([
        'assignUrl' => Url::to(['/task/admin/task/assign', 'id' => $model->id]),
        'setProgressUrl' => Url::to(['/task/admin/task/update-progress', 'id' => $model->id]),
    ]);

    $this->registerJs("$('#task-view-wrapper-{$this->uniqueId}').taskView({$jsOptions})");
    ?>
    <?php Lazy::end(); ?>
    <div class="border-left bg-really-light content-sidebar d-none d-sm-block task-view-sidebar h-100 overflow-auto">
        <?= $this->block('@sidebar:begin') ?>

        <?= $this->render('@modules/note/views/admin/note/components/container', [
            'configurations' => [
                'id' => 'task-note',
                'model' => 'task',
                'model_id' => $model->id,
                'inline' => true,
                'search' => false,
                'jsOptions' => [
                    'autoLoad' => true,
                ],
            ],
        ]) ?>

        <?= $this->block('@sidebar:end') ?>
    </div>
    <?php

    echo $this->block('@end');

    $this->endContent(); ?>
