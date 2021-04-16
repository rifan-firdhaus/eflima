<?php

use modules\account\web\admin\View;
use modules\crm\models\LeadStatus;
use modules\ui\widgets\Icon;
use modules\ui\widgets\lazy\Lazy;
use yii\bootstrap4\ButtonDropdown;
use yii\helpers\Html;

/**
 * @var View       $this
 * @var LeadStatus $model
 */

?>

<div class="lead-status-kanban-item  h-100 d-flex flex-column" data-id="<?= $model->id ?>">
    <div class="lead-status-kanban-item-header px-3 py-2 align-items-center flex-shrink-0 flex-grow-0 d-flex" style="background: <?= Html::encode($model->color_label); ?>;color:<?= Html::colorContrast($model->color_label) ?>">
        <div class="handle d-flex align-items-center"><?= Icon::show('i8:move', ['class' => 'icon icons8-size']) ?></div>
        <?= Html::a($model->label, ['/crm/admin/lead-status/update', 'id' => $model->id], [
            'class' => 'lead-status-kanban-item-title',
        ]) ?>
        <div class="ml-auto">
            <?= ButtonDropdown::widget([
                'label' => Icon::show('i8:double-down', ['class' => 'icon icons8-size']),
                'buttonOptions' => [
                    'class' => ['btn btn-link btn-menu btn-icon px-0', 'toggle' => ''],
                    'data-lazy-container' => '#main-container',
                    'data-lazy-modal-size' => 'modal-md',
                ],
                'encodeLabel' => false,
                'dropdown' => [
                    'items' => [
                        [
                            'encode' => false,
                            'label' => Icon::show('i8:plus', ['class' => 'icon mr-2']) . Yii::t('app', 'Add Lead'),
                            'url' => ['/crm/admin/lead/add', 'status_id' => $model->id],
                            'linkOptions' => [
                                'data-lazy-modal' => 'lead-form',
                                'data-lazy-container' => '#main-container',
                            ],
                        ],
                        [
                            'encode' => false,
                            'label' => Icon::show('i8:edit', ['class' => 'icon mr-2']) . Yii::t('app', 'Update'),
                            'url' => ['/crm/admin/lead-status/update', 'id' => $model->id],
                            'linkOptions' => [
                                'data-lazy-modal' => 'lead-status-form',
                                'data-lazy-container' => '#main-container',
                                'data-lazy-modal-size' => 'modal-md',
                            ],
                        ],
                        [
                            'encode' => false,
                            'label' => Icon::show('i8:trash', ['class' => 'icon mr-2']) . Yii::t('app', 'Delete'),
                            'url' => ['/crm/admin/lead-status/update', 'id' => $model->id],
                            'linkOptions' => [
                                'class' => 'text-danger',
                                'title' => Yii::t('app', 'Delete'),
                                'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure', [
                                    'object_name' => $model->label,
                                ]),
                            ],
                        ],
                    ],
                ],
            ]) ?>
        </div>
    </div>
    <div class="lead-status-kanban-item-content h-100 overflow-hidden d-flex flex-column">
        <?php
        Lazy::begin([
            'id' => "lead-status-kanban-items-{$model->id}",
            'options' => [
                    'class' => ' h-100 overflow-auto p-2'
            ]
        ]);?>

        <a href="#" class="btn btn-outline-primary btn-block btn-load-more"><?= Yii::t('app', 'Load More'); ?></a>
        <?php
        Lazy::end();
        ?>
    </div>
</div>
