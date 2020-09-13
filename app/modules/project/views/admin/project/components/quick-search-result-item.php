<?php

use modules\project\models\Project;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var Project $model
 */

?>
<div class="w-50 mb-3">
    <div class="quick-search-result-item h-100 d-flex flex-column mb-0 w-auto">
        <div class="header border-bottom mb-2 d-flex justify-content-between">
            <div class="mb-2">
                <a href="<?= Url::to(['/project/admin/project/view', 'id' => $model->id]) ?>"
                   data-quick-search-close
                   data-lazy-modal="project-view-modal"
                   data-lazy-container="#main-container"
                   class="title d-block font-size-lg ">
                    <?= Html::encode($model->name) ?>
                </a>
                <a href="<?= Url::to(['/crm/admin/customer/view', 'id' => $model->customer_id]) ?>"
                   data-quick-search-close
                   data-lazy-modal="customer-view-modal"
                   data-lazy-container="#main-container"
                   class="title d-block font-size-sm">
                    <?= Html::encode($model->customer->name) ?>
                </a>
            </div>
            <div class="meta text-nowrap">
      <span class="badge badge-clean text-uppercase ml-2 px-3 py-2" style="color:<?= Html::encode($model->status->color_label) ?>;background-color: <?= Html::hex2rgba($model->status->color_label,
          0.1) ?>">
          <?= Html::encode($model->status->label) ?>
      </span>
            </div>
        </div>
        <div class="content">
            <div class="d-flex">
                <div class="column-detail w-50 mr-4">
                    <div class="label"><?= Yii::t('app', 'Started Date') ?></div>
                    <div class="value">
                        <div><?= Yii::$app->formatter->asDate($model->started_date) ?></div>
                        <small><?= Yii::$app->formatter->asTime($model->started_date) ?></small>
                    </div>
                </div>

                <div class="column-detail w-50 mr-4">
                    <div class="label"><?= Yii::t('app', 'Deadline Date') ?></div>
                    <div class="value">
                        <div><?= Yii::$app->formatter->asDate($model->deadline_date) ?></div>
                        <small><?= Yii::$app->formatter->asTime($model->deadline_date) ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
