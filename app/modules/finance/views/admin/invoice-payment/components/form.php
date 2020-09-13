<?php

use modules\account\web\admin\View;
use modules\core\validators\DateValidator;
use modules\finance\components\Payment;
use modules\finance\models\InvoicePayment;
use modules\finance\widgets\inputs\InvoiceInput;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\inputs\DatepickerInput;
use modules\ui\widgets\inputs\NumericInput;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\ArrayHelper;

/**
 * @var View           $this
 * @var InvoicePayment $model
 * @var array          $formOptions
 */

if (!isset($formOptions)) {
    $formOptions = [];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'id' => 'invoice-payment-form',
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
                'attribute' => 'invoice_id',
                'type' => ActiveField::TYPE_WIDGET,
                'widget' => [
                    'class' => InvoiceInput::class,
                    'is_paid' => 0,
                    'customer_id' => Yii::$app->request->get('customer_id'),
                ],
            ],
            [
                'attribute' => 'method_id',
                'type' => ActiveField::TYPE_RADIO_LIST,
                'source' => Payment::map(),
                'inputOptions' => [
                    'class' => 'h-100 d-flex align-items-center',
                    'itemOptions' => [
                        'custom' => true,
                        'inline' => true,
                    ],
                ],
            ],
            [
                'attribute' => 'amount',
                'type' => ActiveField::TYPE_WIDGET,
                'widget' => [
                    'class' => NumericInput::class,
                ],
            ],
            [
                'attribute' => 'at',
                'type' => ActiveField::TYPE_WIDGET,
                'widget' => [
                    'class' => DatepickerInput::class,
                    'type' => DateValidator::TYPE_DATETIME,
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