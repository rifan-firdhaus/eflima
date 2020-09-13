<?php namespace modules\ui\widgets\data_table\columns;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use yii\helpers\Html;
use modules\ui\widgets\table\cells\Cell;
use yii\helpers\Json;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property string $headerCheckBoxName
 */
class CheckboxColumn extends Column
{
    public $name = 'selection[]';
    public $format = 'raw';

    public $checkboxOptions = [];
    public $headerCheckboxOptions = [];
    public $headerCell = [
        'class' => Cell::class,
        'options' => ['class' => 'fixed-column checkbox-column'],
    ];
    public $contentCell = [
        'class' => Cell::class,
        'tagName' => 'th',
        'options' => ['class' => 'fixed-column checkbox-column'],
    ];
    public $selectedClass = 'table-primary';
    public $selector = '.checkbox-column';
    public $multiple = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->registerClientScript();
    }

    public function registerClientScript()
    {
        $id = $this->dataTable->options['id'];
        $options = Json::encode([
            'selector' => $this->selector,
            'selectedClass' => $this->selectedClass,
            'multiple' => $this->multiple,
        ]);
        $js = "$('#{$id}').dataTable('setSelectionColumn',{$options})";
        $this->dataTable->view->registerJs($js);
    }

    public function renderContent($model, $id, $index)
    {
        if ($this->checkboxOptions instanceof Closure) {
            $options = call_user_func($this->checkboxOptions, $model, $id, $index, $this);
        } else {
            $options = $this->checkboxOptions;
        }

        if (!isset($options['value'])) {
            $options['value'] = is_array($id) ? Json::encode($id) : $id;
        }

        $options['custom'] = true;

        return Html::checkbox($this->name, !empty($options['checked']), $options);
    }

    public function renderHeaderContent()
    {
        $options = $this->headerCheckboxOptions;
        $options['custom'] = true;

        return Html::checkbox($this->getHeaderCheckBoxName(), false, $options);
    }

    public function renderHeader()
    {
        parent::renderHeader();

        $this->headerCell->format = 'raw';
    }

    protected function getHeaderCheckBoxName()
    {
        $name = $this->name;
        if (substr_compare($name, '[]', -2, 2) === 0) {
            $name = substr($name, 0, -2);
        }
        if (substr_compare($name, ']', -1, 1) === 0) {
            $name = substr($name, 0, -1) . '_all]';
        } else {
            $name .= '_all';
        }

        return $name;
    }
}