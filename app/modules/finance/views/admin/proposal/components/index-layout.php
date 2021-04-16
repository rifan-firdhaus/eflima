<?php

use modules\account\web\admin\View;
use modules\ui\widgets\Menu;

/**
 * @var View   $this
 * @var string $active
 * @var string $content
 */

$this->title = Yii::t('app', 'Proposal');
$this->menu->active = "main/transaction/proposal";
$this->icon = 'i8:handshake';

if (!isset($active)) {
    $active = 'index';
}

$this->fullHeightContent = true;

echo $this->block('@begin');
?>
<div class="d-flex h-100 flex-column">
    <?= Menu::widget([
        'active' => $active,
        'items' => [
            'index' => [
                'label' => Yii::t('app', 'Proposals'),
                'url' => ['/finance/admin/proposal/index'],
                'icon' => 'i8:connect',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.proposal.list'),
            ],
            'history' => [
                'label' => Yii::t('app', 'History'),
                'url' => ['/finance/admin/proposal/all-history'],
                'icon' => 'i8:activity-history',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.proposal.history'),
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

    <?= $this->block('@end'); ?>
</div>
