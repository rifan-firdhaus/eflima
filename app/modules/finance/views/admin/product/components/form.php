<?php

use modules\account\web\admin\View;
use modules\finance\assets\admin\ProductFormAsset;
use modules\finance\models\Product;
use modules\finance\widgets\inputs\ProductCategoryInput;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\inputs\NumericInput;
use yii\helpers\ArrayHelper;

/**
 * @var View    $this
 * @var Product $model
 * @var array   $formOptions
 */

if (!isset($formOptions)) {
    $formOptions = [];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'id' => 'product-form',
    'model' => $model,
], $formOptions));

echo $this->block('@form:begin', compact('form'));

$this->mainForm($form);

echo $form->fields([
    [
        'class' => CardField::class,
        'fields' => [
            [
                'attribute' => 'name',
            ],
            [
                'attribute' => 'description',
                'class' => ActiveField::class,
                'type' => ActiveField::TYPE_TEXTAREA,
            ],
            [
                'attribute' => 'price',
                'type' => ActiveField::TYPE_WIDGET,
                'widget' => [
                    'class' => NumericInput::class,
                ],
            ],
            [
                'class' => ActiveField::class,
                'attribute' => 'is_enabled',
                'label' => '',
                'type' => ActiveField::TYPE_CHECKBOX,
                'inputOptions' => [
                    'custom' => true,
                ],
            ],
        ],
    ],
]);

Form::end();


echo $this->block('@end');