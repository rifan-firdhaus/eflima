<?php

use modules\account\models\forms\history\HistorySearch;
use modules\account\web\admin\View;
use modules\account\widgets\history\HistoryWidget;

/**
 * @var View          $this
 * @var HistorySearch $searchModel
 */

$active = 'history'; // Set history tab to active

$this->title = Yii::t('app', 'Proposal');
$this->subTitle = Yii::t('app', 'History');
?>

<?php $this->beginContent('@modules/finance/views/admin/proposal/components/index-layout.php', compact('active')); ?>

    <div class="row m-0">
        <div class="col-md-12 py-3">
            <?= HistoryWidget::widget([
                'id' => 'all-proposal-history',
                'dataProvider' => $searchModel->dataProvider,
            ]); ?>
        </div>
    </div>

<?php $this->endContent();
