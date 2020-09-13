<?php

use modules\account\web\admin\View;
use modules\support\models\KnowledgeBaseCategory;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\Form;
use yii\helpers\ArrayHelper;

/**
 * @var View                  $this
 * @var KnowledgeBaseCategory $model
 * @var array                 $formOptions
 */

if (!isset($formOptions)) {
    $formOptions = [];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'id' => 'knowledge-base-category-form',
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
                'class' => ActiveField::class,
                'type' => ActiveField::TYPE_TEXTAREA,
            ],
            [
                'class' => ActiveField::class,
                'attribute' => 'is_enabled',
                'label' => '',
                'type' => ActiveField::TYPE_CHECKBOX,
                'inputOptions' => [
                    'custom' => true,
                ],
            ],
        ],
    ],
]);

echo $this->block('@form:end', compact('form'));

Form::end();

echo $this->block('@end');