<?php

use modules\account\web\admin\View;
use modules\account\widgets\inputs\StaffInput;
use modules\finance\assets\admin\ProposalFormAsset;
use modules\finance\components\ProposalRelation;
use modules\finance\models\Proposal;
use modules\finance\widgets\inputs\CurrencyInput;
use modules\finance\widgets\inputs\ProposalStatusInput;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\fields\ContainerField;
use modules\ui\widgets\form\fields\RawField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\inputs\DatepickerInput;
use modules\ui\widgets\inputs\NumericInput;
use modules\ui\widgets\inputs\Select2Input;
use modules\ui\widgets\inputs\TinyMceInput;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * @var View     $this
 * @var Proposal $model
 * @var array    $formOptions
 */

ProposalFormAsset::register($this);

if (!isset($formOptions)) {
    $formOptions = [];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'id' => 'proposal-form',
    'model' => $model,
], $formOptions));

echo $this->block('@form:begin', compact('form'));

$this->mainForm($form);

if (empty($model->model)) {
    $modelIdInput = Html::activeHiddenInput($model, 'model_id');
} else {
    $modelIdInput = $model->getRelatedObject()->pickerInput($model, 'model_id');
}

if (Lazy::isLazyModalRequest()) {
    unset($this->toolbar['form-submit']);
}

echo $form->fields([
    [
        'class' => ContainerField::class,
        'inputOnly' => true,
        'inputOptions' => [
            'class' => 'form-row mx-0',
        ],
        'fields' => [
            [
                'size' => 'col-md-6 d-flex pr-0',
                'field' => [
                    'class' => CardField::class,
                    'label' => Yii::t('app', 'Proposal Detail'),
                    'inputOptions' => [
                        'id' => 'basic_section',
                    ],
                    'card' => [
                        'icon' => 'i8:cash',
                        'options' => [
                            'class' => 'card flex-grow-1 h-100',
                        ],
                    ],
                    'fields' => [
                        [
                            'attribute' => 'title',
                        ],
                        [
                            'attribute' => 'number',
                        ],
                        [
                            'attribute' => 'date',
                            'type' => ActiveField::TYPE_WIDGET,
                            'widget' => [
                                'class' => DatepickerInput::class,
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
                                            'source' => ProposalRelation::map(),
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
                            'attribute' => 'status_id',
                            'type' => ActiveField::TYPE_WIDGET,
                            'widget' => [
                                'prompt' => '',
                                'class' => ProposalStatusInput::class,
                            ],
                        ],
                        [
                            'class' => ContainerField::class,
                            'label' => Yii::t('app', 'Currency'),
                            'fields' => [
                                [
                                    'size' => 'col-md-12',
                                    'field' => [
                                        'attribute' => 'currency_code',
                                        'standalone' => true,
                                        'type' => ActiveField::TYPE_WIDGET,
                                        'widget' => [
                                            'class' => CurrencyInput::class,
                                            'is_enabled' => true,
                                        ],
                                    ],
                                ],
                                [
                                    'size' => 'col-md-12',
                                    'field' => [
                                        'attribute' => 'currency_rate',
                                        'standalone' => true,
                                        'inputGroups' => [
                                            [
                                                'position' => 'prepend',
                                                'content' => Yii::t('app', 'Rate'),
                                            ],
                                        ],
                                        'type' => ActiveField::TYPE_WIDGET,
                                        'widget' => [
                                            'class' => NumericInput::class,
                                            'clientOptions' => [
                                                'alias' => 'decimal',
                                                'autoGroup' => true,
                                                'rightAlign' => true,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'size' => 'col-md-6 d-flex pl-0 border-left',
                'field' => [
                    'class' => CardField::class,
                    'label' => Yii::t('app', 'Settings'),
                    'inputOptions' => [
                        'id' => 'advance_section',
                    ],
                    'card' => [
                        'icon' => 'i8:file',
                        'options' => [
                            'class' => 'card flex-grow-1 h-100',
                        ],
                    ],
                    'fields' => [
                        [
                            'attribute' => 'assignee_ids',
                            'type' => ActiveField::TYPE_WIDGET,
                            'widget' => [
                                'class' => StaffInput::class,
                                'multiple' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
]);


$rows = [];

foreach ($model->getItems()->orderBy('order')->all() AS $item) {
    $scenario = $item->scenario;

    $item->scenario = 'admin/temp/update';

    foreach ($item->taxes AS $tax) {
        $tax->scenario = 'admin/temp';
    }

    $item->normalizeAttributes();

    $rows[] = [
        'row' => $this->render('/admin/proposal-item/components/item-row', [
            'model' => $item,
        ]),
        'model' => ArrayHelper::toArray($item),
    ];

    $item->scenario = $scenario;
}

echo $this->render('/admin/proposal-item/components/item-table', [
    'model' => $model,
]);

echo $form->fields([
    [
        'class' => CardField::class,
        'label' => Yii::t('app', 'Content'),
        'inputOptions' => [
            'id' => 'content_section',
        ],
        'card' => [
            'icon' => 'i8:file',
            'options' => [
                'class' => 'card flex-grow-1 h-100',
            ],
        ],
        'fields' => [
            [
                'class' => RawField::class,
                'input' => Html::tag('div', '', [
                    'class' => 'content-tinymce-panel',
                ]),
                'inputOnly' => true,
            ],
            [
                'class' => ActiveField::class,
                'attribute' => 'content',
                'type' => ActiveField::TYPE_WIDGET,
                'inputOnly' => true,
                'placeholder' => Yii::t('app', 'Write the content of propoal here...'),
                'widget' => [
                    'class' => TinyMceInput::class,
                    'inline' => true,
                    'jsOptions' => [
                        'fixed_toolbar_container' => "#{$form->options['id']} .content-tinymce-panel",
                    ],
                ],
            ],
        ],
    ],
]);

$jsOptions = Json::encode([
    'modelInputUrl' => Url::to(['/finance/admin/proposal/model-input']),
    'baseCurrency' => Yii::$app->setting->get('finance/base_currency'),
    'reevaluateUrl' => Url::to(['/finance/admin/proposal-item/reevaluate']),
    'rows' => $rows,
]);

$this->registerJs("$('#{$form->id}').proposalForm({$jsOptions})");


echo $this->block('@form:end', compact('form'));

Form::end();

echo $this->block('@end');
