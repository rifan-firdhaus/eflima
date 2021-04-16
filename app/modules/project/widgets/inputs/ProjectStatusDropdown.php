<?php namespace modules\project\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\project\models\queries\ProjectStatusQuery;
use modules\project\models\ProjectStatus;
use yii\helpers\Html;
use yii\base\InvalidConfigException;
use yii\bootstrap4\ButtonDropdown;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property ProjectStatus[]    $models
 * @property ProjectStatusQuery $query
 */
class ProjectStatusDropdown extends ButtonDropdown
{
    public $_query;
    public $url = ['/project/admin/project/change-status'];
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
                'linkOptions' => [
                    'data-lazy-options' => ['method' => "POST"]
                ],
                'url' => $url,
            ];
        }

        $status = isset($statuses[$this->value]) ? $statuses[$this->value] : ProjectStatus::find()->andWhere(['id' => $this->value])->createCommand()->queryOne();
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
     * @param ProjectStatusQuery $query
     */
    public function setQuery($query)
    {
        $this->_query = $query;
    }

    /**
     * @return ProjectStatusQuery
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if (!isset($this->_query)) {
            $this->_query = ProjectStatus::find();
        }

        return $this->_query;
    }
}
