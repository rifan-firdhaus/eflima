<?php

use modules\account\web\admin\View;
use modules\crm\models\Customer;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\Html;

/**
 * @var View     $this
 * @var Customer $model
 */

if ($model->isNewRecord) {
    if ($model->fromLead) {
        $this->title = Yii::t('app', 'Convert to Customer');
        $this->subTitle = $model->fromLead->name;
    } else {
        $this->title = Yii::t('app', 'Add');
        $this->subTitle = Yii::t('app', 'Customer');
    }
} else {
    $this->title = Yii::t('app', 'Update');
    $this->subTitle = Html::encode($model->company_name);
}

$this->icon = 'i8:contacts';
$this->menu->active = 'main/crm/customer';

if (!$model->isNewRecord && !Lazy::isLazyModalRequest()) {
    if (Yii::$app->user->can('admin.customer.delete')) {
        $this->toolbar['delete-customer'] = Html::a([
            'url' => ['/crm/admin/customer/delete', 'id' => $model->id],
            'class' => 'btn btn-outline-danger btn-icon',
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
