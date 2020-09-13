<?php

use modules\account\web\admin\View;
use modules\ui\widgets\Menu;

/**
 * @var View $this
 */

$this->title = Yii::t('app', 'Quick Add');

$this->beginContent('@modules/account/views/layouts/admin/components/side-panel-layout.php');

echo Menu::widget([
    'items' => $this->menu->getTree('quick_access/quick_add'),
    'itemOptions' => [
        'class' => 'nav-item',
    ],
    'linkOptions' => [
        'class' => 'nav-link',
    ],
    'subMenuOptions' => [
        'class' => 'nav',
    ],
    'options' => [
        'class' => 'sidebar-nav nav flex-column menu',
    ],
]);

$this->endContent();