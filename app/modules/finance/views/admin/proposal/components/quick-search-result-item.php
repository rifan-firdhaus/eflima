<?php

use modules\account\web\admin\View;
use modules\finance\models\Proposal;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var View     $this
 * @var Proposal $model
 */

?>
<div class="w-50 mb-3">
    <div class="quick-search-result-item h-100 d-flex flex-column mb-0 w-auto">
        <div class="header">

            <div class="title d-block font-size-lg mb-2 d-flex justify-content-between align-items-center">
                <a href="<?= Url::to(['/finance/admin/proposal/view', 'id' => $model->id]) ?>" data-lazy-container="#main-container" data-lazy-modal="invoice-view-modal" data-quick-search-close>
                    <?= Html::encode($model->number) ?>
                </a>
                <div class="metas align-items-center d-flex">
        <span style="background: <?= Html::hex2rgba($model->status->color_label, 0.1) ?>;color: <?= $model->status->color_label ?>" class="font-size-sm text-uppercase px-3 py-2 badge badge-clean">
            <?= $model->status->label ?>
        </span>
                </div>
            </div>
        </div>

        <div class="content flex-grow-1 justify-content-between d-flex flex-column border-top pt-2">
            <div>
                <div class="d-flex ml-auto">
                    <div class="label w-50"><?= Yii::t('app', 'Date') ?></div>
                    <div class="flex-grow-1 text-right">
                        <?= Yii::$app->formatter->asDate($model->date) ?>
                    </div>
                </div>

                <div class="d-flex ml-auto">
                    <div class="label w-50"><?= Yii::t('app', 'Related to') ?></div>
                    <div class="flex-grow-1 text-right">
                        <?= $model->getRelatedObject()->getLink($model->getRelatedModel()) ?>
                    </div>
                </div>
            </div>

            <div>
                <div class="d-flex ml-auto border-top mt-1 pt-1">
                    <div class="label w-50"><?= Yii::t('app', 'Sub Total') ?></div>
                    <div class="flex-grow-1 text-right">
                        <?= Yii::$app->formatter->asCurrency($model->sub_total, $model->currency_code) ?>
                    </div>
                </div>

                <div class="d-flex ml-auto border-top mt-1 pt-1">
                    <div class="label w-50"><?= Yii::t('app', 'Tax') ?></div>
                    <div class="flex-grow-1 text-right">
                        <?= Yii::$app->formatter->asCurrency($model->tax, $model->currency_code) ?>
                    </div>
                </div>

                <div class="d-flex ml-auto border-top mt-1 pt-1 font-weight-bold">
                    <div class="label w-50"><?= Yii::t('app', 'Grand Total') ?></div>
                    <div class="flex-grow-1 text-right">
                        <?= Yii::$app->formatter->asCurrency($model->grand_total, $model->currency_code) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

