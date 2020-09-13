<?php namespace modules\support\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\support\models\queries\TicketStatusQuery;
use modules\support\models\TicketStatus;
use yii\helpers\Html;
use yii\base\InvalidConfigException;
use yii\bootstrap4\ButtonDropdown;
use yii\helpers\ArrayHelper;
use function call_user_func;
use function is_array;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property TicketStatus[]    $models
 * @property TicketStatusQuery $query
 */
class TicketStatusDropdown extends ButtonDropdown
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
        $statuses = $this->getModels();

        foreach ($statuses AS $status) {
            $url = $this->url;

            if ($url instanceof Closure) {
                $url = call_user_func($url, $status);
            }

            $this->dropdown['items'][] = [
                'label' => Html::tag('span', '', ["style" => "background-color: {$status['color_label']}", 'class' => 'color-description']) . Html::encode($status['label']),
                'encode' => false,
                'url' => $url,
            ];
        }

        $status = isset($statuses[$this->value]) ? $statuses[$this->value] : TicketStatus::find()->andWhere(['id' => $this->value])->createCommand()->queryOne();
        $backgroundColor = Html::hex2rgba($status['color_label'], 0.1);

        $this->label = $status['label'];

        $this->buttonOptions = ArrayHelper::merge($this->buttonOptions, [
            'style' => "background-color: {$backgroundColor};color:{$status['color_label']}",
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
     * @param TicketStatusQuery $query
     */
    public function setQuery($query)
    {
        $this->_query = $query;
    }

    /**
     * @return TicketStatusQuery
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if (!isset($this->_query)) {
            $this->_query = TicketStatus::find();
        }

        return $this->_query;
    }
}