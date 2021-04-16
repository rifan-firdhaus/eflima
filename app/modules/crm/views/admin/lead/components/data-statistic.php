<?php

use modules\account\web\admin\View;
use modules\crm\models\forms\lead\LeadSearch;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * @var View         $this
 * @var LeadSearch   $searchModel
 * @var array        $dataViewOptions
 * @var array|string $searchAction
 */

$originalSearchModel = $searchModel;
$searchModel = clone $searchModel;

$searchModel->status_id = null;
$searchModel->query = null;

$searchModel->filterQuery();

$formatter = Yii::$app->formatter;
$statuses = $searchModel->getStatusSummary();
?>

<div class="widgets d-flex justify-content-between border-top flex-wrap">
    <?php foreach ($statuses AS $status): ?>
        <a href="<?= $searchModel->searchUrl($searchAction,
            [$searchModel->formName() => ['status_id' => $status['id']]]) ?>" class="widget list-group <?= ($status['id'] == $originalSearchModel->status_id ? 'active' : '') ?> flex-grow-1 text-center">
            <div class="widget-value">
                <?= $formatter->asDecimal($status['count']) ?>
                <small class="ml-1">(<?= $formatter->asDecimal($status['ratio'] * 100, 1) ?>%)</small>
            </div>
            <div class="widget-label" style="color: <?= Html::encode($status['color_label']) ?>">
                <span class="color-description" style="background: <?= Html::encode($status['color_label']) ?>"></span><?= Html::encode($status['label']) ?>
            </div>
        </a>
    <?php endforeach; ?>

    <div class="progress w-100 flex-shrink-0 rounded-0" style="height: 5px">
        <?php foreach ($statuses AS $status): ?>
            <?php $progress = round($status['ratio'] * 100, 1); ?>
            <div class="progress-bar"
                 role="progressbar"
                 title="<?= Html::encode($status['label']) . ':  ' . $formatter->asDecimal($progress) ?>%"
                 data-toggle="tooltip"
                 style="width: <?= $progress ?>%;background-color: <?= $status['color_label'] ?>"
                 aria-valuenow="<?= $progress ?>"
                 aria-valuemin="0"
                 aria-valuemax="100">
            </div>
        <?php endforeach; ?>
    </div>
</div>
