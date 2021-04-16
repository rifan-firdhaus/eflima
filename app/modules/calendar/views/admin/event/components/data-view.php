<?php


use modules\account\web\admin\View;
use modules\calendar\assets\admin\EventCalendarAsset;
use modules\calendar\assets\admin\EventDataViewAsset;
use modules\calendar\models\forms\event\EventSearch;
use modules\crm\assets\admin\LeadDataViewAsset;
use modules\ui\widgets\DataView;
use modules\ui\widgets\Icon;
use yii\bootstrap4\ButtonDropdown;
use yii\bootstrap4\ButtonGroup;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * @var View        $this
 * @var EventSearch $searchModel
 * @var array       $dataViewOptions
 */

$dataProvider = $searchModel->dataProvider;

$view = isset($searchModel->params['view']) ? $searchModel->params['view'] : 'default';

echo $this->block('@begin', [
    'dataViewOptions' => &$dataViewOptions,
]);

$addUrl = ArrayHelper::getValue($searchModel->params, 'addUrl', [
    '/calendar/admin/event/add',
    'model' => isset($searchModel->params['model']) ? $searchModel->params['model'] : null,
    'model_id' => isset($searchModel->params['model_id']) ? $searchModel->params['model_id'] : null,
]);


if (!isset($dataViewOptions)) {
    $dataViewOptions = [];
}

$this->fullHeightContent = true;

$dataView = DataView::begin(ArrayHelper::merge([
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider,
    'id' => 'event-data-view',
    'linkPager' => $view === 'calendar' ? false : [
        'pagination' => $dataProvider->pagination,
    ],
    'options' => [
        'class' => 'card data-view h-100',
    ],
    'mainSearchField' => [
        'attribute' => 'q',
    ],
    'bodyOptions' => [
        'class' => 'card-body p-0 h-100',
    ],
    'lazy' => [
        'options' => [
            'class' => 'h-100',
        ],
    ],
    'sort' => $dataProvider->sort,
    'clearSearchUrl' => $searchModel->clearSearchUrl(),
    'searchAction' => $searchModel->searchUrl('/calendar/admin/event/index', [], false),
    'advanceSearchFields' => [

    ],
], $dataViewOptions));

if ($view === 'calendar') {
    $fetchUrl = ArrayHelper::getValue($searchModel->params, 'fetchUrl', [
        '/calendar/admin/event/index',
        'view' => 'calendar',
        'query' => 1,
        'model' => isset($searchModel->params['model']) ? $searchModel->params['model'] : null,
        'model_id' => isset($searchModel->params['model_id']) ? $searchModel->params['model_id'] : null,
    ]);

    EventCalendarAsset::register($this);

    echo Html::tag('div', '', [
        'id' => "event-calendar-{$this->uniqueId}",
        'data-rid' => 'event-calendar',
        'class' => 'calendar-no-border eflima-calendar',
    ]);

    $jsOptions = Json::encode([
        'fetchEventUrl' => Url::to($fetchUrl),
        'updateEventDateUrl' => Url::to(['/calendar/admin/event/update', 'scenario' => 'admin/update/date']),
        'addEventUrl' => Url::to($addUrl),
        'viewEventUrl' => Url::to(['/calendar/admin/event/view']),
    ]);

    $this->registerJs("$('#event-data-view-{$this->uniqueId}').eventCalendar({$jsOptions})");
} else {
    echo $this->render('data-table', compact('dataProvider', 'searchModel'));
}

$dataView->beginHeader();

echo Html::beginTag('div', [
    'class' => 'flex-grow-1 d-flex align-items-center flex-shrink-0',
]);

if ($addUrl !== false) {
    echo Html::a(Icon::show('i8:plus') . Yii::t('app', 'Create'), $addUrl, [
        'class' => 'btn btn-primary',
        'data-lazy-modal' => 'event-form-modal',
        'data-lazy-container' => '#main-container',
        'data-lazy-modal-size' => 'modal-lg',
    ]);
}


echo ButtonDropdown::widget([
    'label' => Yii::t('app', 'Bulk Action'),
    'options' => [
        'class' => 'bulk-actions',
    ],
    'buttonOptions' => [
        'class' => 'ml-1 btn-outline-primary',
    ],
    'dropdown' => [
        'items' => [
            [
                'label' => Yii::t('app', 'Delete'),
                'url' => ['/calendar/admin/event/bulk-delete'],
                'linkOptions' => [
                    'class' => 'bulk-delete text-danger',
                    'title' => Yii::t('app', 'Bulk Delete'),
                    'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure?', [
                        'object_name' => Yii::t('app', 'selected {object}',[
                            'object' => Yii::t('app', 'Event')
                        ]),
                    ]),
                    'data-lazy-options' => ['method' => 'DELETE']
                ],
            ],
        ],
    ],
]);

echo Html::tag('div', Yii::t('app', 'View as:'), ['class' => 'ml-3']);

echo ButtonGroup::widget([
    'options' => [
        'class' => 'ml-2',
    ],
    'encodeLabels' => false,
    'buttons' => [
        'as-table' => [
            'label' => Yii::t('app', 'Table'),
            'tagName' => 'a',
            'options' => [
                'class' => 'btn-outline-primary',
                'type' => false,
                'href' => $searchModel->searchUrl($dataView->searchAction, [
                    'view' => 'default',
                ]),
            ],
        ],
        'as-month' => ButtonDropdown::widget([
            'label' => Yii::t('app', 'Calendar'),
            'buttonOptions' => [
                'class' => 'btn-outline-primary',
            ],
            'options' => [
                'class' => 'calendar-type-chooser',
            ],
            'dropdown' => [
                'items' => [
                    'monthly' => [
                        'label' => Yii::t('app', 'Monthly View'),
                        'url' => $searchModel->searchUrl($dataView->searchAction, [
                            'view' => 'calendar',
                            'type' => 'monthly',
                        ]),
                        'linkOptions' => [
                            'data-lazy' => $view !== 'calendar' ? '1' : '0',
                        ],
                    ],
                    'weekly' => [
                        'label' => Yii::t('app', 'Weekly View'),
                        'url' => $searchModel->searchUrl($dataView->searchAction, [
                            'view' => 'calendar',
                            'type' => 'weekly',
                        ]),
                        'linkOptions' => [
                            'data-lazy' => $view !== 'calendar' ? '1' : '0',
                        ],
                    ],
                    'daily' => [
                        'label' => Yii::t('app', 'Daily View'),
                        'url' => $searchModel->searchUrl($dataView->searchAction, [
                            'view' => 'calendar',
                            'type' => 'daily',
                        ]),
                        'linkOptions' => [
                            'data-lazy' => $view !== 'calendar' ? '1' : '0',
                        ],
                    ],
                ],
            ],
        ]),
    ],
]);

if ($view === 'calendar') {
    echo ButtonGroup::widget([
        'options' => [
            'class' => 'ml-2',
        ],
        'encodeLabels' => false,
        'buttons' => [
            'prev' => [
                'label' => Icon::show('i8:double-left'),
                'options' => [
                    'class' => 'btn btn-icon btn-calendar-prev btn-outline-primary',
                ],
            ],
            'today' => [
                'label' => Yii::t('app', 'Today'),
                'options' => [
                    'class' => 'btn btn-icon btn-calendar-today btn-outline-primary',
                ],
            ],
            'next' => [
                'label' => Icon::show('i8:double-right'),
                'options' => [
                    'class' => 'btn btn-icon btn-calendar-next btn-outline-primary',
                ],
            ],
        ],
    ]);
}

echo Html::endTag('div');

$dataView->endHeader();


$dataView->beginHeader();
echo Html::tag('div', '', ['class' => 'flex-shrink-1 w-100 text-center calendar-header font-size-lg font-weight-bold align-self-center']);
$dataView->endHeader();


EventDataViewAsset::register($this);

$this->registerJs("$('#{$dataView->getId()}').eventDataView()");

DataView::end();

echo $this->block('@end');
