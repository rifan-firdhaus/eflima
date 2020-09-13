<?php

use modules\account\web\admin\View;
use modules\crm\widgets\inputs\CustomerInput;
use modules\file_manager\widgets\inputs\FileUploaderInput;
use modules\finance\assets\admin\ExpenseFormAsset;
use modules\finance\models\Expense;
use modules\finance\widgets\inputs\CurrencyInput;
use modules\finance\widgets\inputs\ExpenseCategoryInput;
use modules\finance\widgets\inputs\TaxValueInput;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\fields\ContainerField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\inputs\DatepickerInput;
use modules\ui\widgets\inputs\NumericInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * @var View    $this
 * @var Expense $model
 * @var array   $formOptions
 */

ExpenseFormAsset::register($this);

$attachments = [];

foreach ($model->attachments AS $attachment) {
    $attachments[] = $attachment->getFileMetaData('file');
}

if (!isset($formOptions)) {
    $formOptions = [];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'id' => 'expense-form',
    'model' => $model,
], $formOptions));

echo $this->block('@form:begin', compact('form'));

$this->mainForm($form);

$updatableInvoiceField = $model->isInvoiceFieldUpdatable;

echo $form->fields([
    [
        'class' => ContainerField::class,
        'inputOnly' => true,
        'inputOptions' => [
            'class' => 'form-row m-0',
        ],
        'fields' => [
            [
                'size' => 'col-md-6 p-0',
                'field' => [
                    'class' => CardField::class,
                    'label' => Yii::t('app', 'Basic Information'),
                    'inputOptions' => [
                        'id' => Html::getInputId($model, 'basic_section'),
                    ],
                    'card' => [
                        'icon' => 'i8:file',
                        'options' => ['class' => 'card h-100'],
                    ],
                    'fields' => [
                        'name',
                        [
                            'attribute' => 'date',
                            'type' => ActiveField::TYPE_WIDGET,
                            'widget' => [
                                'class' => DatepickerInput::class,
                            ],
                        ],
                        [
                            'attribute' => 'description',
                            'type' => ActiveField::TYPE_TEXTAREA,
                        ],
                        [
                            'attribute' => 'category_id',
                            'type' => ActiveField::TYPE_WIDGET,
                            'widget' => [
                                'class' => ExpenseCategoryInput::class,
                                'prompt' => '',
                                'aliasAttribute' => 'new_category',
                            ],
                        ],
                        [
                            'attribute' => 'customer_id',
                            'type' => ActiveField::TYPE_WIDGET,
                            'widget' => [
                                'class' => CustomerInput::class,
                                'allowClear' => true,
                                'allowAdd' => true,
                                'jsOptions' => [
                                    'disabled' => !$updatableInvoiceField,
                                    'width' => '100%',
                                ],
                            ],
                        ],
                        [
                            'attribute' => 'uploaded_attachments[]',
                            'type' => ActiveField::TYPE_WIDGET,
                            'widget' => [
                                'class' => FileUploaderInput::class,
                                'multiple' => true,
                                'jsOptions' => [
                                    'values' => $attachments,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'size' => 'col-md-6 d-flex p-0 border-left',
                'field' => [
                    'class' => CardField::class,
                    'label' => Yii::t('app', 'Bill Detail'),
                    'inputOptions' => [
                        'id' => Html::getInputId($model, 'bill_section'),
                    ],
                    'card' => [
                        'icon' => 'i8:file',
                        'options' => [
                            'class' => 'card flex-grow-1 h-100',
                        ],
                    ],
                    'fields' => [
                        [
                            'attribute' => 'reference',
                        ],
                        [
                            'class' => ContainerField::class,
                            'label' => Yii::t('app', 'Currency'),
                            'options' => [
                                'class' => 'form-row border-bottom mb-3',
                            ],
                            'fields' => [
                                [
                                    'size' => 'col-md-12',
                                    'field' => [
                                        'attribute' => 'currency_code',
                                        'standalone' => true,
                                        'type' => ActiveField::TYPE_WIDGET,
                                        'widget' => [
                                            'class' => CurrencyInput::class,
                                            'is_enabled' => true,
                                            'jsOptions' => [
                                                'disabled' => !$updatableInvoiceField,
                                                'width' => '100%',
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'size' => 'col-md-12',
                                    'field' => [
                                        'attribute' => 'currency_rate',
                                        'standalone' => true,
                                        'inputOptions' => [
                                            'readonly' => !$updatableInvoiceField,
                                        ],
                                        'inputGroups' => [
                                            [
                                                'position' => 'prepend',
                                                'content' => Yii::t('app', 'Rate'),
                                            ],
                                        ],
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
                                ],
                            ],
                        ],
                        [
                            'attribute' => 'amount',
                            'type' => ActiveField::TYPE_WIDGET,
                            'inputOptions' => [
                                'readonly' => !$updatableInvoiceField,
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
                                    'beforeTaxInput' => '#' . Html::getInputId($model, 'amount'),
                                    'afterTaxInput' => '#' . Html::getInputId($model, 'total'),
                                    'models' => $model->tax_inputs,
                                ],
                            ],
                        ],
                        [
                            'attribute' => 'total',
                            'type' => ActiveField::TYPE_WIDGET,
                            'inputOptions' => [
                                'class' => 'form-control',
                                'readonly' => true,
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
                            'attribute' => 'is_billable',
                            'type' => ActiveField::TYPE_CHECKBOX,
                            'label' => '',
                            'inputOptions' => [
                                'disabled' => !$updatableInvoiceField,
                                'custom' => true,
                                'label' => Yii::t('app', 'Billable (Checking this checkbox means you can bill this expense in customer\'s invoice)'),
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
]);

echo $this->block('@form:end', compact('form'));

$jsOptions = Json::encode([
    'baseCurrency' => Yii::$app->setting->get('finance/base_currency'),
]);

$this->registerJs("$('#{$form->id}').expenseForm({$jsOptions})");

Form::end();


echo $this->block('@end');
