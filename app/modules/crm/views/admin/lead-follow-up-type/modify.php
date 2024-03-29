<?php

use modules\account\web\admin\View;
use modules\crm\models\LeadFollowUpType;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\Html;

/**
 * @var View             $this
 * @var LeadFollowUpType $model
 */

if ($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add');
    $this->subTitle = Yii::t('app', 'Lead Follow Up Type');
} else {
    $this->title = Yii::t('app', 'Update');
    $this->subTitle = Html::encode($model->label);
}

$this->icon = 'i8:handshake';
$this->menu->active = 'setting/crm';

if (!$model->isNewRecord && !Lazy::isLazyModalRequest()) {
    if (Yii::$app->user->can('admin.setting.crm.lead-follow-up-type.delete')) {
        $this->toolbar['delete-lead-follow-up-type'] = Html::a([
            'url' => ['/crm/admin/lead-follow-up-type/delete', 'id' => $model->id],
            'class' => 'btn btn-outlline-danger btn-icon',
            'icon' => 'i8:trash',
            'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure', [
                'object_name' => Html::tag('strong', $model->label),
            ]),
            'data-placement' => 'bottom',
            'title' => Yii::t('app', 'Delete'),
            'data-lazy-options' => ['method' => 'DELETE']
        ]);
    }
}

echo $this->block('@begin');
echo $this->render('components/form', compact('model'));
echo $this->block('@end');
