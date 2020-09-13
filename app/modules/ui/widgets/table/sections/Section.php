<?php namespace modules\ui\widgets\table\sections;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use yii\helpers\Html;
use modules\ui\widgets\table\rows\Row;
use modules\ui\widgets\table\Table;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Section extends Component
{
    /** @var Table */
    public $grid;

    /** @var array|Row[] */
    public $rows = [];

    protected $_currentIndex = 0;

    public $options = [];

    public $tagName = 'tbody';

    public $row = [
        'class' => Row::class,
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        foreach ($this->rows AS $index => $row) {
            if ($row instanceof Row) {
                continue;
            }

            $this->addRow($row, $index);
        }
    }

    /**
     * @param array|string $row
     * @param mixed|null   $id
     *
     * @return Row
     * @throws InvalidConfigException
     */
    public function addRow($row = [], $id = null)
    {
        if ($id === null) {
            $id = $this->_currentIndex;
        }

        $row = ArrayHelper::merge($this->row, $row);

        $row['index'] = $this->_currentIndex;
        $row['id'] = $id;

        $this->_currentIndex++;

        if (!isset($row['class'])) {
            $row['class'] = Row::class;
        }

        $row['section'] = $this;

        /** @var Row $row */
        $this->rows[$id] = Yii::createObject($row);

        return $this->rows[$id];
    }

    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function render()
    {
        return Html::tag($this->tagName, $this->renderRows(), $this->options);
    }

    /**
     * @return string
     * @throws InvalidConfigException
     */
    protected function renderRows()
    {
        $rendered = '';

        foreach ($this->rows AS $row) {
            $rendered .= $row->render();
        }

        return $rendered;
    }
}