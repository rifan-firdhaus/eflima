<?php

use modules\account\web\admin\View;
use modules\finance\assets\admin\ExpenseBillablePickerAsset;
use modules\finance\models\forms\expense\ExpenseSearch;
use modules\finance\models\Invoice;
use modules\ui\widgets\Icon;
use yii\bootstrap4\Alert;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * @var View          $this
 * @var ExpenseSearch $searchModel
 * @var Invoice       $invoice
 */

ExpenseBillablePickerAsset::register($this);

$this->title = Yii::t('app', 'Add');
$this->subTitle = Yii::t('app', 'Billable Expense');

$this->icon = 'i8:shipping-container';
$this->menu->active = 'main/transaction/invoice';

$temp = Yii::$app->request->get('temp');

echo $this->block('@begin');

$this->beginContent('@modules/finance/views/admin/invoice-item/components/modify-layout.php', [
    'model' => !$temp ? $invoice : null,
    'active' => 'expense',
]);

echo Alert::widget([
    'body' => Icon::show('i8:info', ['class' => 'icon icons8-size mr-2']) . Yii::t('app', 'Select expenses you want to bill to this invoice'),
    'closeButton' => false,
    'options' => [
        'class' => 'alert-primary m-2',
    ],
]);

echo Html::beginTag('div', [
    'id' => 'expense-billable-picker',
]);

echo $this->render('components/data-table', [
    'searchModel' => $searchModel,
    'dataProvider' => $searchModel->dataProvider,
    'picker' => true,
]);
?>
    <div class="form-action border-top">
        <div class="align-self-end ml-auto">
            <button type="submit" class="btn btn-primary btn-submit-billable-picker"><i class="icon icons8-paper-plane"></i>Save</button>
        </div>
    </div>
<?php
echo Html::endTag('div');

$jsOptions = Json::encode([
    'url' => Url::to([
        '/finance/admin/expense/billable-picker',
        'invoice_id' => !$temp ? $invoice->id : null,
        'temp' => $temp,
    ]),
]);

$this->registerJs("$('#expense-billable-picker').expenseBillablePicker({$jsOptions})");

$this->endContent();

echo $this->block('@end');