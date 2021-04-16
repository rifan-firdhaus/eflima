<?php

use modules\account\web\admin\View;
use modules\calendar\models\forms\event\EventSearch;
use modules\crm\models\Lead;
use yii\helpers\ReplaceArrayValue;

/**
 * @var Lead        $model
 * @var EventSearch $eventSearchModel
 * @var View        $this
 */

$active = 'event';
$this->subTitle = Yii::t('app', 'Event');

$this->beginContent('@modules/crm/views/admin/lead/components/view-layout.php', compact('model', 'active'));
echo $this->block('@begin');

echo $this->render('@modules/calendar/views/admin/event/components/data-view', [
    'searchModel' => $eventSearchModel,
    'dataViewOptions' => [
        'searchAction' => new ReplaceArrayValue($eventSearchModel->searchUrl('/crm/admin/lead/event', [
            'id' => $model->id,
        ], false)),
    ],
]);

echo $this->block('@end');
$this->endContent();
