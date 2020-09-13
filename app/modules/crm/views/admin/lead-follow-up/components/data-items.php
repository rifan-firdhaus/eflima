<?php

use modules\account\web\admin\View;
use modules\crm\models\LeadFollowUp;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

/**
 * @var View               $this
 * @var ActiveDataProvider $dataProvider
 * @var LeadFollowUp[]     $models
 */

$models = $dataProvider->models;
?>

<?php foreach ($models AS $model): ?>

    <div class="d-flex border-top py-3 w-100 lead-follow-up">
        <?= Html::img($model->staff->account->getFileVersionUrl('avatar', 'thumbnail', Yii::getAlias('@web/public/img/avatar.png')), [
            'class' => 'rounded-circle mr-3',
            'style' => ['width' => '40px','height' => '40px'],
        ]);
        ?>
        <div class="lead-follow-up-container w-100 ">
            <div class="d-flex lead-follow-up-header justify-content-between w-100 align-items-center">
                <div class="lead-follow-up-staff d-flex w-100 align-items-center">
                    <div class="lead-follow-up-staff-detail">
                        <div class="lead-follow-up-staff-name"><?= Html::encode($model->staff->account->username) ?></div>
                        <div class="lead-follow-up-by">Followed by: <strong><?= Html::encode($model->type->label) ?></strong></div>
                    </div>
                </div>

                <div class="lead-follow-up-time text-right text-nowrap">
                    <div class="font-size-sm"><?= Yii::$app->formatter->asDatetime($model->date) ?></div>
                    <div class="font-size-xs"><?= Yii::$app->formatter->asRelativeTime($model->date) ?></div>
                </div>
            </div>

            <?php if (!empty($model->note)): ?>
                <div class="lead-follow-up-note mt-2">
                    <?= Html::encode($model->note); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php endforeach; ?>


