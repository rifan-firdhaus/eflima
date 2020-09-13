<?php

use modules\account\web\admin\View;
use modules\crm\models\CustomerContact;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\Html;

/**
 * @var View            $this
 * @var CustomerContact $model
 */

if ($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add');
    $this->subTitle = Yii::t('app', 'Contact');
} else {
    $this->title = Yii::t('app', 'Update');
    $this->subTitle = Html::encode($model->name);
}

$this->icon = 'i8:conference';
$this->menu->active = 'setting/crm/customer';

if (!$model->isNewRecord) {
    if (!Lazy::isLazyModalRequest()) {
        $this->toolbar['delete-customer-contact'] = Html::a(
            '',
            ['/crm/admin/customer-contact/delete', 'id' => $model->id],
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

        $this->toolbar['add-customer-contact'] = Html::a(
            Yii::t('app', 'Add'),
            ['/crm/admin/customer-contact/add', 'id' => $model->id],
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
