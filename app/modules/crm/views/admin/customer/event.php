<?php

use modules\account\web\admin\View;
use modules\calendar\models\forms\event\EventSearch;
use modules\crm\models\Customer;
use yii\data\ActiveDataProvider;
use yii\helpers\ReplaceArrayValue;

/**
 * @var Customer           $model
 * @var EventSearch        $searchModel
 * @var ActiveDataProvider $dataProvider
 * @var View               $this
 */

$active = 'event';
$this->subTitle = Yii::t('app', 'Event');

$this->beginContent('@modules/crm/views/admin/customer/components/view-layout.php', compact('model', 'active'));
echo $this->block('@begin');

echo $this->render('@modules/calendar/views/admin/event/components/data-view', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
    'dataViewOptions' => [
        'searchAction' => new ReplaceArrayValue($searchModel->searchUrl('/crm/admin/customer/view', [
            'id' => $model->id,
            'action' => 'event',
        ], false)),
    ],
]);

echo $this->block('@end');
$this->endContent();
