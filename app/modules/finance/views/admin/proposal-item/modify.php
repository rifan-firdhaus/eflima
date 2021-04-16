<?php

use modules\account\web\admin\View;
use modules\finance\models\ProposalItem;
use yii\helpers\Html;

/**
 * @var View        $this
 * @var ProposalItem $model
 */

if ($model->isNewRecord && !isset($model->name)) {
    $this->title = Yii::t('app', 'Add');
    $this->subTitle = Yii::t('app', 'Proposal Item');
} else {
    $this->title = Yii::t('app', 'Update');
    $this->subTitle = Html::encode($model->name);
}

$this->icon = 'i8:shipping-container';
$this->menu->active = 'main/transaction/proposal';

echo $this->block('@begin');

echo $this->render('components/form', compact('model'));

echo $this->block('@end');
