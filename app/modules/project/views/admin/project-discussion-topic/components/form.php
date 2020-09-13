<?php

/**
 * @var View          $this
 * @var ProjectStatus $model
 * @var array         $formOptions
 */

use modules\account\web\admin\View;
use modules\project\models\ProjectStatus;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\inputs\TinyMceInput;
use yii\helpers\ArrayHelper;

if (!isset($formOptions)) {
    $formOptions = [];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'id' => 'project-status-form',
    'model' => $model,
], $formOptions));

echo $this->block('@form:begin', compact('form'));

$this->mainForm($form);

echo $form->fields([
    [
        'class' => CardField::class,
        'fields' => [
            [
                'attribute' => 'subject',
            ],
            [
                'attribute' => 'content',
                'type' => ActiveField::TYPE_WIDGET,
                'widget' => [
                    'class' => TinyMceInput::class,
                    'inline' => true,
                ],
            ],
            [
                'attribute' => 'is_internal',
                'type' => ActiveField::TYPE_CHECKBOX,
                'label' => '',
                'inputOptions' => [
                    'custom' => true
                ]
            ]
        ],
    ],
]);

echo $this->block('@form:end', compact('form'));

Form::end();

echo $this->block('@end');