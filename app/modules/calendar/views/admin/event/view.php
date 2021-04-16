<?php

use modules\account\web\admin\View;
use modules\account\widgets\inputs\StaffInput;
use modules\account\widgets\StaffCommentWidget;
use modules\calendar\assets\admin\EventViewAsset;
use modules\calendar\models\Event;
use modules\calendar\models\EventMember;
use modules\ui\widgets\Card;
use modules\ui\widgets\data_table\columns\ActionColumn;
use modules\ui\widgets\data_table\columns\DateColumn;
use modules\ui\widgets\data_table\DataTable;
use modules\ui\widgets\Icon;
use modules\ui\widgets\table\cells\Cell;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * @var View  $this
 * @var Event $model
 */

EventViewAsset::register($this);

$this->title = $model->name;
$this->menu->active = 'main/calendar';
$this->icon = 'i8:event';
$this->fullHeightContent = true;

if (Yii::$app->user->can('admin.event.delete')) {
    $this->toolbar['delete-event'] = Html::a([
        'url' => ['/calendar/admin/event/delete', 'id' => $model->id],
        'class' => 'btn btn-outline-danger btn-icon',
        'icon' => 'i8:trash',
        'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure?', [
            'object_name' => Html::tag('strong', $model->name),
        ]),
        'data-placement' => 'bottom',
        'title' => Yii::t('app', 'Delete'),
        'data-toggle' => 'tooltip',
        'data-lazy-options' => ['method' => 'DELETE'],
    ]);
}

if (Yii::$app->user->can('admin.event.update')) {
    $this->toolbar['update-event'] = Html::a([
        'label' => Yii::t('app', 'Update'),
        'url' => ['/calendar/admin/event/update', 'id' => $model->id],
        'class' => 'btn btn-outline-secondary',
        'icon' => 'i8:edit',
        'data-lazy-modal' => 'event-form-modal',
        'data-lazy-container' => '#main-container',
        'data-lazy-modal-size' => 'modal-md',
    ]);
}
?>
<div class="d-flex h-100">
    <div id="event-view-wrapper-<?= $this->uniqueId; ?>" class="pt-3 event-view-wrapper mh-100 w-100 overflow-auto container-fluid">

        <table class="table table-detail-view">

            <tr>
                <th class="border-top-0"><?= Yii::t('app', 'Event Name') ?></th>
                <td class="border-top-0"><?= Html::encode($model->name) ?></td>
            </tr>

            <tr>
                <th><?= Yii::t('app', 'Description') ?></th>
                <td>
                    <?= Yii::$app->formatter->asHtml($model->description) ?>
                </td>
            </tr>

            <tr>
                <th><?= Yii::t('app', 'Start Date') ?></th>
                <td>
                    <?= Yii::$app->formatter->asDatetime($model->start_date) ?>
                    <div class="font-size-sm">
                        <?= Yii::$app->formatter->asRelativeTime($model->start_date) ?>
                    </div>
                </td>
            </tr>

            <tr>
                <th><?= Yii::t('app', 'End Date') ?></th>
                <td>
                    <?= Yii::$app->formatter->asDatetime($model->end_date) ?>
                    <div class="font-size-sm">
                        <?= Yii::$app->formatter->asRelativeTime($model->end_date) ?>
                    </div>
                </td>
            </tr>

            <tr>
                <th><?= Yii::t('app', 'Location') ?></th>
                <td>
                    <?= Yii::$app->formatter->asHtml($model->location) ?>
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

        </table>
        <?php
        $memberCard = Card::begin([
            'title' => Yii::t('app', 'Attendees'),
            'icon' => 'i8:account',
            'headerOptions' => [
                'class' => 'card-header border-top px-0',
            ],
            'bodyOptions' => false,
        ]);

        echo $this->block('@member:begin');

        if (Yii::$app->user->can('admin.event.update') || Yii::$app->user->can('admin.event.add')) {
            $memberButton = Html::a(Icon::show('i8:paper-plane') . Yii::t('app', 'Invite'), '#', [
                'class' => 'btn btn-outline-primary btn-sm btn-event-member',
            ]);
            $memberInput = StaffInput::widget([
                'name' => 'assignee',
                'url' => ['/calendar/admin/event/staff-invitable-auto-complete', 'id' => $model->id],
                'id' => 'event-member-input',
                'options' => [
                    'class' => 'event-member-input',
                ],
            ]);

            $memberCard->addToHeader(
                Html::tag('div', $memberInput . $memberButton, [
                    'class' => 'event-member-input-container',
                ])
            );
        }

        echo DataTable::widget([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => $model->memberRelationships,
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
                        /** @var EventMember $model */

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
                        /** @var EventMember $model */

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
                    'attribute' => 'created_at',
                    'label' => Yii::t('app', 'Invited at'),
                    'class' => DateColumn::class,
                ],
                [
                    'class' => ActionColumn::class,
                    'controller' => '/calendar/admin/event-member',
                    'buttons' => [
                        'view' => false,
                        'update' => false,
                        'delete' => [
                            'visible' => Yii::$app->user->can('admin.event.update') || Yii::$app->user->can('admin.event.add'),
                            'value' => [
                                'icon' => 'i8:trash',
                                'label' => Yii::t('app', 'Delete'),
                                'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure?', [
                                    'object_name' => Yii::t('app', 'this item'),
                                ]),
                                'class' => 'text-danger',
                                'data-lazy-container' => false,
                                'data-lazy-options' => ['scroll' => false, 'method' => 'DELETE'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        echo $this->block('@member:end');

        Card::end();
        ?>

        <div class="event-comment mt-3 row bg-really-light pt-3 border-top">
            <div class="col-md-12">
                <h3 class="mb-3 font-size-lg">
                    <?= Icon::show('i8:chat', ['class' => 'text-primary mr-2 icon icons8-size']) . Yii::t('app', 'Discussion') ?>
                </h3>

                <?= StaffCommentWidget::widget([
                    'relatedModel' => 'event',
                    'relatedModelId' => $model->id,
                ]) ?>
            </div>
        </div>
    </div>
    <div class="border-left bg-really-light content-sidebar task-view-sidebar mh-100 overflow-auto">
        <?= $this->render('@modules/note/views/admin/note/components/container', [
            'configurations' => [
                'id' => 'event-note',
                'model' => 'event',
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

$jsOptions = Json::encode([
    'inviteUrl' => Url::to(['/calendar/admin/event-member/invite', 'id' => $model->id]),
]);

$this->registerJs("$('#event-view-wrapper-{$this->uniqueId}').eventView({$jsOptions})");
?>
