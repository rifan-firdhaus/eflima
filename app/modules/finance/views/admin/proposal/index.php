<?php

use modules\account\web\admin\View;
use modules\finance\models\forms\proposal\ProposalSearch;
use yii\data\ActiveDataProvider;

/**
 * @var View               $this
 * @var ProposalSearch     $searchModel
 * @var ActiveDataProvider $dataProvider
 */

$active = 'index';
$this->subTitle = Yii::t('app', "List");

$this->beginContent('@modules/finance/views/admin/proposal/components/index-layout.php', compact('active'));

echo $this->block('@begin');
echo $this->render('components/data-view', compact('searchModel'));
echo $this->block('@end');

$this->endContent();

