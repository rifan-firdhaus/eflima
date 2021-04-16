<?php

use modules\account\web\admin\View;
use modules\crm\models\forms\customer\CustomerBulkSetGroup;
use modules\crm\widgets\inputs\CustomerGroupInput;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var View                 $this
 * @var CustomerBulkSetGroup $model
 */


$this->title = Yii::t('app', 'Set Group');


if (!isset($formOptions)) {
    $formOptions = [
        'action' => ['/crm/admin/customer/bulk-set-group'],
    ];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'id' => 'customer-bulk-set-group-form',
    'model' => $model,
], $formOptions));

echo $this->block('@form:begin', compact('form'));

$this->mainForm($form);

if (Lazy::isLazyModalRequest()) {
    unset($this->toolbar['form-submit']);
}

foreach ($model->id AS $key => $id) {
    echo Html::activeHiddenInput($model, 'id[]',[
        'value' => $id,
        'id' => Html::getInputId($model, 'id[]').'-'.$key
    ]);
}

echo $form->fields([
    [
        'class' => CardField::class,
        'fields' => [
            [
                'attribute' => 'group_id',
                'type' => ActiveField::TYPE_WIDGET,
                'inputOnly' => true,
                'widget' => [
                    'class' => CustomerGroupInput::class,
                    'allowAdd' => false,
                ],
            ],
        ],
    ],
]);

echo $this->block('@form:end', compact('form'));

Form::end();

echo $this->block('@end');
