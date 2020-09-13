<?php

use modules\account\web\admin\View;
use modules\core\validators\DateValidator;
use modules\project\models\Project;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\fields\ContainerField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\inputs\DatepickerInput;
use yii\helpers\ArrayHelper;

/**
 * @var View    $this
 * @var Project $model
 * @var array   $formOptions
 */

if (!isset($formOptions)) {
    $formOptions = [];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'id' => 'project-milestone-form',
    'model' => $model,
], $formOptions));

echo $this->block('@form:begin', compact('form'));

$this->mainForm($form);

echo $form->fields([
    [
        'class' => CardField::class,
        'fields' => [
            [
                'attribute' => 'name',
            ],
            [
                'attribute' => 'description',
                'type' => ActiveField::TYPE_TEXTAREA,
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
        ],
    ],
]);

echo $this->block('@form:end', compact('form'));

Form::end();

echo $this->block('@end');