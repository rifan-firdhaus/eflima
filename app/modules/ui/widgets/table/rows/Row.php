<?php namespace modules\ui\widgets\table\rows;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use yii\helpers\Html;
use modules\ui\widgets\table\cells\Cell;
use modules\ui\widgets\table\sections\Section;
use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Row extends Component
{
    /** @var Section */
    public $section;

    /** @var array|Cell[] */
    public $cells = [];
    public $id;
    public $index;
    public $options = [];
    public $visible = true;
    public $tagName = 'tr';
    public $cell = [
        'class' => Cell::class,
    ];
    protected $colspanned = 0;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        foreach ($this->cells AS $name => $cell) {
            if (is_string($cell) || is_callable($cell)) {
                $cell = [
                    'class' => Cell::class,
                    'content' => $cell,
                ];
            }

            $this->addCell($name, $cell);
        }
    }

    /**
     * @param $name
     * @param $cell
     *
     * @return Cell
     * @throws InvalidConfigException
     */
    public function addCell($name, $cell = [])
    {
        $cell = ArrayHelper::merge($this->cell, $cell);

        if (!isset($cell['class'])) {
            $cell['class'] = Cell::class;
        }

        $cell['name'] = $name;

        $this->cells[$name] = Yii::createObject($cell);

        return $this->cells[$name];
    }

    /**
     * @param $name
     *
     * @return Cell
     */
    public function getCell($name)
    {
        if (!isset($this->cells[$name])) {
            throw new InvalidArgumentException("Column {$name} doesn't exists");
        }

        return $this->cells[$name];
    }

    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function render()
    {
        if (!$this->visible) {
            return '';
        }

        $this->normalize();

        return Html::tag($this->tagName, $this->renderCells(), $this->options);
    }

    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function renderCells()
    {
        $rendered = '';

        /** @var Cell[] $cells */
        $cells = array_replace(array_flip(array_keys($this->section->grid->header->mainRow->cells)), $this->cells);

        foreach ($cells AS $name => $cell) {
            if($this->colspanned > 0){
                $this->colspanned--;

                continue;
            }

            if (is_integer($cell)) {
                $cell = $this->emptyCell($name);

                $rendered .= $cell->render();
            } else {
                $rendered .= $cell->render();
            }

            if($cell->colspan > 1){
                $this->colspanned =  $cell->colspan - 1;
            }
        }

        return $rendered;
    }

    /**
     * @param $name
     *
     * @return Cell
     * @throws InvalidConfigException
     */
    public function emptyCell($name)
    {
        $cell = Yii::createObject([
            'class' => Cell::class,
            'format' => false,
            'name' => $name,
        ]);

        return $cell;
    }

    /**
     * @return void
     */
    public function normalize()
    {
        $this->options['data-id'] = $this->id;
        $this->options['data-index'] = $this->index;
    }
}