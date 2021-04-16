<?php

use modules\account\web\admin\View;
use modules\ui\widgets\Menu;

/**
 * @var View   $this
 * @var string $active
 * @var string $content
 */

if (!isset($active)) {
    $active = 'knowledge-base';
}

$this->title = Yii::t('app', 'Knowledge Base');
$this->icon = 'i8:open-book';
$this->menu->active = "main/support/knowledge-base";

$this->fullHeightContent = true;

echo $this->block('@begin');
?>
<div class="d-flex h-100 flex-column">
    <?php
    echo Menu::widget([
        'active' => $active,
        'id' => 'knowledge-base-menu',
        'items' => [
            'knowledge-base' => [
                'label' => Yii::t('app', 'Knowledge Base'),
                'url' => ['/support/admin/knowledge-base/index'],
                'icon' => 'i8:open-book',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.knowledge-base.list')
            ],
            'category' => [
                'label' => Yii::t('app', 'Category'),
                'url' => ['/support/admin/knowledge-base-category/index'],
                'icon' => 'i8:category',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.knowledge-base.category.list')
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
