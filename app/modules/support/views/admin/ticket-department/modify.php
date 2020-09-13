<?php

use modules\account\web\admin\View;
use modules\support\models\TicketDepartment;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\Html;

/**
 * @var View             $this
 * @var TicketDepartment $model
 */

if ($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add');
    $this->subTitle = Yii::t('app', 'Ticket Department');
} else {
    $this->title = Yii::t('app', 'Update');
    $this->subTitle = Html::encode($model->name);
}

$this->icon = 'i8:hammer';
$this->menu->active = 'main/task';

if (!$model->isNewRecord) {
    if (!Lazy::isLazyModalRequest()) {
        $this->toolbar['delete-ticket-department'] = Html::a(
            '',
            ['/task/admin/ticket-department/delete', 'id' => $model->id],
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

        $this->toolbar['add-ticket-department'] = Html::a(
            Yii::t('app', 'Add'),
            ['/task/admin/ticket-department/add', 'id' => $model->id],
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
