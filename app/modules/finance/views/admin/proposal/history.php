<?php

use modules\account\models\forms\history\HistorySearch;
use modules\account\web\admin\View;
use modules\account\widgets\history\HistoryWidget;
use modules\finance\models\Proposal;

/**
 * @var Proposal      $model
 * @var View          $this
 * @var HistorySearch $historySearchModel
 */

$active = 'history';
$this->subTitle = Yii::t('app', 'History');

$this->beginContent('@modules/finance/views/admin/proposal/components/view-layout.php', compact('model', 'active'));
echo $this->block('@begin');
?>

    <div class="row m-0">
        <div class="col-md-12 py-3">
            <?= HistoryWidget::widget([
                'id' => 'proposal-history',
                'dataProvider' => $historySearchModel->dataProvider,
            ]); ?>
        </div>
    </div>
<?php
echo $this->block('@end');
$this->endContent();
