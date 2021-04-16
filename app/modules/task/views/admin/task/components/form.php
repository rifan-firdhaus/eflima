<?php

use modules\account\web\admin\View;
use modules\account\widgets\inputs\StaffInput;
use modules\core\validators\DateValidator;
use modules\file_manager\widgets\inputs\FileUploaderInput;
use modules\task\assets\admin\TaskFormAsset;
use modules\task\components\TaskRelation;
use modules\task\models\Task;
use modules\task\models\TaskPriority;
use modules\task\models\TaskStatus;
use modules\task\widgets\inputs\TaskCheckListInput;
use modules\task\widgets\inputs\TaskPriorityInput;
use modules\task\widgets\inputs\TaskStatusInput;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\fields\ContainerField;
use modules\ui\widgets\form\fields\MultiField;
use modules\ui\widgets\form\fields\RawField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\inputs\DatepickerInput;
use modules\ui\widgets\inputs\Select2Input;
use modules\ui\widgets\inputs\TinyMceInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * @var View  $this
 * @var Task  $model
 * @var array $formOptions
 */
TaskFormAsset::register($this);

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
    'id' => 'task-form',
    'model' => $model,
], $formOptions));

echo $this->block('@form:begin', compact('form'));

$this->mainForm($form);

if (empty($model->model)) {
    $modelIdInput = Html::activeHiddenInput($model, 'model_id');
} else {
    $modelIdInput = $model->getRelatedObject()->pickerInput($model, 'model_id');
}

echo $form->fields([
    [
        'class' => ContainerField::class,
        'inputOnly' => true,
        'inputOptions' => [
            'class' => 'form-row m-0',
        ],
        'fields' => [
            [
                'size' => 'col-md-7 p-0',
                'field' => [
                    'class' => CardField::class,
                    'label' => Yii::t('app', 'Basic Information'),
                    'inputOptions' => [
                        'id' => Html::getInputId($model, 'basic_section'),
                    ],
                    'options' => [
                        'class' => 'card h-100',
                    ],
                    'card' => [
                        'icon' => 'i8:file',
                    ],
                    'fields' => [
                        [
                            'attribute' => 'title',
                        ],
                        [
                            'attribute' => 'status_id',
                            'type' => ActiveField::TYPE_WIDGET,
                            'widget' => [
                                'class' => TaskStatusInput::class,
                                'query' => TaskStatus::find()->enabled(),
                            ],
                        ],
                        [
                            'attribute' => 'priority_id',
                            'type' => ActiveField::TYPE_WIDGET,
                            'widget' => [
                                'class' => TaskPriorityInput::class,
                                'query' => TaskPriority::find()->enabled(),
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
                            'label' => Yii::t('app', 'Related to'),
                            'class' => ContainerField::class,
                            'fields' => [
                                [
                                    'size' => 'col-md-6',
                                    'field' => [
                                        'attribute' => 'model',
                                        'type' => ActiveField::TYPE_WIDGET,
                                        'standalone' => true,
                                        'widget' => [
                                            'class' => Select2Input::class,
                                            'source' => TaskRelation::map(),
                                            'prompt' => Yii::t('app', 'None'),
                                        ],
                                    ],
                                ],
                                [
                                    'size' => 'col-md-6',
                                    'field' => [
                                        'class' => RawField::class,
                                        'options' => [
                                            'id' => 'task-model-id-field',
                                        ],
                                        'label' => false,
                                        'layout' => Form::LAYOUT_VERTICAL,
                                        'input' => $modelIdInput,
                                    ],
                                ],
                            ],
                        ],
                        [
                            'attribute' => 'assignee_ids',
                            'type' => ActiveField::TYPE_WIDGET,
                            'widget' => [
                                'class' => StaffInput::class,
                                'is_blocked' => false,
                                'multiple' => true,
                            ],
                        ],
                        [
                            'attribute' => 'description',
                            'type' => ActiveField::TYPE_WIDGET,
                            'widget' => [
                                'inline' => true,
                                'class' => TinyMceInput::class,
                                'type' => TinyMceInput::TYPE_BASIC,
                            ],
                        ],
                        [
                            'attribute' => 'checklists',
                            'type' => ActiveField::TYPE_WIDGET,
                            'widget' => [
                                'class' => TaskCheckListInput::class,
                                'task_id' => $model->id,
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
                'size' => 'col-md-5 p-0 border-left',
                'field' => [
                    'class' => CardField::class,
                    'options' => [
                        'class' => 'card h-100',
                    ],
                    'card' => [
                        'icon' => 'i8:settings',
                    ],
                    'label' => Yii::t('app', 'Settings'),
                    'fields' => [
                        [
                            'attribute' => 'progress_calculation',
                            'type' => ActiveField::TYPE_RADIO_LIST,
                            'standalone' => true,
                            'source' => Task::progressCalculations(),
                            'options' => [
                                'class' => 'mb-4 form-group',
                            ],
                            'inputOptions' => [
                                'itemOptions' => [
                                    'custom' => true,
                                    'containerOptions' => ['class' => 'mb-2'],
                                ],
                            ],
                        ],
                        [
                            'attribute' => 'visibility',
                            'type' => ActiveField::TYPE_RADIO_LIST,
                            'standalone' => true,
                            'source' => Task::visibilities(),
                            'options' => [
                                'class' => 'mb-4 form-group',
                            ],
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
                        [
                            'attribute' => 'is_customer_allowed_to_comment',
                            'type' => ActiveField::TYPE_CHECKBOX,
                            'standalone' => true,
                            'inputOptions' => [
                                'custom' => true,
                            ],
                        ],
                        [
                            'attribute' => 'is_timer_enabled',
                            'type' => ActiveField::TYPE_CHECKBOX,
                            'standalone' => true,
                            'options' => [
                                'class' => 'mt-4 form-group',
                            ],
                            'inputOptions' => [
                                'custom' => true,
                            ],
                        ],
                        [
                            'class' => MultiField::class,
                            'options' => [
                                'class' => 'timer-group'
                            ],
                            'label' => false,
                            'hint' => false,
                            'fields' => [
                                [
                                    'attribute' => 'timer_type',
                                    'type' => ActiveField::TYPE_RADIO_LIST,
                                    'standalone' => true,
                                    'source' => Task::timerTypes(),
                                    'options' => [
                                        'class' => 'pl-4 form-group',
                                    ],
                                    'inputOptions' => [
                                        'itemOptions' => [
                                            'custom' => true,
                                            'containerOptions' => ['class' => 'mb-2'],
                                        ],
                                    ],
                                ],
                                [
                                    'class' => ContainerField::class,
                                    'inputOnly' => true,
                                    'inputOptions' => [
                                        'class' => 'd-flex pl-4 ml-1',
                                    ],
                                    'fields' => [
                                        [
                                            'size' => '',
                                            'field' => [
                                                'attribute' => 'estimation',
                                                'type' => 'number',
                                                'class' => ActiveField::class,
                                                'standalone' => true,
                                                'inputGroups' => ['before' => Yii::t('app', 'Estimation')],
                                            ],
                                        ],
                                        [
                                            'size' => '',
                                            'field' => [
                                                'attribute' => 'estimation_modifier',
                                                'class' => ActiveField::class,
                                                'type' => ActiveField::TYPE_DROP_DOWN_LIST,
                                                'standalone' => true,
                                                'source' => Task::estimationModifiers(),
                                                'inputOptions' => [
                                                    'class' => 'form-control',
                                                    'style' => 'min-width: 100px',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ]
                        ],
                    ],
                ],
            ],
        ],
    ],
]);

echo $this->block('@form:end', compact('form'));

$jsOptions = Json::encode([
    'modelInputUrl' => Url::to(['/task/admin/task/model-input']),
]);

$this->registerJs("$('#{$form->id}').taskForm({$jsOptions})");

Form::end();

echo $this->block('@end');
