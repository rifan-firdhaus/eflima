<?php namespace modules\task\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\task\models\query\TaskPriorityQuery;
use modules\task\models\query\TaskStatusQuery;
use modules\task\models\TaskPriority;
use yii\helpers\Html;
use yii\base\InvalidConfigException;
use yii\widgets\InputWidget;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property TaskPriorityQuery $query
 */
class TaskPriorityInput extends InputWidget
{
    public $_query;

    /**
     * @param TaskStatusQuery $query
     */
    public function setQuery($query)
    {
        $this->_query = $query;
    }

    /**
     * @return TaskPriorityQuery
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if (!isset($this->_query)) {
            $this->_query = TaskPriority::find();
        }

        return $this->_query;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $selector = $this->renderSelector();

        if (!$this->model) {
            return Html::hiddenInput($this->name, Html::getAttributeValue($this->model, $this->attribute), $this->options) . $selector;
        }

        return Html::activeHiddenInput($this->model, $this->attribute, $this->options) . $selector;
    }


    public function renderSelector()
    {
        /** @var TaskPriority[] $models */
        $models = $this->getQuery()->createCommand()->queryAll();
        $value = $this->hasModel() ? Html::getAttributeValue($this->model, $this->attribute) : $this->value;
        $result = '';

        foreach ($models AS $model) {
            $backgroundColor = Html::hex2rgba($model['color_label'], 0.1);

            $result .= Html::a(Html::encode($model['label']), "#", [
                'class' => 'task-priority-selector py-1 px-2 d-inline-block rounded mr-1 ' . ($value == $model['id'] ? 'active' : ''),
                'style' => "background:{$backgroundColor};color: {$model['color_label']}",
                'data-color' => $model['color_label'],
                'data-id' => $model['id'],
                'onclick' => "$('#{$this->options['id']}').val({$model['id']});$(this).siblings().removeClass('active');$(this).addClass('active');return false;",
            ]);
        }

        return Html::tag('div', $result, ['class' => 'd-flex align-items-center h-100']);

    }

}