<?php namespace modules\ui\widgets\data_table\columns;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\ui\widgets\data_table\DataTable;
use modules\ui\widgets\table\cells\Cell;
use modules\ui\widgets\table\rows\Row;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Column extends BaseObject
{
    /** @var DataTable */
    public $dataTable;
    public $visible = true;
    public $attribute;
    public $label;
    public $format = 'text';
    public $content;

    /** @var array|Cell */
    public $headerCell = [
        'class' => Cell::class,
    ];
    public $contentCell = [
        'class' => Cell::class,
        'vAlign' => Cell::V_ALIGN_CENTER,
    ];

    /** @var Closure */
    public $onRenderCell;

    public function renderHeader()
    {
        $this->normalize();

        $mainRow = $this->dataTable->table->header->mainRow;

        $this->headerCell = $mainRow->addCell($this->attribute, ArrayHelper::merge($this->headerCell, [
            'name' => $this->attribute,
            'content' => $this->renderHeaderContent(),
        ]));
    }

    /**
     * @return mixed
     */
    public function renderHeaderContent()
    {
        return $this->label;
    }

    /**
     * @param array|object $model
     * @param Row          $row
     *
     * @throws InvalidConfigException
     */
    public function renderBody($model, $row)
    {
        $cell = $row->addCell($this->attribute, ArrayHelper::merge($this->contentCell, [
            'format' => $this->format,
            'content' => $this->renderContent($model, $row->id, $row->index),
        ]));

        if ($this->onRenderCell) {
            call_user_func($this->onRenderCell, $model, $cell, $this);
        }
    }

    /**
     * @param array|object $model
     * @param mixed        $id
     * @param mixed        $index
     *
     * @return mixed
     */
    protected function renderContent($model, $id, $index)
    {
        return $this->content;
    }

    /**
     * @return void
     */
    public function normalize()
    {
        if (!isset($this->label)) {
            $this->label = Inflector::humanize($this->attribute);
        }

        if (!$this->attribute) {
            $this->attribute = uniqid();
        }

        if (!isset($this->contentCell['class'])) {
            $this->contentCell['class'] = Cell::class;
        }

        if (!isset($this->headerCell['class'])) {
            $this->headerCell['class'] = Cell::class;
        }
    }
}