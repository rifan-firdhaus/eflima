<?php

use modules\account\web\admin\View;
use modules\account\widgets\inputs\StaffInput;
use modules\crm\models\Lead;
use modules\crm\models\LeadFollowUpType;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\inputs\DatepickerInput;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\ArrayHelper;

/**
 * @var View  $this
 * @var Lead  $model
 * @var array $formOptions
 */

if (!isset($formOptions)) {
    $formOptions = [];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'id' => 'lead-form',
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
                'attribute' => 'date',
                'type' => ActiveField::TYPE_WIDGET,
                'widget' => [
                    'class' => DatepickerInput::class,
                ],
            ],
            [
                'attribute' => 'staff_id',
                'type' => ActiveField::TYPE_WIDGET,
                'widget' => [
                    'class' => StaffInput::class,
                ],
            ],
            [
                'attribute' => 'type_id',
                'type' => ActiveField::TYPE_RADIO_LIST,
                'source' => LeadFollowUpType::find()->enabled()->map('id', 'label'),
                'horizontalCssClasses' => [
                    'input' => 'col-md-9 d-flex align-items-center',
                ],
                'inputOptions' => [
                    'encode' => false,
                    'class' => 'd-flex w-100 flex-wrap',
                    'itemOptions' => [
                        'inline' => false,
                        'custom' => true,
                        'containerOptions' => [
                            'class' => 'w-50 mb-1',
                        ],
                    ],
                ],
            ],
            [
                'attribute' => 'note',
                'type' => ActiveField::TYPE_TEXTAREA,
            ],
        ],
    ],
]);

echo $this->block('@form:end', compact('form'));

Form::end();

echo $this->block('@end');
