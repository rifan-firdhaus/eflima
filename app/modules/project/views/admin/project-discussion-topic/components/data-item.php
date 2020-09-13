<?php

use modules\account\web\admin\View;
use modules\project\models\ProjectDiscussionTopic;
use modules\ui\widgets\Icon;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var View                   $this
 * @var ProjectDiscussionTopic $model
 * @var boolean                $isActive
 */
$viewUrl = Url::to([
    '/project/admin/project/view',
    'id' => $model->project_id,
    'action' => 'discussion',
    'topic_id' => $model->id,
]);

if (!isset($isActive)) {
    $isActive = $model->id == Yii::$app->request->get('topic_id');
}
?>

<a href="<?= $viewUrl ?>" data-toggle="list" class="list-group-item <?= ($isActive ? 'active' : '') ?> rounded-0 list-group-item-action project-item-discussion-item border-bottom border-left-0 border-right-0">
    <div class="font-weight-bold mb-2"><?= Html::encode($model->subject) ?></div>
    <div class="metas d-flex align-items-center font-size-sm">
        <div class="mr-3">
            <?= Icon::show('i8:chat', ['class' => 'icon icons8-size mr-1']) ?> <?= Yii::$app->formatter->asDecimal($model->totalComment); ?>
        </div>

        <div class="mr-3">
            <?= Icon::show('i8:clock', ['class' => 'icon icons8-size mr-1']) ?>
            <span data-toggle="tooltip" title="<?= Yii::$app->formatter->asDatetime($model->created_at) ?>"><?= Yii::$app->formatter->asRelativeTime($model->created_at); ?></span>
        </div>

        <?php if ($model->is_internal): ?>
            <div class="mr-3 text-warning">
                <?= Icon::show('i8:box-important', ['class' => 'icon icons8-size mr-1']) ?>
                <?= Yii::t('app', 'Internal'); ?>
            </div>
        <?php endif; ?>
    </div>
</a>
