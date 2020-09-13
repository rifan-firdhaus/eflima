<?php

use modules\account\web\admin\View;
use modules\calendar\models\forms\event\EventSearch;

/**
 * @var View        $this
 * @var EventSearch $searchModel
 */
$this->title = Yii::t('app', 'Event');
$this->menu->active = "main/event";

if ($searchModel->params['view'] === 'calendar') {
    $this->fullHeightContent = true;
}

echo $this->block('@begin');
echo $this->render('components/data-view', compact('searchModel'));
echo $this->block('@end');
