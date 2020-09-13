<?php

use modules\account\web\admin\View;
use modules\account\widgets\inputs\StaffInput;
use modules\core\validators\DateValidator;
use modules\crm\widgets\inputs\CustomerInput;
use modules\file_manager\widgets\inputs\FileUploaderInput;
use modules\project\models\Project;
use modules\project\widgets\inputs\ProjectStatusInput;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\fields\ContainerField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\inputs\DatepickerInput;
use modules\ui\widgets\inputs\NumericInput;
use modules\ui\widgets\inputs\TinyMceInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var View    $this
 * @var Project $model
 * @var array   $formOptions
 */

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
    'id' => 'project-form',
    'model' => $model,
], $formOptions));

echo $this->block('@form:begin', compact('form'));

$this->mainForm($form);

echo $form->fields([
    [
        'class' => ContainerField::class,
        'inputOnly' => true,
        'inputOptions' => [
            'class' => 'form-row m-0'
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
                            'attribute' => 'customer_id',
                            'type' => ActiveField::TYPE_WIDGET,
                            'widget' => [
                                'class' => CustomerInput::class,
                                'allowAdd' => true,
                            ],
                        ],
                        [
                            'label' => Yii::t('app', 'Schedule'),
                            'class' => ContainerField::class,
                            'fields' => [
                                [
                                    'size' => 'col-md-6',
                                    'field' => [
                                        'attribute' => 'started_date',
                                        'standalone' => true,
                                        'type' => ActiveField::TYPE_WIDGET,
                                        'placeholder' => true,
                                        'widget' => [
                                            'class' => DatepickerInput::class,
                                            'type' => DateValidator::TYPE_DATETIME,
                                        ],
                                    ],
                                ],
                                [
                                    'size' => 'col-md-6',
                                    'field' => [
                                        'attribute' => 'deadline_date',
                                        'standalone' => true,
                                        'type' => ActiveField::TYPE_WIDGET,
                                        'placeholder' => true,
                                        'widget' => [
                                            'class' => DatepickerInput::class,
                                            'type' => DateValidator::TYPE_DATETIME,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'attribute' => 'member_ids',
                            'type' => ActiveField::TYPE_WIDGET,
                            'widget' => [
                                'class' => StaffInput::class,
                                'multiple' => true
                            ]
                        ],
                        [
                            'attribute' => 'budget',
                            'type' => ActiveField::TYPE_WIDGET,
                            'widget' => [
                                'class' => NumericInput::class,
                            ],
                        ],
                        [
                            'attribute' => 'status_id',
                            'type' => ActiveField::TYPE_WIDGET,
                            'widget' => [
                                'class' => ProjectStatusInput::class,
                            ],
                        ],
                        [
                            'attribute' => 'description',
                            'type' => ActiveField::TYPE_WIDGET,
                            'widget' => [
                                'inline' => true,
                                'class' => TinyMceInput::class,
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
                    'label' => Yii::t('app', 'Settings'),
                    'inputOptions' => [
                        'id' => Html::getInputId($model, 'bill_section'),
                    ],
                    'card' => [
                        'icon' => 'i8:settings',
                        'options' => [
                            'class' => 'card flex-grow-1 h-100',
                        ],
                    ],
                    'fields' => [
                        [
                            'attribute' => 'visibility',
                            'type' => ActiveField::TYPE_RADIO_LIST,
                            'source' => Project::visibilities(),
                            'standalone' => true,
                            'inputOptions' => [
                                'itemOptions' => [
                                    'custom' => true,
                                    'containerOptions' => ['class' => 'mb-2'],
                                ],
                            ],
                        ],
                        [
                            'attribute' => 'is_visible_to_customer',
                            'type' => ActiveField::TYPE_CHECKBOX,
                            'standalone' => true,
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

Form::end();

echo $this->block('@end');