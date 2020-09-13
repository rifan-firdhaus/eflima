<?php

use modules\account\web\admin\View;
use modules\address\widgets\inputs\CountryInput;
use modules\crm\assets\admin\CustomerFormAsset;
use modules\crm\models\Customer;
use modules\crm\widgets\inputs\CustomerGroupInput;
use modules\file_manager\widgets\inputs\FileUploaderInput;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\fields\ContainerField;
use modules\ui\widgets\form\fields\MultiField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var View     $this
 * @var Customer $model
 * @var array    $formOptions
 */

CustomerFormAsset::register($this);

if (!isset($formOptions)) {
    $formOptions = [];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'id' => 'customer-form',
    'model' => $model,
], $formOptions));

echo $this->block('@form:begin', compact('form'));

$this->mainForm($form);

if (Lazy::isLazyModalRequest()) {
    unset($this->toolbar['form-submit']);
}

echo $form->fields([
    [
        'class' => CardField::class,
        'inputOptions' => [
            'id' => Html::getInputId($model, 'general_section'),
        ],
        'fields' => [
            [
                'class' => ContainerField::class,
                'inputOnly' => true,
                'fields' => [
                    [
                        'size' => 'col-md-6',
                        'field' => [
                            'class' => MultiField::class,
                            'inputOnly' => true,
                            'fields' => [
                                [
                                    'attribute' => 'type',
                                    'type' => ActiveField::TYPE_RADIO_LIST,
                                    'inputOptions' => [
                                        'itemOptions' => [
                                            'custom' => true,
                                            'inline' => true,
                                        ],
                                    ],
                                    'horizontalCssClasses' => [
                                        'input' => 'col-md-9 d-flex align-items-center',
                                    ],
                                    'source' => Customer::types(),
                                ],
                                [
                                    'attribute' => 'group_id',
                                    'type' => ActiveField::TYPE_WIDGET,
                                    'inputOptions' => [
                                        'prompt' => '',
                                        'class' => 'form-control',
                                    ],
                                    'widget' => [
                                        'class' => CustomerGroupInput::class,
                                        'aliasAttribute' => 'new_group',
                                        'allowClear' => true,
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
        'class' => CardField::class,
        'label' => Yii::t('app', 'Company Detail'),
        'inputOptions' => [
            'id' => Html::getInputId($model, 'company_detail_section'),
        ],
        'card' => [
            'icon' => 'i8:file',
        ],
        'fields' => [
            [
                'class' => ContainerField::class,
                'inputOnly' => true,
                'inputOptions' => [
                    'class' => 'row',
                ],
                'fields' => [
                    [
                        'size' => 'col-md-6',
                        'field' => [
                            'class' => MultiField::class,
                            'inputOnly' => true,
                            'fields' => [
                                'company_name',
                                'phone',
                                'email',
                                'fax',
                                'vat_number',
                                [
                                    'attribute' => 'uploaded_company_logo',
                                    'type' => ActiveField::TYPE_WIDGET,
                                    'widget' => [
                                        'class' => FileUploaderInput::class,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'size' => 'col-md-6',
                        'field' => [
                            'class' => MultiField::class,
                            'inputOnly' => true,
                            'fields' => [
                                'city',
                                'province',
                                [
                                    'attribute' => 'country_code',
                                    'type' => ActiveField::TYPE_WIDGET,
                                    'widget' => [
                                        'class' => CountryInput::class,
                                    ],
                                ],
                                [
                                    'attribute' => 'address',
                                    'type' => ActiveField::TYPE_TEXTAREA,
                                ],
                                'postal_code',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    [
        'class' => CardField::class,
        'label' => Yii::t('app', 'Personal Contact Detail'),
        'inputOptions' => [
            'id' => Html::getInputId($model, 'personal_detail_section'),
        ],
        'card' => [
            'icon' => 'i8:file',
        ],
        'fields' => [
            [
                'class' => ContainerField::class,
                'inputOnly' => true,
                'inputOptions' => [
                    'class' => 'row',
                ],
                'fields' => [
                    [
                        'size' => 'col-md-6',
                        'field' => [
                            'class' => MultiField::class,
                            'inputOnly' => true,
                            'fields' => [
                                [
                                    'class' => ContainerField::class,
                                    'label' => Yii::t('app', 'Name'),
                                    'fields' => [
                                        [
                                            'size' => 'col-md-6',
                                            'field' => [
                                                'attribute' => 'first_name',
                                                'placeholder' => true,
                                                'standalone' => true,
                                                'model' => $model->primaryContactModel,
                                            ],
                                        ],
                                        [
                                            'size' => 'col-md-6',
                                            'field' => [
                                                'attribute' => 'last_name',
                                                'placeholder' => true,
                                                'standalone' => true,
                                                'model' => $model->primaryContactModel,
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'attribute' => 'phone',
                                    'model' => $model->primaryContactModel,
                                ],
                                [
                                    'attribute' => 'mobile',
                                    'model' => $model->primaryContactModel,
                                ],
                                [
                                    'attribute' => 'email',
                                    'model' => $model->primaryContactModel,
                                ],
                                [
                                    'attribute' => 'has_customer_area_access',
                                    'model' => $model->primaryContactModel,
                                    'type' => ActiveField::TYPE_CHECKBOX,
                                    'label' => '',
                                    'inputOptions' => [
                                        'custom' => true,
                                    ],
                                ],
                                [
                                    'class' => ContainerField::class,
                                    'label' => Yii::t('app', 'Password'),
                                    'inputOptions' => [
                                        'id' => 'password-container',
                                        'class' => 'form-row',
                                    ],
                                    'fields' => [
                                        [
                                            'size' => 'col-md-6',
                                            'field' => [
                                                'attribute' => 'password',
                                                'standalone' => true,
                                                'model' => $model->primaryContactModel->accountModel,
                                                'type' => ActiveField::TYPE_PASSWORD,
                                                'inputOptions' => [
                                                    'value' => '',
                                                    'class' => 'form-control',
                                                ],
                                            ],
                                        ],
                                        [
                                            'size' => 'col-md-6',
                                            'field' => [
                                                'attribute' => 'password_repeat',
                                                'standalone' => true,
                                                'label' => '',
                                                'placeholder' => Yii::t('app', 'Repeat the password'),
                                                'model' => $model->primaryContactModel->accountModel,
                                                'type' => ActiveField::TYPE_PASSWORD,
                                                'inputOptions' => [
                                                    'value' => '',
                                                    'class' => 'form-control',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'size' => 'col-md-6',
                        'field' => [
                            'class' => MultiField::class,
                            'inputOnly' => true,
                            'fields' => [
                                [
                                    'attribute' => 'city',
                                    'model' => $model->primaryContactModel,
                                ],
                                [
                                    'attribute' => 'province',
                                    'model' => $model->primaryContactModel,
                                ],
                                [
                                    'attribute' => 'country_code',
                                    'type' => ActiveField::TYPE_WIDGET,
                                    'model' => $model->primaryContactModel,
                                    'widget' => [
                                        'class' => CountryInput::class,
                                    ],
                                ],
                                [
                                    'attribute' => 'address',
                                    'type' => ActiveField::TYPE_TEXTAREA,
                                    'model' => $model->primaryContactModel,
                                ],

                                [
                                    'attribute' => 'postal_code',
                                    'model' => $model->primaryContactModel,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
]);

echo $this->block('@form:end', compact('form'));

Form::end();

$this->registerJs("$('#{$form->id}').customerForm()");

echo $this->block('@end');
