<?php

use modules\account\web\admin\View;
use modules\crm\assets\admin\LeadStatusKanbanAsset;
use modules\crm\models\LeadStatus;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * @var View         $this
 * @var LeadStatus[] $statuses
 */
LeadStatusKanbanAsset::register($this);

$active = 'kanban';
$this->subTitle = Yii::t('app', "Kanban");
$this->beginContent('@modules/crm/views/admin/lead/components/index-layout.php', compact('active'));

echo $this->block('@begin');
?>

<div class="px-3 h-100">
    <div id="lead-status-kanban-<?= $this->uniqueId ?>" class="d-flex py-3 overflow-auto h-100">
        <?php foreach ($statuses AS $status): ?>
            <?= $this->render('components/kanban-item', ['model' => $status]); ?>
        <?php endforeach; ?>
    </div>
</div>

<?php

$jsOptions = Json::encode([
    'sortUrl' => Url::to(['/crm/admin/lead-status/sort']),
    'sortLeadUrl' => Url::to(['/crm/admin/lead-status/sort-lead']),
    'moveLeadUrl' => Url::to(['/crm/admin/lead-status/move-lead']),
    'loadLeadUrl' => Url::to(['/crm/admin/lead-status/lead-list']),
]);

$this->registerJs("$('#lead-status-kanban-{$this->uniqueId}').leadStatusKanban({$jsOptions})");


echo $this->block('@end');

$this->endContent(); ?>
