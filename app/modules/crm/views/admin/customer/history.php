<?php

use modules\account\models\forms\history\HistorySearch;
use modules\account\web\admin\View;
use modules\account\widgets\history\HistoryWidget;
use modules\crm\models\Customer;

/**
 * @var Customer      $model
 * @var View          $this
 * @var HistorySearch $searchModel
 */

$active = 'history';
$this->subTitle = Yii::t('app', 'History');

$this->beginContent('@modules/crm/views/admin/customer/components/view-layout.php', compact('model', 'active'));
echo $this->block('@begin');
?>

    <div class="row m-0">
        <div class="col-md-12 py-3">
            <?= HistoryWidget::widget([
                'id' => 'customer-history',
                'dataProvider' => $searchModel->dataProvider,
            ]); ?>
        </div>
    </div>
<?php
echo $this->block('@end');
$this->endContent();
