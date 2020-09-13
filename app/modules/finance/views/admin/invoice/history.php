<?php

use modules\account\models\forms\history\HistorySearch;
use modules\account\web\admin\View;
use modules\account\widgets\history\HistoryWidget;
use modules\finance\models\Invoice;

/**
 * @var View          $this
 * @var Invoice       $model
 * @var HistorySearch $historySearchModel
 */

$this->title = '#' . $model->number;
$this->subTitle = Yii::t('app', 'History');
$this->menu->active = 'main/transaction/invoice';

$this->beginContent('@modules/finance/views/admin/invoice/components/view-layout.php', [
    'model' => $model,
    'active' => 'history',
]);
?>

    <div class="row m-0">
        <div class="col-md-12 py-3">
            <?= HistoryWidget::widget([
                'id' => 'invoice-history',
                'dataProvider' => $historySearchModel->dataProvider,
            ]); ?>
        </div>
    </div>

<?php
$this->endContent();
