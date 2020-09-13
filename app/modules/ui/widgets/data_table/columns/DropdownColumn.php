<?php namespace modules\ui\widgets\data_table\columns;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use yii\bootstrap4\ButtonDropdown;
use yii\helpers\ArrayHelper;
use function call_user_func;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class DropdownColumn extends DataColumn
{
    public $items = [];
    public $format = 'raw';

    /** @var array|ButtonDropdown */
    public $buttonDropdown = [
        'class' => ButtonDropdown::class,
    ];

    public function renderContent($model, $id, $index)
    {
        $items = $this->items;
        $buttonDropdown = $this->buttonDropdown;

        if ($items instanceof Closure) {
            $items = call_user_func($items, $model, $id, $index);
        }

        if ($buttonDropdown instanceof Closure) {
            $buttonDropdown = call_user_func($buttonDropdown, $model, $id, $index);
        }

        $buttonDropdown['dropdown']['items'] = $items;
        $buttonDropdown['label'] = $this->getColumnValue($model, $id, $index);

        $class = ArrayHelper::remove($buttonDropdown, 'class', ButtonDropdown::class);

        return $class::widget($buttonDropdown);
    }
}