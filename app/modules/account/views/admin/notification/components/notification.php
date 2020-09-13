<?php

use modules\account\models\AccountNotification;
use modules\account\models\StaffAccount;
use modules\account\web\admin\View;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var View                $this
 * @var AccountNotification $model
 * @var StaffAccount        $account
 */
$account = Yii::$app->user->identity;
$url = $model->renderedUrl ? Url::to(['/account/admin/notification/visit', 'id' => $model->id]) : '#';

echo $this->block('@begin');
?>

<a href="<?= $url ?>" class="side-panel-close account-notification <?= ($model->getIsRead($account) ? 'read' : 'not-read') ?>">
    <div class="content">
        <?= $this->block('@content:begin') ?>

        <?php if ($model->title): ?>
            <div class="account-notification-title text-primary font-weight-bold">
                <?= $this->block('@title:begin') ?>
                <?= Html::encode(Yii::t('app', $model->title, $model->title_params)) ?>
                <?= $this->block('@title:end') ?>
            </div>
        <?php endif; ?>

        <?php if ($model->body): ?>
            <div class="account-notification-body">
                <?= $this->block('@body:begin') ?>
                <?= Html::encode(Yii::t('app', $model->body, $model->body_params)) ?>
                <?= $this->block('@body:end') ?>
            </div>
        <?php endif; ?>

        <div class="account-notification-time">
            <?= $this->block('@time:begin') ?>
            <?= Yii::$app->formatter->asDatetime($model->at) ?> - <?= Yii::$app->formatter->asRelativeTime($model->at) ?>
            <?= $this->block('@time:end') ?>
        </div>

        <?= $this->block('@content:end') ?>
    </div>
</a>

<?= $this->block('@end'); ?>
