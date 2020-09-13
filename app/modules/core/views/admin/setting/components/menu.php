<?php

use modules\account\web\admin\View;
use modules\ui\widgets\Menu;

/**
 * @var View $this
 */

echo $this->block('@begin');
echo Menu::widget([
    'items' => $this->menu->getTree('setting'),
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
echo $this->block('@end');