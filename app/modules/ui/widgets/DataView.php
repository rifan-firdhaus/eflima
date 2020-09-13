<?php namespace modules\ui\widgets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Exception;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\fields\ContainerField;
use modules\ui\widgets\form\fields\InputField;
use modules\ui\widgets\form\fields\RegularField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\inputs\Select2Input;
use modules\ui\widgets\lazy\Lazy;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\bootstrap4\Modal;
use yii\data\Sort;
use yii\debug\models\timeline\DataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\LinkPager;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class DataView extends Card
{
    public $options = [
        'class' => 'card mb-4 data-view',
    ];

    public $footerOptions = [
        'class' => 'card-footer justify-content-between align-items-center d-flex',
    ];

    public $mainSearchField;
    public $searchFields = [];
    public $advanceSearchFields = [];

    public $searchAction = '';
    public $clearSearchUrl;

    /** @var array|Form|false */
    public $searchForm = [
        'class' => Form::class,
        'layout' => Form::LAYOUT_INLINE,
        'method' => 'get',
        'enableAjaxValidation' => false,
        'autoRenderActions' => false,
        'lazy' => false,
    ];

    /** @var array|Form|false */
    public $advanceSearchForm = [
        'class' => Form::class,
        'lazy' => false,
        'method' => 'get',
        'enableTimestamp' => false,
    ];
    /** @var Model */
    public $searchModel;

    /** @var DataProvider */
    public $dataProvider;

    /** @var array|Lazy */
    public $lazy = [
        'class' => Lazy::class,
    ];
    /** @var array|LinkPager */
    public $linkPager = [];
    public $summary;
    public $summaryOptions = [];
    /** @var Sort|false */
    public $sort;
    /** @var array|Modal */
    protected $advanceSearchModal = [
        'class' => Modal::class,
        'size' => 'modal-dialog-centered modal-dialog-scrollable',
        'closeButton' => [
            'data-dismiss' => 'extended-modal',
        ],
    ];
    /** @var Lazy */
    protected $_lazyClass;

    public function init()
    {
        parent::init();

        if ($this->lazy !== false) {
            $this->_lazyClass = $lazyClass = ArrayHelper::remove($this->lazy, 'class', Lazy::class);

            if (!isset($this->lazy['id'])) {
                $this->lazy['id'] = $this->getRealId() . '-lazy';
            }

            ob_start();
            ob_implicit_flush(false);

            $this->lazy = $lazyClass::begin($this->lazy);
        }

        if ($this->searchForm !== false) {
            if (!isset($this->searchForm['id'])) {
                $this->searchForm['id'] = $this->getRealId() . '-search-form';
            }

            if (isset($this->mainSearchField)) {
                $this->searchFields[] = ArrayHelper::merge([
                    'standalone' => true,
                    'placeholder' => Yii::t('app', 'Search...'),
                    'sort' => 999999,
                    'inputOptions' => [
                        'data-toggle' => 'popover',
                        'data-content' => Yii::t('app', 'Press enter to start searching'),
                        'data-trigger' => 'focus',
                        'data-container' => $this->lazy === false ? "#{$this->options['id']}" : "#{$this->lazy->id}",
                        'class' => 'form-control search-query',
                    ],
                    'options' => [
                        'class' => 'd-none d-sm-block',
                    ],
                ], $this->mainSearchField);
            }

            if (!isset($this->searchForm['action'])) {
                $this->searchForm['action'] = $this->searchAction;
            }

            if (!isset($this->searchForm['model'])) {
                $this->searchForm['model'] = $this->searchModel;
            }
        }

        if ($this->advanceSearchForm !== false) {
            if (!isset($this->advanceSearchForm['id'])) {
                $this->advanceSearchForm['id'] = $this->getRealId() . '-advance-search-form';
            }

            if (!isset($this->advanceSearchModal['id'])) {
                $this->advanceSearchModal['id'] = $this->advanceSearchForm['id'] . '-modal';
            }

            if (!isset($this->advanceSearchModal['title'])) {
                $this->advanceSearchModal['title'] = Yii::t('app', 'Advance Search');
            }

            if (!isset($this->advanceSearchForm['action']) && isset($this->searchForm['action'])) {
                $this->advanceSearchForm['action'] = $this->searchForm['action'];
            }

            if (!isset($this->advanceSearchForm['model'])) {
                $this->advanceSearchForm['model'] = $this->searchForm['model'];
            }
        }

    }

    /**
     * @return bool
     * @throws InvalidConfigException
     */
    public function hasSearch()
    {
        return !empty(Yii::$app->request->get($this->searchModel->formName()));
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->beginHeader();
        $this->renderSearch();
        $this->endHeader();

        if ($this->linkPager !== false) {
            if ($this->summary !== false) {
                $this->addToFooter($this->renderSummary());
            }

            $this->addToFooter($this->renderPagination());
        }

        $result = parent::run();

        if ($this->lazy !== false) {
            echo $result;

            $lazyClass = $this->_lazyClass;
            $lazyClass::end();

            return ob_get_clean();
        }

        return $result;
    }

    /**
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function renderSearch()
    {
        $isAdvanceSearchAvailable = $this->advanceSearchForm !== false && !empty($this->advanceSearchFields);

        if (!$this->searchForm && !$isAdvanceSearchAvailable) {
            return;
        }

        echo Html::beginTag('div', [
            'class' => 'data-table-filter align-self-center text-right flex-grow-1 flex-shrink-1 d-flex justify-content-end',
        ]);

        $this->searchForm = Form::begin($this->searchForm);

        echo $this->searchForm->activeFields($this->searchModel, $this->searchFields);
        echo Html::submitButton('', ['class' => 'd-none']);

        if ($isAdvanceSearchAvailable) {
            echo Html::a([
                'label' => Icon::show('i8:advanced-search', ['class' => 'icon d-none d-sm-inline-block']) . Icon::show('i8:search', ['class' => 'icon d-inline-block d-sm-none']),
                'href' => '#',
                'class' => 'btn btn-outline-primary btn-icon ml-2',
                'title' => $this->advanceSearchModal['title'],
                "data-toggle" => "modal",
                "data-target" => "#{$this->advanceSearchModal['id']}-{$this->view->uniqueId}",
            ]);
        }

        echo Html::endTag('div');

        Form::end();

        if ($this->lazy && $this->searchForm) {
            $this->view->registerJs("$('#{$this->searchForm->id}').on('submit',function(event){event.stopPropagation();event.preventDefault();$(this).closest('[data-rid=\"{$this->lazy->getRealId()}\"]').lazyContainer('load',$(this).attr('action'),$(this).attr('method'),$(this).serialize())})");
        }

        if ($isAdvanceSearchAvailable) {
            $this->renderAdvanceSearch();
        }
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function renderAdvanceSearch()
    {
        $this->advanceSearchModal['title'] = Icon::show('i8:advanced-search') . $this->advanceSearchModal['title'];

        $this->advanceSearchModal = Modal::begin($this->advanceSearchModal);
        $this->advanceSearchForm = Form::begin($this->advanceSearchForm);

        echo $this->advanceSearchForm->activeFields($this->searchModel, $this->advanceSearchFields);

        $sortOptions = [];
        $currentOrderConfig = $this->sort->getOrders();
        $order = reset($currentOrderConfig);
        $sort = key($currentOrderConfig);
        $sortParam = ($order === SORT_DESC ? "-" : "") . $sort;

        foreach ($this->sort->attributes AS $id => $attribute) {
            $sortOptions[$id] = $attribute['label'];
        }

        echo $this->advanceSearchForm->fields([
            [
                'class' => CardField::class,
                'label' => Yii::t('app', 'Sorting'),
                'card' => [
                    'icon' => 'i8:sorting',
                ],
                'fields' => [
                    [
                        'class' => ContainerField::class,
                        'inputOnly' => true,
                        'inputOptions' => [
                            'class' => 'd-flex align-items-center',
                        ],
                        'fields' => [
                            [
                                'size' => 'flex-grow-1 w-100 mr-3',
                                'field' => [
                                    'class' => RegularField::class,
                                    'options' => [
                                        'class' => 'm-0',
                                    ],
                                    'inputOptions' => [
                                        'id' => $this->advanceSearchForm->id . '-sort-field',
                                    ],
                                    'value' => $sort,
                                    'name' => 'sort',
                                    'standalone' => true,
                                    'type' => InputField::TYPE_WIDGET,
                                    'widget' => [
                                        'class' => Select2Input::class,
                                        'source' => $sortOptions,
                                    ],
                                ],
                            ],
                            [
                                'size' => 'flex-shrink-1',
                                'field' => [
                                    'class' => RegularField::class,
                                    'standalone' => true,
                                    'type' => InputField::TYPE_RADIO_LIST,
                                    'options' => [
                                        'class' => 'text-nowrap',
                                    ],
                                    'value' => $order === SORT_ASC || !$order ? 'asc' : 'desc',
                                    'name' => 'order',
                                    'inputOptions' => [
                                        'id' => $this->advanceSearchForm->id . '-order-field',
                                        'encode' => false,
                                        'itemOptions' => [
                                            'custom' => true,
                                            'class' => 'nowrap',
                                            'containerOptions' => [
                                                'class' => 'mb-1',
                                            ],
                                        ],
                                    ],
                                    'source' => [
                                        'asc' => Icon::show('i8:ascending-sorting', ['class' => 'icons8-size text-primary mr-1']) . Yii::t('app', 'Ascending'),
                                        'desc' => Icon::show('i8:descending-sorting', ['class' => 'icons8-size text-primary mr-1']) . Yii::t('app', 'Descending'),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        echo Html::hiddenInput($this->sort->sortParam, $sortParam, ['id' => $this->advanceSearchForm->id . '-real-sort-field']);

        $this->advanceSearchForm->formActionsSections['secondary'] = [
            'sort' => -1,
        ];

        $this->advanceSearchForm->addAction(
            Html::a(
                Icon::show('i8:refresh') . Yii::t('app', 'Clear Search'),
                isset($this->clearSearchUrl) ? $this->clearSearchUrl : $this->advanceSearchForm->action,
                [
                    'class' => 'btn btn-outline-primary',
                    'onclick' => "$('#{$this->advanceSearchModal->id}').modal('hide')",
                ]
            ),
            'clear',
            'secondary'
        );

        $this->advanceSearchForm->addAction(
            Html::button(
                Icon::show('i8:search') . Yii::t('app', 'Search'),
                [
                    'class' => 'btn btn-primary',
                    'onclick' => "$('#{$this->advanceSearchForm->id}').trigger('submit')",
                ]
            ),
            'save'
        );

        Form::end();

        Modal::end();

        $submitJs = <<<JS
$('#{$this->advanceSearchForm->id}').on('submit',function(event){
    event.stopPropagation();
    event.preventDefault();
    
    $('#{$this->advanceSearchModal->id}').modal('hide');
    $(this).closest('[data-rid=\'{$this->lazy->getRealId()}\']').lazyContainer('load',$(this).attr('action'),$(this).attr('method'),$(this).serialize())
});
JS;
        $sortJs = <<<JS
$("#{$this->advanceSearchForm->id}-sort-field,#{$this->advanceSearchForm->id}-order-field input[type=radio]").on('change',function(){
    var sort = $("#{$this->advanceSearchForm->id}-sort-field").val();
    var order = $("#{$this->advanceSearchForm->id}-order-field input[type=radio]:checked").val();
    var value = (order === 'asc' ? '' : '-') + sort;
    
    if(sort === ''){
      value = "";
    }            
    
    $("#{$this->advanceSearchForm->id}-real-sort-field").val(value);
})
JS;

        $this->view->registerJs($sortJs);
        $this->view->registerJs($submitJs);
    }

    /**
     * @return string
     */
    public function renderSummary()
    {
        $count = $this->dataProvider->getCount();

        if ($count <= 0) {
            return '';
        }

        $summaryOptions = $this->summaryOptions;
        $tag = ArrayHelper::remove($summaryOptions, 'tag', 'div');

        if (($pagination = $this->dataProvider->getPagination()) !== false) {
            $totalCount = $this->dataProvider->getTotalCount();
            $begin = $pagination->getPage() * $pagination->pageSize + 1;
            $end = $begin + $count - 1;

            if ($begin > $end) {
                $begin = $end;
            }

            $page = $pagination->getPage() + 1;
            $pageCount = $pagination->pageCount;

            if (($summaryContent = $this->summary) === null) {
                return Html::tag($tag, Yii::t('app', 'Showing <b>{begin, number}-{end, number}</b> of <b>{totalCount, number}</b> {totalCount, plural, one{item} other{items}}.', [
                    'begin' => $begin,
                    'end' => $end,
                    'count' => $count,
                    'totalCount' => $totalCount,
                    'page' => $page,
                    'pageCount' => $pageCount,
                ]), $summaryOptions);
            }
        } else {
            $begin = $page = $pageCount = 1;
            $end = $totalCount = $count;

            if (($summaryContent = $this->summary) === null) {
                return Html::tag($tag, Yii::t('app', 'Total <b>{count, number}</b> {count, plural, one{item} other{items}}.', [
                    'begin' => $begin,
                    'end' => $end,
                    'count' => $count,
                    'totalCount' => $totalCount,
                    'page' => $page,
                    'pageCount' => $pageCount,
                ]), $summaryOptions);
            }
        }

        return Yii::$app->getI18n()->format($summaryContent, [
            'begin' => $begin,
            'end' => $end,
            'count' => $count,
            'totalCount' => $totalCount,
            'page' => $page,
            'pageCount' => $pageCount,
        ], Yii::$app->language);
    }

    /**
     * @return string
     */
    public function renderPagination()
    {
        $paginationClass = ArrayHelper::remove($this->linkPager, 'class', LinkPager::class);

        return $paginationClass::widget($this->linkPager);
    }
}
