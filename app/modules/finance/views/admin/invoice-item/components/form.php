<?php

use modules\account\web\admin\View;
use modules\finance\assets\admin\InvoiceItemFormAsset;
use modules\finance\models\InvoiceItem;
use modules\finance\models\Product;
use modules\finance\widgets\inputs\ProductInput;
use modules\finance\widgets\inputs\TaxValueInput;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\fields\RegularField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\inputs\NumericInput;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var View        $this
 * @var InvoiceItem $model
 * @var array       $formOptions
 */

InvoiceItemFormAsset::register($this);

if (!isset($formOptions)) {
    $formOptions = [];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'id' => 'invoice-item-form',
    'model' => $model,
    'lazy' => [
        'jsOptions' => [
            'pushState' => false,
            'scroll' => false,
        ],
    ],
], $formOptions));

echo $this->block('@form:begin', compact('form'));

$this->mainForm($form);

if (Lazy::isLazyModalRequest()) {
    unset($this->toolbar['form-submit']);
}

if (Yii::$app->request->get('temp')) {
    echo Html::hiddenInput('model', Yii::$app->request->post('model'));
    echo Html::hiddenInput('models', Yii::$app->request->post('models'));
    echo Html::hiddenInput('invoice', Yii::$app->request->post('invoice'));
}

echo $form->fields([
    [
        'attribute' => 'type',
        'inputOnly' => true,
        'type' => 'hidden',
    ],
    [
        'attribute' => 'id',
        'inputOnly' => true,
        'type' => 'hidden',
    ],
    [
        'attribute' => 'name',
        'inputOnly' => true,
        'type' => 'hidden',
    ],
    [
        'attribute' => 'product_id',
        'inputOnly' => true,
        'type' => 'hidden',
    ],
    [
        'class' => CardField::class,
        'label' => false,
        'fields' => [
            [
                'class' => RegularField::class,
                'type' => ActiveField::TYPE_WIDGET,
                'label' => Yii::t('app', 'Product'),
                'name' => 'product_name',
                'value' => $model->type === 'raw' ? $model->name : $model->product_id,
                'inputOptions' => [
                    'class' => 'form-control',
                    'id' => 'invoiceitem-product_chooser',
                    'prompt' => '',
                ],
                'widget' => [
                    'class' => ProductInput::class,
                    'selected' => function ($value, $selec2) use ($model) {
                        if ($model->type === 'raw') {
                            return [$model->name => $model->name];
                        }

                        return Product::find()->andWhere(['id' => $model->product_id])->map('id', 'name');
                    },
                ],
            ],
            [
                'attribute' => 'price',
                'type' => ActiveField::TYPE_WIDGET,
                'widget' => [
                    'class' => NumericInput::class,
                    'clientOptions' => [
                        'alias' => 'decimal',
                        'autoGroup' => true,
                        'rightAlign' => true,
                    ],
                ],
            ],
            [
                'attribute' => 'amount',
                'type' => ActiveField::TYPE_WIDGET,
                'widget' => [
                    'class' => NumericInput::class,
                    'clientOptions' => [
                        'alias' => 'decimal',
                        'autoGroup' => true,
                        'rightAlign' => true,
                    ],
                ],
            ],
            [
                'attribute' => 'sub_total',
                'type' => ActiveField::TYPE_WIDGET,
                'inputOptions' => [
                    'readonly' => true,
                    'class' => 'form-control',
                ],
                'widget' => [
                    'class' => NumericInput::class,
                    'clientOptions' => [
                        'alias' => 'decimal',
                        'autoGroup' => true,
                        'rightAlign' => true,
                    ],
                ],
            ],
            [
                'attribute' => 'tax_inputs',
                'type' => ActiveField::TYPE_WIDGET,
                'standalone' => true,
                'inputOptions' => [
                    'class' => '',
                ],
                'widget' => [
                    'class' => TaxValueInput::class,
                    'jsOptions' => [
                        'beforeTaxInput' => '#' . Html::getInputId($model, 'sub_total'),
                        'afterTaxInput' => '#' . Html::getInputId($model, 'grand_total'),
                        'models' => $model->tax_inputs,
                    ],
                ],
            ],
            [
                'attribute' => 'grand_total',
                'type' => ActiveField::TYPE_WIDGET,
                'inputOptions' => [
                    'readonly' => true,
                    'class' => 'form-control',
                ],
                'widget' => [
                    'class' => NumericInput::class,
                    'clientOptions' => [
                        'alias' => 'decimal',
                        'autoGroup' => true,
                        'rightAlign' => true,
                    ],
                ],
            ],
        ],
    ],
]);

echo $this->block('@form:end', compact('form'));

$this->registerJs("$('#{$form->id}').invoiceItemForm()");

Form::end();

echo $this->block('@end');