<?php

use modules\account\web\admin\View;
use modules\file_manager\helpers\ImageVersion;
use modules\support\assets\admin\TicketViewAsset;
use modules\support\models\forms\ticket_reply\TicketReplySearch;
use modules\support\models\Ticket;
use modules\support\models\TicketReply;
use modules\support\widgets\inputs\TicketPriorityDropdown;
use modules\support\widgets\inputs\TicketStatusDropdown;
use modules\ui\widgets\Card;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * @var View               $this
 * @var Ticket             $model
 * @var TicketReply        $replyModel
 * @var TicketReplySearch  $replySearchModel
 * @var ActiveDataProvider $replyDataProvider
 */

TicketViewAsset::register($this);

if (Yii::$app->user->can('admin.ticket.add')) {
    $this->toolbar['delete-ticket'] = Html::a([
        'url' => ['/support/admin/ticket/delete', 'id' => $model->id],
        'class' => 'btn btn-outline-danger btn-icon',
        'icon' => 'i8:trash',
        'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure', [
            'object_name' => Html::tag('strong', $model->subject),
        ]),
        'data-placement' => 'bottom',
        'title' => Yii::t('app', 'Delete'),
        'data-lazy-options' => ['method' => 'DELETE'],
    ]);
}

if (Yii::$app->user->can('admin.ticket.add')) {
    $this->toolbar['update-ticket'] = Html::a([
        'url' => ['/support/admin/ticket/update', 'id' => $model->id],
        'label' => Yii::t('app', 'Update'),
        'class' => 'btn btn-outline-secondary',
        'icon' => 'i8:edit',
        'data-lazy-modal' => 'task-form-modal',
        'data-lazy-container' => '#main-container',
        'data-lazy-modal-size' => 'modal-md',
    ]);
}

$this->beginContent('@modules/support/views/admin/ticket/components/view-layout.php', compact('model'));

echo $this->block('@begin');

?>
    <div class="d-flex h-100">
        <div class="container-fluid h-100 py-3 overflow-auto container" id="ticket-view-wrapper">
            <div class="row border-bottom">
                <div class="col-md-5">
                    <?php Card::begin([
                        'title' => Yii::t('app', 'Ticket Detail'),
                        'icon' => 'i8:two-tickets',
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

                    <table class="table m-0">
                        <tr>
                            <th class="border-top-0 px-0 pt-0"><?= Yii::t('app', 'Subject') ?></th>
                            <td class="text-right border-top-0 px-0 pt-0"><?= Html::encode($model->subject) ?></td>
                        </tr>

                        <tr>
                            <th class="px-0"><?= Yii::t('app', 'Department') ?></th>
                            <td class="text-right px-0">
                                <?= Html::encode($model->department->name); ?>
                            </td>
                        </tr>

                        <tr>
                            <th class="px-0"><?= Yii::t('app', 'Customer') ?></th>
                            <td class="text-right px-0">
                                <?= Html::a(Html::encode($model->contact->customer->name), ['/crm/admin/customer/view', 'id' => $model->contact->customer_id], [
                                    'data-lazy-container' => '#main-container',
                                    'data-lazy-modal' => 'customer-view-modal',
                                ]) ?>
                                <div class="font-size-sm">
                                    <?= Html::encode($model->contact->name) ?>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th class="px-0"><?= Yii::t('app', 'Status') ?></th>
                            <td class="text-right px-0">
                                <?= TicketStatusDropdown::widget([
                                    'value' => $model->status_id,
                                    'url' => function ($status) use ($model) {
                                        return ['/support/admin/ticket/change-status', 'status' => $status['id'], 'id' => $model->id];
                                    },
                                ]) ?>
                            </td>
                        </tr>

                        <tr>
                            <th class="px-0"><?= Yii::t('app', 'Priority') ?></th>
                            <td class="text-right px-0">
                                <?= TicketPriorityDropdown::widget([
                                    'value' => $model->priority_id,
                                    'url' => function ($status) use ($model) {
                                        return ['/support/admin/ticket/change-priority', 'priority' => $status['id'], 'id' => $model->id];
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
                    </table>

                    <?php Card::end(); ?>
                </div>
                <div class="col-md-7">
                    <?php Card::begin([
                        'title' => Yii::t('app', 'Ticket Content'),
                        'icon' => 'i8:file',
                        'options' => [
                            'class' => 'sticky-top card border-bottom-0',
                        ],
                        'bodyOptions' => [
                            'class' => 'card-body px-0',
                        ],
                        'headerOptions' => [
                            'class' => 'card-header px-0',
                        ],
                    ]); ?>
                    <?= Yii::$app->formatter->asHtml($model->content) ?>

                    <?php if ($model->attachments): ?>
                        <div class="attachments mt-3">
                            <?php foreach ($model->attachments AS $attachment): ?>
                                <?php
                                $metaData = $attachment->getFileMetaData('file');
                                $extension = explode('/', $metaData['type'], 2);
                                ?>
                                <a href="<?= $metaData['url'] ?>" target="_blank" data-lazy="0" class="attachment bg-really-light shadow-sm" data-toggle="tooltip" title="<?= Html::encode($metaData['name']) ?>">
                                    <div class="attachment-preview">
                                        <?php
                                        if (ImageVersion::isImage($attachment->getFilePath('file'))) {
                                            echo Html::img($metaData['src']);
                                        } else {
                                            echo Html::tag('div', Html::tag('div', end($extension)), [
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

                    <?php Card::end(); ?>
                </div>
            </div>

            <div class="bg-really-light pt-3 row">
                <div class="col-12">
                    <div class="ticket-reply-form-wrapper">
                        <?= $this->render('/admin/ticket-reply/components/form', [
                            'model' => $replyModel,
                        ]); ?>
                    </div>

                    <div class="ticket-replies">
                        <?= $this->render('/admin/ticket-reply/components/data-list', [
                            'dataProvider' => $replyDataProvider,
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-left bg-really-light content-sidebar ticket-view-sidebar h-100 overflow-auto">
            <?= $this->render('@modules/note/views/admin/note/components/container', [
                'configurations' => [
                    'id' => 'ticket-note',
                    'model' => 'ticket',
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

$jsOptions = Json::encode([]);

$this->registerJs("$('#ticket-view-wrapper').ticketView({$jsOptions})");

echo $this->block('@end');

$this->endContent();
