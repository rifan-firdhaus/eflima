<?php

use modules\account\web\admin\View;
use modules\finance\models\Product;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\Html;

/**
 * @var View    $this
 * @var Product $model
 */

if ($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add');
    $this->subTitle = Yii::t('app', 'Product');
} else {
    $this->title = Yii::t('app', 'Update');
    $this->subTitle = Html::encode($model->name);
}

$this->icon = 'i8:money-transfer';
$this->menu->active = 'main/transaction/product';

if (!$model->isNewRecord) {
    if (!Lazy::isLazyModalRequest()) {
        $this->toolbar['delete-product'] = Html::a(
            '',
            ['/finance/admin/product/delete', 'id' => $model->id],
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

        $this->toolbar['add-product'] = Html::a(
            Yii::t('app', 'Add'),
            ['/finance/admin/product/add', 'id' => $model->id],
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
