<?php

use modules\account\models\Staff;
use modules\account\web\admin\View;
use modules\file_manager\widgets\inputs\FileUploaderInput;
use modules\ui\widgets\Card;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\fields\ContainerField;
use modules\ui\widgets\form\Form;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var View  $this
 * @var Staff $model
 * @var array $formOptions
 */

$isUpdate = !$model->isNewRecord && $model->scenario === 'admin/update';

if (!isset($formOptions)) {
    $formOptions = [];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'id' => 'staff-form',
    'model' => $model,
], $formOptions));

echo $this->block('@form:begin', compact('form'));

$this->mainForm($form);

echo $form->fields([
    [
        'class' => ContainerField::class,
        'inputOptions' => [
            'class' => 'row',
        ],
        'inputOnly' => true,
        'fields' => [
            [
                'size' => 'col-7 pr-0',
                'field' => [
                    'class' => CardField::class,
                    'label' => Yii::t('app', 'Account Detail'),
                    'card' => [
                        'class' => Card::class,
                        'icon' => 'i8:account',
                    ],
                    'options' => [
                        'class' => 'h-100 card',
                    ],
                    'fields' => [
                        [
                            'class' => ContainerField::class,
                            'label' => Yii::t('app', 'Name'),
                            'labelOptions' => ['for' => Html::getInputId($model, 'first_name')],
                            'fields' => [
                                [
                                    'size' => 'col-6',
                                    'field' => [
                                        'attribute' => 'first_name',
                                        'class' => ActiveField::class,
                                        'standalone' => true,
                                    ],
                                ],
                                [
                                    'size' => 'col-6',
                                    'field' => [
                                        'attribute' => 'last_name',
                                        'class' => ActiveField::class,
                                        'standalone' => true,
                                    ],
                                ],
                            ],
                        ],
                        [
                            'attribute' => 'username',
                            'class' => ActiveField::class,
                            'model' => $model->accountModel,
                        ],
                        [
                            'attribute' => 'uploaded_avatar',
                            'class' => ActiveField::class,
                            'model' => $model->accountModel,
                            'type' => ActiveField::TYPE_WIDGET,
                            'widget' => [
                                'class' => FileUploaderInput::class,
                            ],
                        ],
                        [
                            'attribute' => 'email',
                            'class' => ActiveField::class,
                            'model' => $model->accountModel,
                        ],
                        [
                            'attribute' => 'phone',
                            'class' => ActiveField::class,
                            'model' => $model->accountModel->contactModel,
                        ],
                        [
                            'attribute' => 'address',
                            'class' => ActiveField::class,
                            'model' => $model->accountModel->contactModel,
                            'type' => ActiveField::TYPE_TEXTAREA,
                        ],
                    ],
                ],
            ],
            [
                'size' => 'col-5 pl-0 border-left',
                'field' => [
                    'class' => CardField::class,
                    'label' => $isUpdate ? Yii::t('app', 'Change Password') : Yii::t('app', 'Password'),
                    'card' => [
                        'class' => Card::class,
                        'icon' => 'i8:lock',
                    ],
                    'options' => [
                        'class' => 'h-100 card',
                    ],
                    'fields' => [
                        [
                            'attribute' => 'revisor_password',
                            'class' => ActiveField::class,
                            'model' => $model->accountModel,
                            'visible' => $isUpdate,
                            'type' => ActiveField::TYPE_PASSWORD,
                            'inputOptions' => [
                                'value' => '',
                                'class' => 'form-control',
                            ],
                            'horizontalCssClasses' => [
                                'label' => 'col-md-3 col-form-label',
                                'input' => 'col-md-9',
                            ],
                        ],
                        [
                            'attribute' => 'password',
                            'class' => ActiveField::class,
                            'model' => $model->accountModel,
                            'type' => ActiveField::TYPE_PASSWORD,
                            'inputOptions' => [
                                'value' => '',
                                'class' => 'form-control',
                            ],
                            'horizontalCssClasses' => [
                                'label' => 'col-md-3 col-form-label',
                                'input' => 'col-md-9',
                            ],
                        ],
                        [
                            'attribute' => 'password_repeat',
                            'class' => ActiveField::class,
                            'model' => $model->accountModel,
                            'type' => ActiveField::TYPE_PASSWORD,
                            'inputOptions' => [
                                'value' => '',
                                'class' => 'form-control',
                            ],
                            'horizontalCssClasses' => [
                                'label' => 'col-md-3 col-form-label',
                                'input' => 'col-md-9',
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