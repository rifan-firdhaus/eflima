<?php

use modules\account\web\admin\View;
use modules\support\models\TicketPredefinedReply;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\Html;

/**
 * @var View                  $this
 * @var TicketPredefinedReply $model
 */

if ($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add');
    $this->subTitle = Yii::t('app', 'Predefined Reply');
} else {
    $this->title = Yii::t('app', 'Update');
    $this->subTitle = Html::encode($model->title);
}

$this->icon = 'i8:index';
$this->menu->active = 'setting/ticket';

if (!$model->isNewRecord) {
    if (!Lazy::isLazyModalRequest()) {
        $this->toolbar['delete-ticket-predefined-reply'] = Html::a(
            '',
            ['/task/admin/ticket-predefined-reply/delete', 'id' => $model->id],
            [
                'class' => 'btn btn-danger btn-icon',
                'icon' => 'i8:trash',
                'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure', [
                    'object_name' => Html::tag('strong', $model->title),
                ]),
                'data-placement' => 'bottom',
                'title' => Yii::t('app', 'Delete'),
            ]
        );

        $this->toolbar['add-ticket-predefined-reply'] = Html::a(
            Yii::t('app', 'Add'),
            ['/task/admin/ticket-predefined-reply/add', 'id' => $model->id],
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
