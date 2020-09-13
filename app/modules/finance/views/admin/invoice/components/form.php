<?php

use modules\account\web\admin\View;
use modules\account\widgets\inputs\StaffInput;
use modules\crm\widgets\inputs\CustomerInput;
use modules\finance\assets\admin\InvoiceFormAsset;
use modules\finance\components\Payment;
use modules\finance\models\Invoice;
use modules\finance\widgets\inputs\CurrencyInput;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\fields\ContainerField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\inputs\DatepickerInput;
use modules\ui\widgets\inputs\NumericInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * @var View    $this
 * @var Invoice $model
 * @var array   $formOptions
 */

InvoiceFormAsset::register($this);

if (!isset($formOptions)) {
    $formOptions = [];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'id' => 'invoice-form',
    'model' => $model,
], $formOptions));

echo $this->block('@form:begin', compact('form'));

$this->mainForm($form);

echo $form->fields([
    [
        'class' => ContainerField::class,
        'inputOnly' => true,
        'fields' => [
            [
                'size' => 'col-md-6 d-flex pr-0',
                'field' => [
                    'class' => CardField::class,
                    'label' => Yii::t('app', 'Invoice Detail'),
                    'inputOptions' => [
                        'id' => Html::getInputId($model, 'basic_section'),
                        'data-rid' => Html::getRealInputId($model, 'basic_section'),
                    ],
                    'card' => [
                        'icon' => 'i8:cash',
                        'options' => [
                            'class' => 'card flex-grow-1 h-100',
                        ],
                    ],
                    'fields' => [
                        [
                            'attribute' => 'number',
                        ],
                        [
                            'attribute' => 'date',
                            'type' => ActiveField::TYPE_WIDGET,
                            'widget' => [
                                'class' => DatepickerInput::class,
                            ],
                        ],
                        [
                            'attribute' => 'due_date',
                            'type' => ActiveField::TYPE_WIDGET,
                            'widget' => [
                                'class' => DatepickerInput::class,
                            ],
                        ],
                        [
                            'attribute' => 'customer_id',
                            'type' => ActiveField::TYPE_WIDGET,
                            'widget' => [
                                'class' => CustomerInput::class,
                                'allowAdd' => true,
                            ],
                        ],
                        [
                            'class' => ContainerField::class,
                            'label' => Yii::t('app', 'Currency'),
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
                                        ],
                                    ],
                                ],
                                [
                                    'size' => 'col-md-12',
                                    'field' => [
                                        'attribute' => 'currency_rate',
                                        'standalone' => true,
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
                    ],
                ],
            ],
            [
                'size' => 'col-md-6 d-flex pl-0 border-left',
                'field' => [
                    'class' => CardField::class,
                    'label' => Yii::t('app', 'Settings'),
                    'inputOptions' => [
                        'id' => Html::getInputId($model, 'advance_section'),
                        'data-rid' => Html::getRealInputId($model, 'advance_section'),
                    ],
                    'card' => [
                        'icon' => 'i8:file',
                        'options' => [
                            'class' => 'card flex-grow-1 h-100',
                        ],
                    ],
                    'fields' => [
                        [
                            'attribute' => 'allowed_payment_method',
                            'label' => Yii::t('app', 'Payment Method'),
                            'type' => ActiveField::TYPE_CHECKBOX_LIST,
                            'source' => Payment::map(),
                            'inputOptions' => [
                                'class' => 'h-100 d-flex align-items-center',
                                'itemOptions' => [
                                    'custom' => true,
                                    'inline' => true,
                                ],
                            ],
                        ],
                        [
                            'attribute' => 'assignee_ids',
                            'type' => ActiveField::TYPE_WIDGET,
                            'widget' => [
                                'class' => StaffInput::class,
                                'multiple' => true,
                            ],
                        ],
                        [
                            'attribute' => 'is_assignee_allowed_to_update_item',
                            'type' => ActiveField::TYPE_CHECKBOX,
                            'label' => '',
                            'inputOptions' => [
                                'custom' => true,
                            ],
                        ],
                        [
                            'attribute' => 'is_assignee_allowed_to_add_payment',
                            'type' => ActiveField::TYPE_CHECKBOX,
                            'label' => '',
                            'inputOptions' => [
                                'custom' => true,
                            ],
                        ],
                        [
                            'attribute' => 'is_assignee_allowed_to_add_discount',
                            'type' => ActiveField::TYPE_CHECKBOX,
                            'label' => '',
                            'inputOptions' => [
                                'custom' => true,
                            ],
                        ],
                        [
                            'attribute' => 'is_assignee_allowed_to_cancel',
                            'type' => ActiveField::TYPE_CHECKBOX,
                            'label' => '',
                            'inputOptions' => [
                                'custom' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
]);

echo $this->block('@form:end', compact('form'));

$rows = [];

foreach ($model->items AS $item) {
    $scenario = $item->scenario;

    $item->scenario = 'admin/temp/update';

    foreach ($item->taxes AS $tax) {
        $tax->scenario = 'admin/temp';
    }

    $item->normalizeAttributes();

    $rows[] = [
        'row' => $this->render('/admin/invoice-item/components/item-row', [
            'model' => $item,
        ]),
        'model' => ArrayHelper::toArray($item),
    ];

    $item->scenario = $scenario;
}

$jsOptions = Json::encode([
    'baseCurrency' => Yii::$app->setting->get('finance/base_currency'),
    'reevaluateUrl' => Url::to(['/finance/admin/invoice-item/reevaluate']),
    'rows' => $rows,
]);

$this->registerJs("$('#{$form->id}').invoiceForm({$jsOptions})");

echo $this->render('/admin/invoice-item/components/item-table', [
    'model' => $model
]);

Form::end();

echo $this->block('@end');