<?php namespace modules\ui\widgets\data_table\columns;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\ui\widgets\table\cells\Cell;
use yii\data\Pagination;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class SerialColumn extends Column
{
    public $headerCell = [
        'class' => Cell::class,
        'options' => ['class' => 'fixed-column serial-column'],
    ];
    public $contentCell = [
        'class' => Cell::class,
        'tagName' => 'th',
        'options' => ['class' => 'fixed-column serial-column'],
    ];

    /**
     * @inheritdoc
     */
    protected function renderContent($model, $id, $index)
    {
        $offset = 0;
        $pagination = $this->dataTable->dataProvider->pagination;

        if ($pagination instanceof Pagination) {
            $offset = $pagination->getOffset();
        }

        return $offset + $index + 1;
    }

    /**
     * @inheritdoc
     */
    public function normalize()
    {
        if(!$this->label){
            $this->label = '#';
        }

        if (!isset($this->headerCell['options']['width'])) {
            $this->headerCell['options']['width'] = '20px';
        }

        if (!isset($this->headerCell['hAlign'])) {
            $this->headerCell['hAlign'] = Cell::H_ALIGN_CENTER;
        }

        if (!isset($this->contentCell['hAlign'])) {
            $this->contentCell['hAlign'] = Cell::H_ALIGN_CENTER;
        }

        parent::normalize();
    }
}