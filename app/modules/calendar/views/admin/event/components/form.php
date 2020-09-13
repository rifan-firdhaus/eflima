<?php

use modules\account\web\admin\View;
use modules\account\widgets\inputs\StaffInput;
use modules\calendar\assets\admin\EventFormAsset;
use modules\calendar\components\EventRelation;
use modules\calendar\models\Event;
use modules\core\validators\DateValidator;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\fields\ContainerField;
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
 * @var Event $model
 * @var array $formOptions
 */

EventFormAsset::register($this);

if (!isset($formOptions)) {
    $formOptions = [];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'id' => 'event-form',
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
        'class' => CardField::class,
        'fields' => [
            [
                'attribute' => 'name',
            ],
            [
                'label' => Yii::t('app', 'Schedule'),
                'class' => ContainerField::class,
                'fields' => [
                    [
                        'size' => 'col-md-6',
                        'field' => [
                            'attribute' => 'start_date',
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
                            'attribute' => 'end_date',
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
                                'source' => EventRelation::map(),
                                'prompt' => Yii::t('app', 'None'),
                                'allowClear' => true,
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
                'attribute' => 'description',
                'type' => ActiveField::TYPE_WIDGET,
                'widget' => [
                    'class' => TinyMceInput::class,
                    'type' => TinyMceInput::TYPE_FLOATING,
                    'inline' => true,
                ],
            ],
            [
                'attribute' => 'member_ids',
                'type' => ActiveField::TYPE_WIDGET,
                'widget' => [
                    'multiple' => true,
                    'class' => StaffInput::class,
                    'jsOptions' => [
                        'closeOnSelect' => false,
                        'width' => '100%',
                    ],
                ],
            ],
            [
                'attribute' => 'location',
                'type' => ActiveField::TYPE_TEXTAREA,
            ],
        ],
    ],
]);

echo $this->block('@form:end', compact('form'));


$jsOptions = Json::encode([
    'modelInputUrl' => Url::to(['/calendar/admin/event/model-input']),
]);

$this->registerJs("$('#{$form->id}').eventForm({$jsOptions})");

Form::end();

echo $this->block('@end');