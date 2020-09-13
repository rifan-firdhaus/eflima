<?php

use modules\account\web\admin\View;
use modules\ui\widgets\Menu;

/**
 * @var View $this
 */

echo $this->block('@begin');

echo Menu::widget([
    'items' => $this->menu->getTree('sidenav/top'),
    'hideLabel' => true,
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
        'class' => 'navbar-nav menu',
    ],
]);

echo $this->block('@middle');

echo Menu::widget([
    'items' => $this->menu->getTree('sidenav/bottom'),
    'hideLabel' => true,
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
        'class' => 'navbar-nav menu',
    ],
]);

echo $this->block('@end');