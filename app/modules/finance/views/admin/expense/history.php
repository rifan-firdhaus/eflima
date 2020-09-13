<?php

use modules\account\models\forms\history\HistorySearch;
use modules\account\web\admin\View;
use modules\account\widgets\history\HistoryWidget;
use modules\finance\models\Expense;

/**
 * @var View          $this
 * @var Expense       $model
 * @var HistorySearch $historySearchModel
 */

$this->subTitle = Yii::t('app', 'History');

$this->beginContent('@modules/finance/views/admin/expense/components/view-layout.php', [
    'model' => $model,
    'active' => 'history',
]);

echo $this->block('@begin');
?>
    <div class="row m-0">
        <div class="col-md-12 py-3">
            <?= HistoryWidget::widget([
                'id' => 'expense-history',
                'dataProvider' => $historySearchModel->dataProvider,
            ]); ?>
        </div>
    </div>
<?php
echo $this->block('@end');

$this->endContent();
