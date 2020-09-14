<?php

use modules\account\web\admin\View;
use modules\account\widgets\inputs\StaffInput;
use modules\account\widgets\StaffCommentWidget;
use modules\address\assets\FlagIconAsset;
use modules\crm\assets\admin\LeadViewAsset;
use modules\crm\models\forms\lead_follow_up\LeadFollowUpSearch;
use modules\crm\models\Lead;
use modules\crm\models\LeadStatus;
use modules\crm\widgets\inputs\LeadStatusDropdown;
use modules\task\models\TaskAssignee;
use modules\ui\widgets\Card;
use modules\ui\widgets\data_table\columns\ActionColumn;
use modules\ui\widgets\data_table\columns\DateColumn;
use modules\ui\widgets\data_table\DataTable;
use modules\ui\widgets\Icon;
use modules\ui\widgets\table\cells\Cell;
use yii\bootstrap4\ButtonDropdown;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * @var View               $this
 * @var Lead               $model
 * @var LeadFollowUpSearch $followUpSearchModel
 */

LeadViewAsset::register($this);
FlagIconAsset::register($this);

$this->beginContent('@modules/crm/views/admin/lead/components/view-layout.php', compact('model'));
echo $this->block('@begin');

$this->toolbar['delete-lead'] = Html::a([
    'url' => ['/crm/admin/lead/delete', 'id' => $model->id],
    'class' => 'btn btn-outline-danger btn-icon',
    'icon' => 'i8:trash',
    'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure', [
        'object_name' => Html::tag('strong', $model->name),
    ]),
    'data-placement' => 'bottom',
    'title' => Yii::t('app', 'Delete'),
]);

$this->toolbar['update-lead'] = Html::a([
    'label' => Yii::t('app', 'Update'),
    'url' => ['/crm/admin/lead/update', 'id' => $model->id],
    'class' => 'btn btn-outline-secondary',
    'icon' => 'i8:edit',
    'data-lazy-modal' => 'lead-form-modal',
    'data-lazy-container' => '#main-container',
]);

$this->toolbar['convert-lead'] = Html::a([
    'label' => Yii::t('app', 'Convert'),
    'url' => ['/crm/admin/lead/convert', 'id' => $model->id],
    'class' => 'btn btn-outline-primary',
    'icon' => 'i8:refresh',
    'data-lazy-modal' => 'lead-form-modal',
    'data-lazy-container' => '#main-container',
    'title' => Yii::t('app', 'Convert to Customer'),
    'data-toggle' => 'tooltip',
]);


$leadStatusMenuItems = [
    [
        'label' => Yii::t('app', 'Set status to:'),
    ],
];

foreach (LeadStatus::find()->enabled()->andWhere(['!=', 'id', $model->status_id])->all() AS $projectStatus) {
    $leadStatusMenuItems[] = [
        'label' => Html::tag('span', '', ['class' => 'color-description', 'style' => ['background-color' => $projectStatus->color_label]]) . Html::encode($projectStatus->label),
        'url' => ['/crm/admin/lead/change-status', 'id' => $model->id, 'status' => $projectStatus->id],
    ];
}

$leadStatusMenuItems[] = '-';

$this->toolbar['lead-more'] = ButtonDropdown::widget([
    'label' => Icon::show('i8:double-down'),
    'encodeLabel' => false,
    'id' => 'lead-more-action',
    'buttonOptions' => [
        'class' => ['btn btn-outline-secondary btn-icon', 'toggle' => ''],
    ],
    'dropdown' => [
        'encodeLabels' => false,
        'items' => ArrayHelper::merge($leadStatusMenuItems, [
            [
                'label' => Icon::show('i8:checked', ['class' => 'icon icons8-size mr-2']) . Yii::t('app', 'Add {object}', [
                        'object' => Yii::t('app', 'Task'),
                    ]),
                'url' => ['/task/admin/task/add', 'model' => 'lead', 'model_id' => $model->id],
                'linkOptions' => [
                    'data-lazy-container' => '#main-container',
                    'data-lazy-modal' => 'task-form-modal',
                ],
            ],
            [
                'label' => Icon::show('i8:event', ['class' => 'icon icons8-size mr-2']) . Yii::t('app', 'Add {object}', [
                        'object' => Yii::t('app', 'Event'),
                    ]),
                'url' => ['/calendar/admin/event/add', 'model' => 'lead', 'model_id' => $model->id],
                'linkOptions' => [
                    'data-lazy-container' => '#main-container',
                    'data-lazy-modal' => 'event-form-modal',
                    'data-lazy-modal-size' => 'modal-lg',
                ],
            ],
        ]),
    ],
]);
?>

    <div id="lead-view-wrapper-<?= $this->uniqueId; ?>" class="d-flex h-100">
        <div class="overflow-auto py-3 w-100 d-flex flex-column container-fluid mh-100">
            <div class="d-flex row border-bottom">
                <?= $this->block('@main:begin') ?>

                <div class="col-md-5">
                    <?= $this->block('@main/left:begin') ?>

                    <?php Card::begin([
                        'title' => Yii::t('app', 'Basic Detail'),
                        'icon' => 'i8:contacts',
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
                        <?= $this->block('@company_detail:begin') ?>

                        <tr>
                            <th><?= Yii::t('app', 'Name') ?></th>
                            <td><?= Html::encode($model->name) ?></td>
                        </tr>

                        <tr>
                            <th><?= Yii::t('app', 'Phone') ?></th>
                            <td><?= Html::encode($model->phone) ?></td>
                        </tr>

                        <tr>
                            <th><?= Yii::t('app', 'Mobile') ?></th>
                            <td><?= Html::encode($model->mobile) ?></td>
                        </tr>

                        <tr>
                            <th><?= Yii::t('app', 'Email') ?></th>
                            <td><?= Yii::$app->formatter->asEmail($model->email) ?></td>
                        </tr>

                        <tr>
                            <th><?= Yii::t('app', 'Source') ?></th>
                            <td><?= Html::encode($model->source->name) ?></td>
                        </tr>

                        <tr>
                            <th><?= Yii::t('app', 'Status') ?></th>
                            <td>
                                <?= LeadStatusDropdown::widget([
                                    'value' => $model->status_id,
                                    'url' => function ($status) use ($model) {
                                        return ['/crm/admin/lead/change-status', 'id' => $model->id, 'status' => $status['id']];
                                    },
                                ]) ?>
                            </td>
                        </tr>

                        <tr>
                            <th><?= Yii::t('app', 'Address') ?></th>
                            <td>
                                <div class="d-flex">
                                    <div>
                                        <?= Html::encode($model->fullAddress) ?>
                                    </div>
                                    <div style="width:30px;height: 25px" class="flag-icon flex-shrink-0 ml-2 border align-self-center flag-icon-<?= strtolower($model->country->iso2) ?>"></div>
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

                        <?= $this->block('@company_detail:end') ?>
                    </table>

                    <?php Card::end(); ?>

                    <?= $this->block('@main/left:end') ?>
                </div>

                <div class="col-md-7">
                    <?= $this->block('@main/right:begin'); ?>

                    <?php
                    $followUpCard = Card::begin([
                        'title' => Yii::t('app', 'Follow Up'),
                        'icon' => 'i8:phone',
                        'headerOptions' => [
                            'class' => 'card-header px-0',
                        ],
                        'bodyOptions' => false,
                    ]);

                    $followUpCard->addToHeader(
                        Html::a(Icon::show('i8:phone') . Yii::t('app', 'Add Follow Up'), ['/crm/admin/lead-follow-up/add', 'lead_id' => $model->id], [
                            'class' => 'btn btn-sm btn-outline-primary',
                            'data-lazy-modal' => 'lead-follow-up-form-modal',
                            'data-lazy-modal-size' => 'modal-md',
                            'data-lazy-container' => '#main-container',
                        ])
                    );

                    echo $this->render('../lead-follow-up/components/data-view', [
                        'searchModel' => $followUpSearchModel,
                    ]);

                    Card::end(); ?>

                    <?php
                    $assigneeCard = Card::begin([
                        'title' => Yii::t('app', 'Assignee'),
                        'icon' => 'i8:account',
                        'headerOptions' => [
                            'class' => 'card-header px-0',
                        ],
                        'bodyOptions' => false,
                    ]);


                    $assigneeButton = Html::a(Icon::show('i8:paper-plane') . Yii::t('app', 'Assign'), '#', [
                        'class' => 'btn btn-outline-primary btn-sm btn-lead-assignee',
                    ]);
                    $assigneeInput = StaffInput::widget([
                        'name' => 'assignee',
                        'url' => ['/crm/admin/lead/staff-assignable-auto-complete', 'id' => $model->id],
                        'id' => 'event-member-input',
                        'options' => [
                            'class' => 'lead-assignee-input',
                        ],
                    ]);

                    $assigneeCard->addToHeader(
                        Html::tag('div', $assigneeInput . $assigneeButton, [
                            'class' => 'lead-assignee-input-container',
                        ])
                    );

                    echo $this->block('@assignee:begin');

                    echo DataTable::widget([
                        'dataProvider' => new ArrayDataProvider([
                            'allModels' => $model->assigneesRelationship,
                            'pagination' => false,
                        ]),
                        'id' => 'event-member-list',
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
                                'controller' => '/task/admin/task-assignee',
                                'buttons' => [
                                    'view' => false,
                                    'update' => false,
                                    'delete' => [
                                        'value' => [
                                            'icon' => 'i8:trash',
                                            'label' => Yii::t('app', 'Delete'),
                                            'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure', [
                                                'object_name' => Yii::t('app', 'this item'),
                                            ]),
                                            'class' => 'text-danger',
                                            'data-lazy-container' => false,
                                            'data-lazy-options' => ['scroll' => false],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]);

                    echo $this->block('@assignee:end');

                    Card::end();
                    ?>

                    <?= $this->block('@main/right:end'); ?>
                </div>

                <?= $this->block('@main:end') ?>
            </div>

            <div class="lead-comment flex-grow-1 row bg-really-light pt-3 border-top">
                <div class="col">
                    <h3 class="mb-3 font-size-lg">
                        <?= Icon::show('i8:chat', ['class' => 'text-primary mr-2 icon icons8-size']) . Yii::t('app', 'Discussion') ?>
                    </h3>

                    <?= StaffCommentWidget::widget([
                        'relatedModel' => 'lead',
                        'relatedModelId' => $model->id,
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="border-left bg-really-light content-sidebar lead-view-sidebar overflow-auto mh-100">
            <?= $this->block('@sidebar:begin') ?>

            <?= $this->render('@modules/note/views/admin/note/components/container', [
                'configurations' => [
                    'id' => 'lead-note',
                    'model' => 'lead',
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
    </div>


<?php
$jsOptions = Json::encode([
    'inviteUrl' => Url::to(['/crm/admin/lead/assign', 'id' => $model->id]),
]);

$this->registerJs("$('#lead-view-wrapper-{$this->uniqueId}').leadView({$jsOptions})");


echo $this->block('@end');
$this->endContent();
