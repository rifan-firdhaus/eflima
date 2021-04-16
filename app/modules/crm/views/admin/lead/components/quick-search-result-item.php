<?php

use modules\account\web\admin\View;
use modules\crm\models\Lead;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/**
 * @var View $this
 * @var Lead $model
 */

?>
<div class="w-50 mb-3">
    <div class="quick-search-result-item h-100 d-flex flex-column mb-0 w-auto">
        <div class="header border-bottom mb-2 d-flex justify-content-between">
            <div class="mb-2">
                <a href="<?= Url::to(['/crm/admin/lead/view', 'id' => $model->id]) ?>"
                   data-quick-search-close
                   data-lazy-modal="lead-view-modal"
                   data-lazy-container="#main-container"
                   class="title d-block font-size-lg ">
                    <?= Html::encode($model->name) ?>
                </a>
            </div>
            <div class="meta text-nowrap">
      <span class="badge badge-clean text-uppercase ml-2 px-3 py-2"
            style="color:<?= Html::encode($model->status->color_label) ?>;background-color: <?= Html::hex2rgba($model->status->color_label, 0.1) ?>">
          <?= Html::encode($model->status->label) ?>
      </span>
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
                        'attribute' => 'mobile',
                    ],
                    [
                        'attribute' => 'email',
                        'format' => 'email',
                    ],
                    [
                        'attribute' => 'created_at',
                        'format' => 'datetime',
                    ],
                    [
                        'label' => Yii::t('app', 'Last follow up'),
                        'format' => 'raw',
                        'value' => $model->lastFollowUp ? Yii::$app->formatter->asDatetime($model->lastFollowUp->date) : Html::tag('span', Yii::t('app', 'Never'), ['class' => 'text-warning']),
                    ],
                ],
            ]); ?>
        </div>


    </div>
</div>
