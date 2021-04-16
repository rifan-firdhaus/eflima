<?php namespace modules\task\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\task\models\query\TaskPriorityQuery;
use modules\task\models\query\TaskStatusQuery;
use modules\task\models\TaskPriority;
use yii\helpers\Html;
use yii\base\InvalidConfigException;
use yii\bootstrap4\ButtonDropdown;
use yii\helpers\ArrayHelper;
use function call_user_func;
use function is_array;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property TaskStatusQuery $query
 */
class TaskPriorityDropdown extends ButtonDropdown
{
    public $_query;
    public $url = ['/'];
    public $value;
    public $tagName = 'a';
    protected $_models;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $priorities = $this->getModels();

        foreach ($priorities AS $priority) {
            $url = $this->url;

            if ($url instanceof Closure) {
                $url = call_user_func($url, $priority);
            }

            $this->dropdown['items'][] = [
                'label' => Html::tag('span', '', ["style" => "background-color: {$priority['color_label']}", 'class' => 'color-description']) . Html::encode($priority['label']),
                'encode' => false,
                'url' => $url,
                'linkOptions' => [
                    'data-lazy-options' => ['method' => 'POST']
                ],
            ];
        }

        $priority = isset($priorities[$this->value]) ? $priorities[$this->value] : TaskPriority::find()->andWhere(['id' => $this->value])->createCommand()->queryOne();

        if (!$priority) {
            throw new InvalidConfigException("Priority with id: {$this->value} doesn't exists");
        }

        $backgroundColor = Html::hex2rgba($priority['color_label'], 0.1);

        $this->label = $priority['label'];

        $this->buttonOptions = ArrayHelper::merge($this->buttonOptions, [
            'style' => "background-color: {$backgroundColor};color:{$priority['color_label']}",
        ]);

        Html::addCssClass($this->buttonOptions, ['widget' => 'badge badge-clean text-uppercase px-3 py-2']);

        parent::init();
    }

    public function getModels()
    {
        if (!isset($this->_models)) {
            $this->setModels($this->getQuery()->createCommand()->query());
        }

        return $this->_models;
    }

    public function setModels($models)
    {
        if (!is_array($models)) {
            $models = ArrayHelper::toArray($models);
        }

        $this->_models = ArrayHelper::index($models, 'id');
    }

    /**
     * @param TaskPriorityQuery $query
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
}
