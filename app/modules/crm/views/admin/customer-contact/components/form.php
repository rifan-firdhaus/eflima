<?php

use modules\account\web\admin\View;
use modules\address\widgets\inputs\CountryInput;
use modules\crm\models\CustomerContact;
use modules\crm\widgets\inputs\CustomerInput;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\fields\ContainerField;
use modules\ui\widgets\form\fields\MultiField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\ArrayHelper;

/**
 * @var View            $this
 * @var CustomerContact $model
 * @var array           $formOptions
 */

if (!isset($formOptions)) {
    $formOptions = [];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'id' => 'customer-contact-form',
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
                                    'attribute' => 'customer_id',
                                    'type' => ActiveField::TYPE_WIDGET,
                                    'widget' => [
                                        'class' => CustomerInput::class,
                                    ],
                                ],
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
                                            ],
                                        ],
                                        [
                                            'size' => 'col-md-6',
                                            'field' => [
                                                'attribute' => 'last_name',
                                                'placeholder' => true,
                                                'standalone' => true,
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'attribute' => 'phone',
                                ],
                                [
                                    'attribute' => 'mobile',
                                ],
                                [
                                    'attribute' => 'email',
                                ],
                                [
                                    'attribute' => 'has_customer_area_access',
                                    'type' => ActiveField::TYPE_CHECKBOX,
                                    'label' => '',
                                    'inputOptions' => [
                                        'custom' => true,
                                    ],
                                ],
                                [
                                    'class' => ContainerField::class,
                                    'label' => Yii::t('app', 'Password'),
                                    'fields' => [
                                        [
                                            'size' => 'col-md-6',
                                            'field' => [
                                                'attribute' => 'password',
                                                'standalone' => true,
                                                'model' => $model->accountModel,
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
                                                'model' => $model->accountModel,
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
                                ],
                                [
                                    'attribute' => 'province',
                                ],
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

                                [
                                    'attribute' => 'postal_code',
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

echo $this->block('@end');