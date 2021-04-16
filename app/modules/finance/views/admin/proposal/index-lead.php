<?php

use modules\account\web\admin\View;
use modules\crm\models\Lead;
use modules\finance\models\forms\proposal\ProposalSearch;

/**
 * @var View           $this
 * @var Lead           $lead
 * @var ProposalSearch $searchModel
 */


$this->subTitle = Yii::t('app', 'Proposal');

$this->beginContent('@modules/crm/views/admin/lead/components/view-layout.php', [
    'model' => $lead,
    'active' => 'proposal',
]);

echo $this->block('@begin');
echo $this->render('components/data-view', compact('searchModel'));
echo $this->block('@end');

$this->endContent();
