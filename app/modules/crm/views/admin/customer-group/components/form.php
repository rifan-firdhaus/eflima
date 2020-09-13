<?php

use modules\account\web\admin\View;
use modules\crm\models\CustomerGroup;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\fields\ContainerField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\inputs\SpectrumInput;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\ArrayHelper;

/**
 * @var View          $this
 * @var CustomerGroup $model
 * @var array         $formOptions
 */

if (!isset($formOptions)) {
    $formOptions = [];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'id' => 'customer-group-form',
    'model' => $model,
    'layout' => Lazy::isLazyModalRequest() ? Form::LAYOUT_VERTICAL : Form::LAYOUT_HORIZONTAL,
], $formOptions));

echo $this->block('@form:begin', compact('form'));

$this->mainForm($form);

if (Lazy::isLazyModalRequest()) {
    unset($this->toolbar['form-submit']);
}

echo $form->fields([
    [
        'class' => CardField::class,
        'fields' => [
            [
                'class' => ContainerField::class,
                'inputOnly' => true,
                'fields' => [
                    [
                        'size' => 'col-md-6',
                        'field' => [
                            'attribute' => 'name',
                        ],
                    ],
                    [
                        'size' => 'col-md-6',
                        'field' => [
                            'attribute' => 'color_label',
                            'type' => ActiveField::TYPE_WIDGET,
                            'widget' => [
                                'class' => SpectrumInput::class,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'attribute' => 'description',
                'class' => ActiveField::class,
                'type' => ActiveField::TYPE_TEXTAREA,
            ],
            [
                'class' => ActiveField::class,
                'attribute' => 'is_enabled',
                'label' => false,
                'type' => ActiveField::TYPE_CHECKBOX,
                'inputOptions' => [
                    'custom' => true,
                ],
            ],
        ],
    ],
]);

echo $this->block('@form:end', compact('form'));

Form::end();

echo $this->block('@end');