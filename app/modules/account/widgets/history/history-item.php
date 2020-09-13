<?php

use modules\account\models\History;
use modules\account\widgets\history\HistoryWidget;
use modules\core\web\View;
use yii\helpers\Html;
use modules\ui\widgets\Icon;

/**
 * @var View          $view
 * @var History       $model
 * @var HistoryWidget $widget
 * @var string        $description
 * @var array         $params
 * @var string        $icon
 * @var array         $iconOptions
 */

$icons = [
    'add' => "i8:plus-math",
    'update' => "i8:edit",
    'delete' => "i8:trash",
    'block' => "i8:shield",
    'unblock' => "i8:remove-shield",
];

if (!isset($icon)) {
    $icon = isset($icons[$model->tag]) ? $icons[$model->tag] : null;
}

?>

<div class="history-timeline-item-content">
    <?= ($icon ? Icon::show($icon, $iconOptions) : null); ?>
  <div class="history-timeline-item-description">
      <?= Yii::t('app', $description, $params) ?>
  </div>
  <div class="history-timeline-item-time">
      <?= Yii::$app->formatter->asTime($model->at) ?>
  </div>
  <div class="history-timeline-item-executor">
      <?= Html::a(Html::img($model->executor->getFileVersionUrl('avatar','thumbnail'), ['class' => 'history-timeline-item-executor-avatar']) . $model->executor->username, ['/']) ?>
  </div>
</div>

