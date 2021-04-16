<?php

use modules\account\web\admin\View;
use modules\support\models\Ticket;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\Html;

/**
 * @var View   $this
 * @var Ticket $model
 */


if ($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add');
    $this->subTitle = Yii::t('app', 'Ticket');
} else {
    $this->title = Yii::t('app', 'Update');
    $this->subTitle = Html::encode($model->name);
}

$this->icon = 'i8:two-tickets';
$this->menu->active = 'main/support/ticket';

if (!$model->isNewRecord) {
    if (!Lazy::isLazyModalRequest()) {
        $this->toolbar['delete-ticket'] = Html::a([
            'url' => ['/task/admin/ticket/delete', 'id' => $model->id],
            'class' => 'btn btn-danger btn-icon',
            'icon' => 'i8:trash',
            'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure', [
                'object_name' => Html::tag('strong', $model->name),
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
