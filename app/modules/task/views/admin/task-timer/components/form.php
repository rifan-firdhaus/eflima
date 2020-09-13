<?php

use modules\account\web\admin\View;
use modules\account\widgets\inputs\StaffInput;
use modules\core\validators\DateValidator;
use modules\task\models\TaskTimer;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\fields\ContainerField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\inputs\DatepickerInput;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\ArrayHelper;

/**
 * @var View      $this
 * @var TaskTimer $model
 * @var array     $formOptions
 */

if (!isset($formOptions)) {
    $formOptions = [];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'id' => 'task-timer-form',
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
                            'attribute' => 'started_at',
                            'type' => ActiveField::TYPE_WIDGET,
                            'widget' => [
                                'class' => DatepickerInput::class,
                                'type' => DateValidator::TYPE_DATETIME,
                            ],
                        ],
                    ],
                    [
                        'size' => 'col-md-6',
                        'field' => [
                            'attribute' => 'starter_id',
                            'type' => ActiveField::TYPE_WIDGET,
                            'widget' => [
                                'class' => StaffInput::class,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'class' => ContainerField::class,
                'inputOnly' => true,
                'fields' => [
                    [
                        'size' => 'col-md-6',
                        'field' => [
                            'attribute' => 'stopped_at',
                            'type' => ActiveField::TYPE_WIDGET,
                            'widget' => [
                                'class' => DatepickerInput::class,
                                'type' => DateValidator::TYPE_DATETIME,
                            ],
                        ],
                    ],
                    [
                        'size' => 'col-md-6',
                        'field' => [
                            'attribute' => 'stopper_id',
                            'type' => ActiveField::TYPE_WIDGET,
                            'widget' => [
                                'class' => StaffInput::class,
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