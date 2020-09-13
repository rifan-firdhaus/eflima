<?php

use modules\account\web\admin\View;
use modules\project\models\forms\project\ProjectSearch;

/**
 * @var View          $this
 * @var ProjectSearch $searchModel
 */
$this->title = Yii::t('app', 'Project');
$this->subTitle = Yii::t('app', 'List');
$this->menu->active = "main/project";

echo $this->block('@begin');
echo $this->render('components/data-view', compact('searchModel'));
echo $this->block('@end');