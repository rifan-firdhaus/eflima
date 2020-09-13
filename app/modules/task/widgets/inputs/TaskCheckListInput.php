<?php namespace modules\task\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\base\InputWidget;
use modules\task\assets\admin\TaskCheckListAsset;
use modules\task\models\TaskChecklist;
use yii\helpers\Html;
use modules\ui\widgets\JQueryWidgetTrait;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class TaskCheckListInput extends InputWidget
{
    use JQueryWidgetTrait;

    public $jsOptions = [];
    public $task_id;

    public function registerAsset()
    {
        $this->jsOptions['modelClass'] = $this->hasModel() ? Html::getInputName($this->model, $this->attribute) : $this->name;

        $value = $this->value;

        if ($this->hasModel()) {
            $value = Html::getAttributeValue($this->model, $this->attribute);
        }

        if (!empty($value)) {
            $this->jsOptions['data'] = $value;
        } elseif ($this->task_id && !isset($this->jsOptions['data'])) {
            $models = TaskChecklist::find()->orderBy('order')->andWhere(['task_id' => $this->task_id])->select(['label', 'is_checked', 'id'])->createCommand()->queryAll();
            $this->jsOptions['data'] = $models;
        }

        TaskCheckListAsset::register($this->view);

        $this->registerPlugin('taskCheckList');
    }

    public function run()
    {
        $this->registerAsset();

        return Html::tag('div', '', $this->options);
    }
}