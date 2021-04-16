<?php

use modules\account\web\admin\View;
use modules\crm\models\Customer;
use modules\task\models\forms\task\TaskSearch;
use modules\task\models\forms\task_timer\TaskTimerSearch;
use modules\ui\widgets\Card;
use modules\ui\widgets\Icon;
use yii\bootstrap4\ButtonDropdown;
use yii\helpers\Html;

/**
 * @var View            $this
 * @var Customer        $model
 * @var TaskSearch      $taskSearchModel
 * @var TaskTimerSearch $taskTimerSearchModel
 */

$this->beginContent('@modules/crm/views/admin/customer/components/view-layout.php', compact('model'));

echo $this->block('@begin');

if (Yii::$app->user->can('admin.customer.delete')) {
    $this->toolbar['delete-customer'] = Html::a([
        'url' => ['/crm/admin/customer/delete', 'id' => $model->id],
        'class' => 'btn btn-outline-danger btn-icon',
        'icon' => 'i8:trash',
        'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure', [
            'object_name' => Html::tag('strong', $model->name),
        ]),
        'data-placement' => 'bottom',
        'title' => Yii::t('app', 'Delete'),
    ]);
}

if (Yii::$app->user->can('admin.customer.update')) {
    $this->toolbar['update-customer'] = Html::a([
        'label' => Yii::t('app', 'Update'),
        'url' => ['/crm/admin/customer/update', 'id' => $model->id],
        'class' => 'btn btn-outline-secondary',
        'icon' => 'i8:edit',
        'data-lazy-modal' => 'customer-form-modal',
        'data-lazy-container' => '#main-container',
    ]);
}

$this->toolbar['customer-more'] = ButtonDropdown::widget([
    'label' => Icon::show('i8:double-down'),
    'encodeLabel' => false,
    'id' => 'customer-more-action',
    'buttonOptions' => [
        'class' => ['btn btn-outline-secondary btn-icon dropdown-toggle-none'],
    ],
    'dropdown' => [
        'encodeLabels' => false,
        'items' => [
            [
                'label' => Icon::show('i8:checked', ['class' => 'icon icons8-size mr-2']) . Yii::t('app', 'Add {object}', [
                        'object' => Yii::t('app', 'Task'),
                    ]),
                'url' => ['/task/admin/task/add', 'model' => 'customer', 'model_id' => $model->id],
                'linkOptions' => [
                    'data-lazy-container' => '#main-container',
                    'data-lazy-modal' => 'task-form-modal',
                ],
                'visible' => Yii::$app->user->can('admin.customer.view.task')
            ],
            [
                'label' => Icon::show('i8:address-book', ['class' => 'icon icons8-size mr-2']) . Yii::t('app', 'Add {object}', [
                        'object' => Yii::t('app', 'Contact'),
                    ]),
                'url' => ['/crm/admin/customer-contact/add', 'customer_id' => $model->id],
                'linkOptions' => [
                    'data-lazy-container' => '#main-container',
                    'data-lazy-modal' => 'customer-contact-form-modal',
                ],
                'visible' => Yii::$app->user->can('admin.customer.view.contact')
            ],
            [
                'label' => Icon::show('i8:event', ['class' => 'icon icons8-size mr-2']) . Yii::t('app', 'Add {object}', [
                        'object' => Yii::t('app', 'Event'),
                    ]),
                'url' => ['/calendar/admin/event/add', 'model' => 'customer', 'model_id' => $model->id],
                'linkOptions' => [
                    'data-lazy-container' => '#main-container',
                    'data-lazy-modal' => 'event-form-modal',
                    'data-lazy-modal-size' => 'modal-lg',
                ],
                'visible' => Yii::$app->user->can('admin.customer.view.event')
            ],
        ],
    ],
]);
?>

    <div class="d-flex h-100">
        <div class="overflow-auto py-3 w-100 container-fluid mh-100">
            <div class="d-flex row border-bottom">
                <?= $this->block('@main:begin') ?>

                <div class="col-md-6">
                    <?= $this->block('@main/left:begin') ?>

                    <?php Card::begin([
                        'title' => Yii::t('app', 'Company Detail'),
                        'icon' => 'i8:contacts',
                        'options' => [
                            'class' => 'card border-bottom-0',
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
                            <th><?= Yii::t('app', 'Type') ?></th>
                            <td>
                                <?= $model->typeText ?>
                            </td>
                        </tr>

                        <tr>
                            <th><?= Yii::t('app', 'Group') ?></th>
                            <td>
                                <?php if ($model->group_id): ?>
                                    <span class="color-description" style="background: <?= Html::encode($model->group->color_label) ?>"></span>
                                    <?= Html::encode($model->group->name) ?>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <tr>
                            <th><?= Yii::t('app', 'Phone`') ?></th>
                            <td><?= Html::encode($model->phone) ?></td>
                        </tr>

                        <tr>
                            <th><?= Yii::t('app', 'Fax`') ?></th>
                            <td><?= Html::encode($model->fax) ?></td>
                        </tr>

                        <tr>
                            <th><?= Yii::t('app', 'Email`') ?></th>
                            <td><?= Yii::$app->formatter->asEmail($model->email) ?></td>
                        </tr>

                        <tr>
                            <th><?= Yii::t('app', 'Address`') ?></th>
                            <td><?= Html::encode($model->fullAddress) ?></td>
                        </tr>

                        <tr>
                            <th><?= Yii::t('app', 'VAT Number`') ?></th>
                            <td><?= Html::encode($model->vat_number) ?></td>
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

                <div class="col-md-6">
                    <?= $this->block('@main/right:begin') ?>

                    <?php Card::begin([
                        'title' => Yii::t('app', 'Personal Detail'),
                        'icon' => 'i8:address-book',
                        'options' => [
                            'class' => 'card border-bottom-0',
                        ],
                        'bodyOptions' => [
                            'class' => 'card-body px-0',
                        ],
                        'headerOptions' => [
                            'class' => 'card-header px-0',
                        ],
                    ]); ?>
                    <table class="table-detail-view table m-0">
                        <?= $this->block('@personal_detail:end') ?>

                        <tr>
                            <th class="border-top-0 px-0"><?= Yii::t('app', 'Name') ?></th>
                            <td class="border-top-0 pt-0"><?= Html::encode($model->primaryContact->name) ?></td>
                        </tr>

                        <tr>
                            <th><?= Yii::t('app', 'Phone`') ?></th>
                            <td><?= Html::encode($model->primaryContact->phone) ?></td>
                        </tr>

                        <tr>
                            <th><?= Yii::t('app', 'Fax`') ?></th>
                            <td><?= Html::encode($model->fax) ?></td>
                        </tr>

                        <tr>
                            <th><?= Yii::t('app', 'Email`') ?></th>
                            <td><?= Yii::$app->formatter->asEmail($model->primaryContact->email) ?></td>
                        </tr>

                        <tr>
                            <th><?= Yii::t('app', 'Address`') ?></th>
                            <td><?= Html::encode($model->primaryContact->fullAddress) ?></td>
                        </tr>

                        <tr>
                            <th><?= Yii::t('app', 'Created at') ?></th>
                            <td>
                                <?= Yii::$app->formatter->asDatetime($model->primaryContact->created_at) ?>
                                <div class="font-size-sm">
                                    <?= Yii::$app->formatter->asRelativeTime($model->primaryContact->created_at) ?>
                                </div>
                            </td>
                        </tr>

                        <?= $this->block('@personal_detail:end') ?>
                    </table>

                    <?php Card::end(); ?>

                    <?= $this->block('@main/right:end') ?>
                </div>

                <?= $this->block('@main:end') ?>
            </div>

            <div class="bg-really-light pt-3 row">
                <?= $this->block('@summary:begin') ?>

                <div class="col-12">
                    <?php
                    $taskCard = Card::begin([
                        'title' => Yii::t('app', 'Task Overview'),
                        'icon' => 'i8:checked',
                        'bodyOptions' => false,
                        'options' => [
                            'class' => 'card mb-3 border border-bottom-0 rounded shadow-sm overflow-hidden',
                        ],
                    ]);

                    $taskCard->addToHeader(Html::a([
                        'url' => ['/crm/admin/customer/view', 'id' => $model->id, 'action' => 'task'],
                        'label' => Yii::t('app', 'See More'),
                        'icon' => 'i8:double-right',
                        'class' => 'btn btn-light btn-sm',
                    ]));

                    echo $this->render('@modules/task/views/admin/task/components/data-statistic', [
                        'searchModel' => $taskSearchModel,
                        'searchAction' => ['/crm/admin/customer/view', 'id' => $model->id, 'action' => 'task'],
                    ]);

                    Card::end();

                    $taskTimerCard = Card::begin([
                        'title' => Yii::t('app', 'Timesheet Overview'),
                        'icon' => 'i8:timer',
                        'bodyOptions' => false,
                        'options' => [
                            'class' => 'card borderd mb-3 border-bottom-0 rounded shadow-sm overflow-hidden',
                        ],
                    ]);

                    $taskTimerCard->addToHeader(Html::a([
                        'url' => ['/crm/admin/customer/view', 'id' => $model->id, 'action' => 'task-timer'],
                        'label' => Yii::t('app', 'See More'),
                        'icon' => 'i8:double-right',
                        'class' => 'btn btn-light btn-sm',
                    ]));

                    echo $this->render('@modules/task/views/admin/task-timer/components/data-statistic', [
                        'searchModel' => $taskTimerSearchModel,
                        'searchAction' => ['/crm/admin/customer/view', 'id' => $model->id, 'action' => 'task-timer'],
                    ]);

                    Card::end();
                    ?>
                </div>

                <?= $this->block('@summary:end') ?>
            </div>

        </div>

        <div class="border-left bg-really-light content-sidebar d-none d-sm-block  customer-view-sidebar overflow-auto mh-100">
            <?= $this->block('@sidebar:begin') ?>

            <?= $this->render('@modules/note/views/admin/note/components/container', [
                'configurations' => [
                    'id' => 'customer-note',
                    'model' => 'customer',
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
echo $this->block('@end');

$this->endContent();
