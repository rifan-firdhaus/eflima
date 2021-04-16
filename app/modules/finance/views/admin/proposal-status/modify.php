<?php

use modules\account\web\admin\View;
use modules\finance\models\ProposalStatus;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\Html;

/**
 * @var View           $this
 * @var ProposalStatus $model
 */

if ($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add');
    $this->subTitle = Yii::t('app', 'Proposal status');
} else {
    $this->title = Yii::t('app', 'Update');
    $this->subTitle = Html::encode($model->label);
}

$this->icon = 'i8:handshake';
$this->menu->active = 'setting/finance';

if (!$model->isNewRecord && !Lazy::isLazyModalRequest()) {
    if (Yii::$app->user->can('admin.setting.finance.proposal-status.delete')) {
        $this->toolbar['delete-proposal-status'] = Html::a([
            'url' => ['/finance/admin/proposal-status/delete', 'id' => $model->id],
            'class' => 'btn btn-outline-danger btn-icon',
            'icon' => 'i8:trash',
            'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure', [
                'object_name' => Html::tag('strong', $model->label),
            ]),
            'data-placement' => 'bottom',
            'title' => Yii::t('app', 'Delete'),
            'data-lazy-options' => ['method' => 'DELETE'],
        ]);
    }
}

echo $this->block('@begin');
echo $this->render('components/form', compact('model'));
echo $this->block('@end');
