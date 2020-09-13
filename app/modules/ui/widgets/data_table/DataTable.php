<?php namespace modules\ui\widgets\data_table;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\ui\widgets\Card;
use modules\ui\widgets\data_table\assets\DataTableAsset;
use modules\ui\widgets\data_table\columns\Column;
use modules\ui\widgets\data_table\columns\DataColumn;
use modules\ui\widgets\Icon;
use modules\ui\widgets\lazy\Lazy;
use modules\ui\widgets\table\rows\Row;
use modules\ui\widgets\table\Table;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\debug\models\timeline\DataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\LinkPager;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class DataTable extends Widget
{
    /** @var DataProvider */
    public $dataProvider;
    public $columns = [];
    public $idAttribute;

    public $searchModel;

    public $options = [
        'class' => 'data-table table-responsive',
    ];

    public $emptyText;

    public $emptyOptions = [
        'class' => 'data-table-empty-text',
    ];

    public $summary;

    public $summaryOptions = [
        'class' => 'data-table-summary',
    ];

    /** @var array|Lazy */
    public $lazy = [
        'class' => Lazy::class,
    ];

    /** @var array|Table */
    public $table = [
        'class' => Table::class,
        'options' => [
            'class' => 'm-0 table table-hover table-striped',
        ],
    ];

    /** @var array|Card */
    public $card = [
        'class' => Card::class,
        'bodyOptions' => [
            'class' => 'p-0',
        ],
    ];

    /** @var array|LinkPager */
    public $linkPager = [
        'class' => LinkPager::class,
        'options' => [
            'class' => 'pagination justify-content-center m-0',
        ],
        'linkContainerOptions' => [
            'class' => 'page-item',
        ],
        'linkOptions' => [
            'class' => 'page-link',
        ],
        'disabledListItemSubTagOptions' => [
            'tag' => 'a',
            'class' => 'page-link',
        ],
    ];

    /** @var null|Closure */
    public $onRenderRow;

    /** @var Table */
    protected $_tableClass;

    /** @var Card */
    protected $_cardClass;

    /** @var Lazy */
    protected $_lazyClass;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->_tableClass = $tableClass = ArrayHelper::remove($this->table, 'class', Table::class);

        $this->table['header']['mainRow'] = [
            'class' => Row::class,
        ];

        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->id;
        }

        $this->options['data-rid'] = $this->getRealId();

        if (!isset($this->emptyText)) {
            $this->emptyText = Icon::show('i8:high-importance', ['class' => 'icons8-size icon']) . Yii::t('app', 'Nothing to show');
        }

        parent::init();

        ob_start();
        ob_implicit_flush(false);

        if ($this->lazy !== false) {
            $this->_lazyClass = $lazyClass = ArrayHelper::remove($this->lazy, 'class', Lazy::class);

            if (!isset($this->lazy['id'])) {
                $this->lazy['id'] = $this->getRealId() . '-lazy';
            }

            $this->lazy = $lazyClass::begin($this->lazy);
        }

        $this->registerClientScript();
        $this->normalizeColumns();

        if ($this->card !== false && !($this->card instanceof Card)) {
            $this->_cardClass = $cardClass = ArrayHelper::remove($this->table, 'class', Card::class);
            $this->card = $cardClass::begin($this->card);
        }

        $this->table = $tableClass::begin($this->table);
    }

    protected function registerClientScript()
    {
        DataTableAsset::register($this->view);

        $js = "$('#{$this->options['id']}').dataTable()";

        $this->view->registerJs($js);
    }

    /**
     * @return void
     * @throws InvalidConfigException
     */
    protected function normalizeColumns()
    {
        $sort = 0;

        foreach ($this->columns as $index => $column) {
            if (is_string($column)) {
                $this->columns[$index] = $this->columnConfigFromText($column);
            }

            if (!isset($this->columns[$index]['sort'])) {
                $this->columns[$index]['sort'] = $sort;
                $sort++;
            } elseif ($sort && $this->columns[$index]['sort'] > $sort) {
                $sort = $this->columns[$index]['sort'];
            }
        }

        ArrayHelper::multisort($this->columns, 'sort');

        foreach ($this->columns AS $index => $column) {
            unset($column['sort']);

            /** @var Column $column */
            $column = Yii::createObject(array_merge([
                'class' => DataColumn::class,
                'dataTable' => $this,
            ], $column));

            if (!$column->visible) {
                unset($this->columns[$index]);

                continue;
            }

            $this->columns[$index] = $column;
        }
    }

    /**
     * @param string $text
     *
     * @return array
     * @throws InvalidConfigException
     */
    protected function columnConfigFromText($text)
    {
        if (!preg_match('/^([^:]+)(:(\w*))?(:(.*))?$/', $text, $matches)) {
            throw new InvalidConfigException('The column must be specified in the format of "attribute", "attribute:format" or "attribute:format:label"');
        }

        return [
            'attribute' => $matches[1],
            'format' => isset($matches[3]) ? $matches[3] : 'text',
            'label' => isset($matches[5]) ? $matches[5] : null,
        ];
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->renderHeader();

        if ($this->dataProvider->totalCount > 0) {
            $this->renderBody();
        }

        $tableClass = $this->_tableClass;

        $tableClass::end();

        if ($this->dataProvider->totalCount == 0) {
            $this->renderEmptyBody();
        }

        if ($this->card !== false && $this->_cardClass) {
            if ($this->dataProvider->pagination) {
                $this->card->addToFooter($this->renderSummary());

                if ($this->linkPager !== false) {
                    $this->card->addToFooter($this->renderPagination());
                }
            }

            $cardClass = $this->_cardClass;
            $cardClass::end();
        }

        if ($this->lazy !== false) {
            $lazyClass = $this->_lazyClass;
            $lazyClass::end();
        }

        $result = Html::tag('div', ob_get_clean(), $this->options);

        return $result;
    }

    /**
     * @return void
     */
    public function renderHeader()
    {
        foreach ($this->columns AS $column) {
            $column->renderHeader();
        }
    }

    /**
     * @return void
     * @throws InvalidConfigException
     */
    protected function renderBody()
    {
        foreach ($this->dataProvider->models AS $model) {
            $this->renderRow($model);
        }
    }

    /**
     * @param $model
     *
     * @throws InvalidConfigException
     */
    protected function renderRow($model)
    {
        $id = ArrayHelper::getValue($model, $this->idAttribute);
        $row = $this->table->body->addRow([], $id);

        if ($this->onRenderRow instanceof Closure) {
            call_user_func($this->onRenderRow, $model, $row, $this);
        }

        foreach ($this->columns AS $column) {
            $column->renderBody($model, $row);
        }
    }

    public function renderEmptyBody()
    {
        echo Html::tag('div', $this->emptyText, $this->emptyOptions);
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
                return Html::tag($tag, Yii::t('yii', 'Total <b>{count, number}</b> {count, plural, one{item} other{items}}.', [
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
        $this->linkPager['pagination'] = $this->dataProvider->pagination;

        return $paginationClass::widget($this->linkPager);
    }
}