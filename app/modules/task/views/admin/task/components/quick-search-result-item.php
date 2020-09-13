<?php

use modules\account\models\StaffAccount;
use modules\account\web\admin\View;
use modules\task\models\Task;
use yii\bootstrap4\Progress;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var View         $this
 * @var Task         $model
 * @var StaffAccount $account
 */

$account = Yii::$app->user->identity;

?>
<div class="quick-search-result-item" style="border-left: 5px solid <?= $model->status->color_label; ?>">
    <div class="header d-flex justify-content-between">
        <a href="<?= Url::to(['/task/admin/task/view', 'id' => $model->id]) ?>"
           data-quick-search-close
           data-lazy-modal="task-view-modal"
           data-lazy-container="#main-container"
           class="title d-block mb-2 font-size-lg mb-2">
            <?php if ($model->isTimerStarted($account->profile->id)): ?>
                <span class="bubble-indicator mr-1 bg-danger animation-blink" title="<?= Yii::t('app', 'Recording') ?>" data-toggle="tooltip"></span>
            <?php endif; ?>
            <?= Html::encode($model->title) ?>
        </a>
        <div class="meta text-nowrap">
      <span class="badge badge-clean text-uppercase ml-2 px-3 py-2" style="color:<?= Html::encode($model->status->color_label) ?>;background-color: <?= Html::hex2rgba($model->status->color_label,
          0.1) ?>">
          <?= Html::encode($model->status->label) ?>
      </span>
            <span class="badge badge-clean text-uppercase px-3 py-2" style="color:<?= Html::encode($model->priority->color_label) ?>;background-color: <?= Html::hex2rgba($model->priority->color_label,
                0.1) ?>">
          <?= Html::encode($model->priority->label) ?>
      </span>
        </div>
    </div>
    <div class="content">
        <div class="d-flex">
            <div class="column-detail mr-4">
                <div class="label"><?= Yii::t('app', 'Progress') ?></div>
                <div class="value text-center">
                    <div class="task-list-progress-label mb-1"><?= $model->progress * 100 ?>%</div>
                    <?= Progress::widget([
                        'percent' => $model->progress * 100,
                        'options' => [
                            'style' => 'height:4px',
                            'class' => 'flex-grow-1 mt-1',
                        ],
                    ]);
                    ?>
                </div>
            </div>

            <div class="column-detail mr-4">
                <div class="label"><?= Yii::t('app', 'Started Date') ?></div>
                <div class="value">
                    <div><?= Yii::$app->formatter->asDate($model->started_date) ?></div>
                    <small><?= Yii::$app->formatter->asTime($model->started_date) ?></small>
                </div>
            </div>

            <div class="column-detail mr-4">
                <div class="label"><?= Yii::t('app', 'Deadline Date') ?></div>
                <div class="value">
                    <div><?= Yii::$app->formatter->asDate($model->deadline_date) ?></div>
                    <small><?= Yii::$app->formatter->asTime($model->deadline_date) ?></small>
                </div>
            </div>
        </div>

    </div>
</div>
