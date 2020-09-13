<?php

use modules\account\web\admin\View;
use modules\finance\models\forms\invoice\InvoiceSearch;
use modules\project\models\Project;
use yii\data\ActiveDataProvider;
use yii\helpers\ReplaceArrayValue;

/**
 * @var View               $this
 * @var Project            $model
 * @var InvoiceSearch      $searchModel
 */

$this->subTitle = Yii::t('app', 'Invoice');

$this->beginContent('@modules/project/views/admin/project/components/view-layout.php', [
    'model' => $model,
    'active' => 'transaction',
]);

echo $this->block('@begin');

echo $this->render('@modules/finance/views/admin/invoice/components/data-view', [
    'searchModel' => $searchModel,
    'dataViewOptions' => [
        'searchAction' => new ReplaceArrayValue($searchModel->searchUrl('/project/admin/project/view', [
            'id' => $model->id,
            'action' => 'invoice',
        ], false)),
    ],
]);

echo $this->block('@end');

$this->endContent();
