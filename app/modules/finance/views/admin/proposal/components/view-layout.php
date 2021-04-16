<?php

use modules\account\web\admin\View;
use modules\finance\assets\admin\ProposalViewAsset;
use modules\finance\models\Proposal;
use modules\ui\widgets\Menu;

/**
 * @var View     $this
 * @var string   $active
 * @var string   $content
 * @var Proposal $model
 */
ProposalViewAsset::register($this);

if (!isset($active)) {
    $active = 'profile';
}

$this->title = $model->title;
$this->menu->active = 'main/finance/proposal';
$this->icon = 'i8:connect';
$this->fullHeightContent = true;

echo $this->block('@begin');


?>

<div class="d-flex h-100 flex-column">
    <?php
    echo Menu::widget([
        'active' => $active,
        'id' => 'customer-view-menu',
        'items' => [
            'profile' => [
                'label' => Yii::t('app', 'Detail'),
                'url' => ['/finance/admin/proposal/detail', 'id' => $model->id],
                'icon' => 'i8:connect',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.proposal.view.detail'),
            ],
            'task' => [
                'label' => Yii::t('app', 'Task'),
                'url' => ['/finance/admin/proposal/task', 'id' => $model->id],
                'icon' => 'i8:checked',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.proposal.view.task'),
            ],
            'history' => [
                'label' => Yii::t('app', 'History'),
                'url' => ['/finance/admin/proposal/history', 'id' => $model->id],
                'icon' => 'i8:activity-history',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'order' => 99,
                'visible' => Yii::$app->user->can('admin.proposal.view.history'),
            ],
        ],
        'options' => [
            'class' => 'nav nav-pills nav-pills-main',
        ],
        'linkOptions' => [
            'class' => 'nav-link',
        ],
        'itemOptions' => [
            'class' => 'nav-item',
        ],
    ]);
    ?>
    <div class="h-100 overflow-auto">
        <?= $content; ?>
    </div>
</div>

<?= $this->block('@end'); ?>
