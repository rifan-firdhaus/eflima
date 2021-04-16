<?php

use modules\account\web\admin\View;
use modules\crm\models\Customer;
use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var View     $this
 * @var Customer $model
 */
$isCompany = $model->type === Customer::TYPE_COMPANY;
?>

<div class="w-50 mb-3">
    <div class="quick-search-result-item h-100 d-flex flex-column mb-0 w-auto">
        <div class="header border-bottom mb-2 d-flex justify-content-between">
            <div class="mb-2">
                <?= Html::a($isCompany ? $model->company_name : $model->name, ['/crm/admin/customer/view', 'id' => $model->id], [
                    "data-quick-search-close" => true,
                    "data-lazy-modal" => "customer-view-modal",
                    "data-lazy-container" => "#main-container",
                    "class" => "title d-block font-size-lg ",
                ]) ?>

            <?php
            if ($isCompany) {
                echo Html::a($model->name, ['/crm/admin/customer/view', 'id' => $model->id], [
                    "data-quick-search-close" => true,
                    "data-lazy-modal" => "customer-view-modal",
                    "data-lazy-container" => "#main-container",
                    "class" => "d-block font-size-sm ",
                ]);
            }
            ?>
            </div>
            <div class="meta text-nowrap">
                <?php
                if ($model->group_id) {
                    echo Html::tag('span', Html::encode($model->group->name), [
                        'class' => "badge badge-clean text-uppercase ml-2 px-3 py-2",
                        'style' => [
                            'background' => Html::hex2rgba($model->group->color_label, 0.1),
                            'color' => $model->group->color_label,
                        ],
                    ]);
                }
                ?>
            </div>
        </div>

        <div class="content flex-grow-1 justify-content-between">
            <?= DetailView::widget([
                'model' => $model,
                'options' => [
                    'class' => 'table table-borderless table-sm',
                ],
                'attributes' => [
                    [
                        'attribute' => 'phone',
                    ],
                    [
                        'attribute' => 'email',
                        'format' => 'email',
                    ],
                    [
                        'attribute' => 'created_at',
                        'format' => 'datetime',
                    ],
                ],
            ]); ?>
        </div>


    </div>
</div>
