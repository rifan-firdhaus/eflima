<?php

use modules\account\web\admin\View;
use modules\account\widgets\StaffCommentWidget;
use modules\finance\models\InvoicePayment;
use modules\ui\widgets\Card;
use modules\ui\widgets\Icon;
use yii\helpers\Html;

/**
 * @var View           $this
 * @var InvoicePayment $model
 */

$this->title = $model->number;
$this->icon = 'i8:receive-cash';
$this->fullHeightContent = true;

echo $this->block('@begin');
?>

    <div class="d-flex h-100">
        <div id="invoice-payment-view-wrapper-<?= $this->uniqueId; ?>" class="task-view-wrapper h-100 py-3 w-100 overflow-auto container-fluid">

            <div class="row">
                <div class="col-12">
                    <?php Card::begin([
                        'bodyOptions' => false,
                    ])
                    ?>

                    <table class="table mb-0 table-detail-view">
                        <tr>
                            <th class="border-top-0"><?= Yii::t('app', 'Number') ?></th>
                            <td class="border-top-0"><?= Html::encode($model->number) ?></td>
                        </tr>

                        <tr>
                            <th><?= Yii::t('app', 'Received from') ?></th>
                            <td>
                                <?= Html::a(Html::encode($model->invoice->customer->name), ['/crm/admin/customer/view', 'id' => $model->invoice->customer_id], [
                                    'data-lazy-container' => '#main-container',
                                    'data-lazy-modal' => 'customer-view-modal',
                                ]) ?>
                                <div class="font-size-sm">
                                    <?= Html::encode($model->invoice->customer->primaryContact->name) ?>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th><?= Yii::t('app', 'Invoice') ?></th>
                            <td>
                                <?= Html::a(Html::encode($model->invoice->number), ['/finance/admin/invoice/view', 'id' => $model->invoice->id], [
                                    'data-lazy-container' => '#main-container',
                                    'data-lazy-modal' => 'invoice-view-modal',
                                ]) ?>
                                <div class="font-size-sm">
                                    <?= Yii::$app->formatter->asDatetime($model->invoice->date) ?>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th><?= Yii::t('app', 'Payment Method') ?></th>
                            <td><?= Html::encode($model->method->label) ?></td>
                        </tr>

                        <tr>
                            <th><?= Yii::t('app', 'Paid at') ?></th>
                            <td>
                                <?= Yii::$app->formatter->asDatetime($model->at) ?>
                                <div class="font-size-sm">
                                    <?= Yii::$app->formatter->asRelativeTime($model->at) ?>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th><?= Yii::t('app', 'Accepted at') ?></th>
                            <td>
                                <?= Yii::$app->formatter->asDatetime($model->accepted_at) ?>
                                <div class="font-size-sm">
                                    <?= Yii::$app->formatter->asRelativeTime($model->accepted_at) ?>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <div class="bg-soft-warning p-3 my-3 rounded-lg border-lg overflow-hidden w-100">
                        <table class="table mb-0 table-detail-view">
                            <tr>
                                <th class="font-size-lg"><?= Yii::t('app', 'Amount') ?></th>
                                <td class="font-size-lg text-right text-primary">
                                    <?= Yii::$app->formatter->asCurrency($model->amount, $model->invoice->currency_code) ?>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <?php Card::end(); ?>
                </div>
            </div>

            <div class="event-comment row bg-really-light py-3 border-top">
                <div class="col-md-12">
                    <h3 class="mb-3 font-size-lg">
                        <?= Icon::show('i8:chat', ['class' => 'text-primary mr-2 icon icons8-size']) . Yii::t('app', 'Discussion') ?>
                    </h3>

                    <?= StaffCommentWidget::widget([
                        'relatedModel' => 'invoice_payment',
                        'relatedModelId' => $model->id,
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="border-left bg-really-light content-sidebar invoice-payment-view-sidebar h-100 overflow-auto">
            <?= $this->render('@modules/note/views/admin/note/components/container', [
                'configurations' => [
                    'id' => 'invoice-payment-note',
                    'model' => 'invoice_payment',
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
<?= $this->block('@end'); ?>