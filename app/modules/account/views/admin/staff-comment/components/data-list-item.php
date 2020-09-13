<?php


use modules\account\models\AccountComment;
use modules\account\web\admin\View;
use yii\bootstrap\Html;

/**
 * @var View           $this
 * @var AccountComment $model
 */
?>
<div class="comment-item d-flex <?= ($model->isMe ? 'comment-item-me' : '') ?>">
    <div class="comment-avatar flex-shrink-0"><?= Html::img($model->account->getFileVersionUrl('avatar', 'thumbnail')) ?></div>
    <div class="comment-content">
        <?= $this->block('@content:begin') ?>

        <div class="comment-meta d-flex justify-content-center mb-1">
            <div class="comment-staff flex-grow-1 mr-2">
                <?= Html::a(Html::encode($model->account->username), ['/account/admin/staff/profile', 'id' => $model->account_id]); ?>
            </div>
            <small class="comment-time text-muted align-middle align-self-end flex-shrink-0"
                   data-toggle="tooltip"
                   title="<?= Yii::$app->formatter->asDatetime($model->posted_at) ?>">
                <?= Yii::$app->formatter->asRelativeTime($model->posted_at); ?>
            </small>
        </div>

        <div class="comment-comment">
            <?= Yii::$app->formatter->asHtml($model->comment) ?>
        </div>

        <?= $this->block('@content:end') ?>
    </div>
</div>
