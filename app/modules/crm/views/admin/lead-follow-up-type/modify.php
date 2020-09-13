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

if (!$model->isNewRecord) {
    if (!Lazy::isLazyModalRequest()) {
        $this->toolbar['delete-lead-follow-up-type'] = Html::a(
            '',
            ['/crm/admin/lead-follow-up-type/delete', 'id' => $model->id],
            [
                'class' => 'btn btn-danger btn-icon',
                'icon' => 'i8:trash',
                'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure', [
                    'object_name' => Html::tag('strong', $model->name),
                ]),
                'data-placement' => 'bottom',
                'title' => Yii::t('app', 'Delete'),
            ]
        );

        $this->toolbar['add-lead-follow-up-type'] = Html::a(
            Yii::t('app', 'Add'),
            ['/crm/admin/lead-follow-up-type/add', 'id' => $model->id],
            [
                'class' => 'btn btn-secondary',
                'icon' => 'i8:plus',
            ]
        );
    }
}

echo $this->block('@begin');
echo $this->render('components/form', compact('model'));
echo $this->block('@end');
