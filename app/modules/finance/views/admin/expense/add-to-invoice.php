<?php

use modules\account\web\admin\View;
use modules\finance\models\Expense;
use modules\finance\widgets\inputs\InvoiceInput;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\Form;
use yii\base\DynamicModel;
use yii\helpers\ArrayHelper;

/**
 * @var View         $this
 * @var DynamicModel $formModel
 * @var Expense      $model
 * @var array        $formOptions
 */

$this->title = Yii::t('app', 'Add to Invoice');
$this->menu->active = "main/transaction/expense";

if (!isset($formOptions)) {
    $formOptions = [];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);


$form = Form::begin(ArrayHelper::merge([
    'id' => 'add-to-invoice-form',
    'model' => $formModel,
], $formOptions));

echo $this->block('@form:begin', compact('form'));

echo $form->fields([
    [
        'class' => CardField::class,
        'fields' => [
            [
                'attribute' => 'invoice_id',
                'type' => ActiveField::TYPE_WIDGET,
                'label' => Yii::t('app', 'Invoice'),
                'widget' => [
                    'class' => InvoiceInput::class,
                    'customer_id' => $model->customer_id
                ],
            ],
        ],
    ],
]);

echo $this->block('@form:end', compact('form'));

$this->mainForm($form);

echo $this->block('@end');

Form::end();