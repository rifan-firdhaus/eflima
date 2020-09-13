<?php

use modules\account\web\admin\View;
use modules\file_manager\helpers\ImageVersion;
use modules\finance\models\forms\invoice\InvoiceSearch;
use modules\project\models\Project;
use modules\project\models\ProjectMember;
use modules\project\models\ProjectStatus;
use modules\project\widgets\inputs\ProjectStatusDropdown;
use modules\support\models\forms\ticket\TicketSearch;
use modules\task\models\forms\task\TaskSearch;
use modules\task\models\forms\task_timer\TaskTimerSearch;
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

/**
 * @var View            $this
 * @var Project         $model
 * @var TaskSearch      $taskSearchModel
 * @var TaskTimerSearch $taskTimerSearchModel
 * @var InvoiceSearch   $invoiceSearchModel
 * @var TicketSearch    $ticketSearchModel
 */

$this->beginContent('@modules/project/views/admin/project/components/view-layout.php', compact('model'));

echo $this->block('@begin');


$this->toolbar['delete-project'] = Html::a([
    'url' => ['/project/admin/project/delete', 'id' => $model->id],
    'class' => 'btn btn-outline-danger btn-icon',
    'icon' => 'i8:trash',
    'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure', [
        'object_name' => Html::tag('strong', $model->name),
    ]),
    'data-placement' => 'bottom',
    'title' => Yii::t('app', 'Delete'),
]);

$this->toolbar['update-project'] = Html::a([
    'label' => Yii::t('app', 'Update'),
    'url' => ['/project/admin/project/update', 'id' => $model->id],
    'class' => 'btn btn-outline-secondary',
    'icon' => 'i8:edit',
    'data-lazy-modal' => 'customer-form-modal',
    'data-lazy-container' => '#main-container',
]);

$projectActionItems = [
    [
        'label' => Yii::t('app', 'Set status to:'),
    ],
];

foreach (ProjectStatus::find()->enabled()->andWhere(['!=', 'id', $model->status_id])->all() AS $projectStatus) {
    $projectActionItems[] = [
        'label' => Html::tag('span', '', ['class' => 'color-description', 'style' => ['background-color' => $projectStatus->color_label]]) . Html::encode($projectStatus->label),
        'url' => ['/project/admin/project/change-status', 'id' => $model->id, 'status' => $projectStatus->id],
    ];
}

$projectActionItems = ArrayHelper::merge($projectActionItems, [
    '-',
    [
        'label' => Icon::show('i8:checked', ['class' => 'icon icons8-size mr-2']) . Yii::t('app', 'Add {object}', [
                'object' => Yii::t('app', 'Task'),
            ]),
        'url' => ['/task/admin/task/add', 'model' => 'project', 'model_id' => $model->id],
        'linkOptions' => [
            'data-lazy-container' => '#main-container',
            'data-lazy-modal' => 'task-form-modal',
        ],
    ],
    [
        'label' => Icon::show('i8:cash', ['class' => 'icon icons8-size mr-2']) . Yii::t('app', 'Add {object}', [
                'object' => Yii::t('app', 'Invoice'),
            ]),
        'url' => ['/finance/admin/invoice/add', 'customer_id' => $model->customer_id, 'project_id' => $model->id],
        'linkOptions' => [
            'data-lazy-container' => '#main-container',
            'data-lazy-modal' => 'invoice-form-modal',
        ],
    ],
    [
        'label' => Icon::show('i8:receive-cash', ['class' => 'icon icons8-size mr-2']) . Yii::t('app', 'Add {object}', [
                'object' => Yii::t('app', 'Payment'),
            ]),
        'url' => ['/finance/admin/invoice-payment/add', 'customer_id' => $model->customer_id, 'project_id' => $model->id],
        'linkOptions' => [
            'data-lazy-container' => '#main-container',
            'data-lazy-modal' => 'expense-form-modal',
        ],
    ],
    [
        'label' => Icon::show('i8:cash', ['class' => 'icon icons8-size mr-2']) . Yii::t('app', 'Add {object}', [
                'object' => Yii::t('app', 'Expense'),
            ]),
        'url' => ['/finance/admin/expense/add', 'customer_id' => $model->customer_id, 'project_id' => $model->id],
        'linkOptions' => [
            'data-lazy-container' => '#main-container',
            'data-lazy-modal' => 'expense-form-modal',
        ],
    ],
    [
        'label' => Icon::show('i8:two-tickets', ['class' => 'icon icons8-size mr-2']) . Yii::t('app', 'Add {object}', [
                'object' => Yii::t('app', 'Ticket'),
            ]),
        'url' => ['/support/admin/ticket/add', 'contact_id' => $model->customer->primaryContact->id, 'project_id' => $model->id],
        'linkOptions' => [
            'data-lazy-container' => '#main-container',
            'data-lazy-modal' => 'ticket-form-modal',
            'data-lazy-modal-size' => 'dialog-md',
        ],
    ],
]);

$this->toolbar['project-more'] = ButtonDropdown::widget([
    'label' => Icon::show('i8:double-down'),
    'encodeLabel' => false,
    'buttonOptions' => [
        'class' => ['btn btn-outline-secondary btn-icon', 'toggle' => ''],
    ],
    'dropdown' => [
        'encodeLabels' => false,
        'items' => $projectActionItems,
    ],
]);
?>

    <div class="d-flex h-100">
        <div class="overflow-auto py-3 w-100 container-fluid mh-100">
            <div class="d-flex row border-bottom">
                <div class="col-md-5">

                    <?php Card::begin([
                        'title' => Yii::t('app', 'Project Detail'),
                        'icon' => 'i8:idea',
                        'options' => [
                            'class' => 'card sticky-top  border-bottom-0',
                        ],
                        'bodyOptions' => [
                            'class' => 'card-body px-0',
                        ],
                        'headerOptions' => [
                            'class' => 'card-header px-0',
                        ],
                    ]); ?>
                    <table class="flex-shrink-0 table m-0">
                        <tr>
                            <th class="border-top-0 px-0 pt-0"><?= Yii::t('app', 'Name') ?></th>
                            <td class="text-right border-top-0 px-0 pt-0"><?= Html::encode($model->name) ?></td>
                        </tr>

                        <tr>
                            <th class="px-0"><?= Yii::t('app', 'Customer') ?></th>
                            <td class="text-right px-0">
                                <?= Html::a(Html::encode($model->customer->name), ['/crm/admin/customer/view', 'id' => $model->customer_id], [
                                    'data-lazy-container' => '#main-container',
                                    'data-lazy-modal' => 'customer-view-modal',
                                ]) ?>
                                <div class="font-size-sm">
                                    <?= Html::encode($model->customer->primaryContact->name) ?>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th class="px-0"><?= Yii::t('app', 'Status') ?></th>
                            <td class="text-right px-0">
                                <?= ProjectStatusDropdown::widget([
                                    'value' => $model->status_id,
                                    'url' => function ($status) use ($model) {
                                        return ['/project/admin/project/change-status', 'status' => $status['id'], 'id' => $model->id];
                                    },
                                ]) ?>
                            </td>
                        </tr>

                        <tr>
                            <th class="px-0"><?= Yii::t('app', 'Created at') ?></th>
                            <td class="text-right px-0">
                                <?= Yii::$app->formatter->asDatetime($model->created_at) ?>
                                <div class="font-size-sm">
                                    <?= Yii::$app->formatter->asRelativeTime($model->created_at) ?>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th class="px-0"><?= Yii::t('app', 'Start Date') ?></th>
                            <td class="text-right px-0">
                                <?= Yii::$app->formatter->asDatetime($model->started_date) ?>
                                <div class="font-size-sm">
                                    <?= Yii::$app->formatter->asRelativeTime($model->started_date) ?>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th class="px-0"><?= Yii::t('app', 'Deadline') ?></th>
                            <td class="text-right px-0">
                                <?= Yii::$app->formatter->asDatetime($model->started_date) ?>
                                <div class="font-size-sm">
                                    <?= Yii::$app->formatter->asRelativeTime($model->deadline_date) ?>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th class="px-0"><?= Yii::t('app', 'Budget') ?></th>
                            <td class="text-right px-0"><?= Yii::$app->formatter->asCurrency($model->budget, $model->customer->currency_code) ?></td>
                        </tr>
                    </table>
                    <?php Card::end(); ?>
                </div>

                <div class="col-md-7">
                    <?php Card::begin([
                        'title' => Yii::t('app', 'Project Description'),
                        'icon' => 'i8:file',
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

                    <?= Yii::$app->formatter->asHtml($model->description) ?>

                    <?php if ($model->attachments): ?>
                        <div class="attachments mt-3">
                            <?php foreach ($model->attachments AS $attachment): ?>
                                <?php
                                $metaData = $attachment->getFileMetaData('file');
                                ?>
                                <a href="<?= $metaData['url'] ?>" target="_blank" data-lazy="0" class="attachment bg-really-light shadow-sm" data-toggle="tooltip" title="<?= Html::encode($metaData['name']) ?>">
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
                        </div>
                    <?php endif; ?>

                    <?php
                    Card::begin([
                        'title' => Yii::t('app', 'Members'),
                        'icon' => 'i8:account',
                        'headerOptions' => [
                            'class' => 'card-header px-0 border-top',
                        ],
                        'bodyOptions' => false,
                    ]);

                    echo $this->block('@members:begin');

                    echo DataTable::widget([
                        'dataProvider' => new ArrayDataProvider([
                            'allModels' => $model->membersRelationship,
                            'pagination' => false,
                        ]),
                        'id' => 'project-member-list',
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
                                    /** @var ProjectMember $model */

                                    return Html::img($model->staff->account->getFileVersionUrl('avatar', 'thumbnail', Yii::getAlias('@web/public/img/avatar.png')), [
                                        'class' => 'w-100 rounded-circle',
                                    ]);
                                },
                            ],
                            [
                                'attribute' => 'staff.name',
                                'label' => Yii::t('app', 'Staff'),
                                'format' => 'raw',
                                'content' => function ($model) {
                                    /** @var ProjectMember $model */

                                    $name = Html::a([
                                        'label' => Html::encode($model->staff->name),
                                        'url' => ['/account/admin/staff/update', 'id' => $model->id],
                                        'class' => 'data-table-main-text',
                                        'data-lazy-modal' => 'staff-form-modal',
                                        'data-lazy-container' => '#main-container',
                                    ]);

                                    $username = Html::tag(
                                        'div',
                                        Html::encode($model->staff->account->username),
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
                                'controller' => '/project/admin/project-member',
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

                    echo $this->block('@members:begin');

                    Card::end();
                    ?>

                    <?php Card::end(); ?>
                </div>
            </div>

            <div class="row pt-3 bg-really-light">
                <div class="col-12">
                    <?php
                    Card::begin([
                        'title' => Yii::t('app', 'Invoice Overview'),
                        'icon' => 'i8:cash',
                        'bodyOptions' => false,
                        'options' => [
                            'class' => 'card border mb-3 border-bottom-0 rounded shadow-sm overflow-hidden',
                        ],
                        'headerOptions' => [
                            'class' => 'card-header border-bottom',
                        ],
                    ]);
                    echo $this->render('@modules/finance/views/admin/invoice/components/data-payment-statistic', [
                        'searchModel' => $invoiceSearchModel,
                        'searchAction' => ['/project/admin/project/view', 'id' => $model->id, 'action' => 'invoice'],
                    ]);
                    Card::end();

                    Card::begin([
                        'title' => Yii::t('app', 'Task Overview'),
                        'icon' => 'i8:checked',
                        'bodyOptions' => false,
                        'options' => [
                            'class' => 'card mb-3 border border-bottom-0 rounded shadow-sm overflow-hidden',
                        ],
                    ]);
                    echo $this->render('@modules/task/views/admin/task/components/data-statistic', [
                        'searchModel' => $taskSearchModel,
                        'searchAction' => ['/project/admin/project/view', 'id' => $model->id, 'action' => 'task'],
                    ]);
                    Card::end();

                    Card::begin([
                        'title' => Yii::t('app', 'Timesheet Overview'),
                        'icon' => 'i8:timer',
                        'bodyOptions' => false,
                        'options' => [
                            'class' => 'card borderd mb-3 border-bottom-0 rounded shadow-sm overflow-hidden',
                        ],
                    ]);
                    echo $this->render('@modules/task/views/admin/task-timer/components/data-statistic', [
                        'searchModel' => $taskTimerSearchModel,
                        'searchAction' => ['/project/admin/project/view', 'id' => $model->id, 'action' => 'task-timer'],
                    ]);
                    Card::end();

                    Card::begin([
                        'title' => Yii::t('app', 'Ticket Overview'),
                        'icon' => 'i8:two-tickets',
                        'bodyOptions' => false,
                        'options' => [
                            'class' => 'card mb-3 border border-bottom-0 rounded shadow-sm overflow-hidden',
                        ],
                    ]);
                    echo $this->render('@modules/support/views/admin/ticket/components/data-statistic', [
                        'searchModel' => $ticketSearchModel,
                        'searchAction' => ['/project/admin/project/view', 'id' => $model->id, 'action' => 'ticket'],
                    ]);
                    Card::end();
                    ?>
                </div>
            </div>
        </div>
        <div class="border-left bg-really-light content-sidebar project-view-sidebar overflow-auto mh-100">
            <?= $this->render('@modules/note/views/admin/note/components/container', [
                'configurations' => [
                    'id' => 'project-note',
                    'model' => 'project',
                    'model_id' => $model->id,
                    'inline' => true,
                    'search' => false,
                    'jsOptions' => [
                        'autoLoad' => true,
                    ],
                ],
            ]) ?>
        </div>
    </div>

<?php
echo $this->block('@end');

$this->endContent();
