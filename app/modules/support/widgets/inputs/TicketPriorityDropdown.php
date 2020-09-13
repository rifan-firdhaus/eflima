<?php namespace modules\support\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\support\models\queries\TicketPriorityQuery;
use modules\support\models\TicketPriority;
use yii\helpers\Html;
use yii\base\InvalidConfigException;
use yii\bootstrap4\ButtonDropdown;
use yii\helpers\ArrayHelper;
use function call_user_func;
use function is_array;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property TicketPriority[]    $models
 * @property TicketPriorityQuery $query
 */
class TicketPriorityDropdown extends ButtonDropdown
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
        $priorityes = $this->getModels();

        foreach ($priorityes AS $priority) {
            $url = $this->url;

            if ($url instanceof Closure) {
                $url = call_user_func($url, $priority);
            }

            $this->dropdown['items'][] = [
                'label' => Html::tag('span', '', ["style" => "background-color: {$priority['color_label']}", 'class' => 'color-description']) . Html::encode($priority['label']),
                'encode' => false,
                'url' => $url,
            ];
        }

        $priority = isset($priorityes[$this->value]) ? $priorityes[$this->value] : TicketPriority::find()->andWhere(['id' => $this->value])->createCommand()->queryOne();
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
            $this->setModels($this->getQuery()->createCommand()->queryAll());
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
     * @param TicketPriorityQuery $query
     */
    public function setQuery($query)
    {
        $this->_query = $query;
    }

    /**
     * @return TicketPriorityQuery
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if (!isset($this->_query)) {
            $this->_query = TicketPriority::find();
        }

        return $this->_query;
    }
}