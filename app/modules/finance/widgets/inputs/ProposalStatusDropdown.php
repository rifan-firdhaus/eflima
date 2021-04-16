<?php namespace modules\finance\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\finance\models\ProposalStatus;
use modules\finance\models\queries\ProposalStatusQuery;
use yii\base\InvalidConfigException;
use yii\bootstrap4\ButtonDropdown;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property ProposalStatus[]    $models
 * @property ProposalStatusQuery $query
 */
class ProposalStatusDropdown extends ButtonDropdown
{
    public $_query;
    public $url = ['/finance/admin/proposal/change-status'];
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
                'linkOptions' => [
                    'data-lazy-options' => ['method' => "POST"]
                ],
            ];
        }

        $status = isset($statuses[$this->value]) ? $statuses[$this->value] : ProposalStatus::find()->andWhere(['id' => $this->value])->createCommand()->queryOne();
        $backgroundColor = Html::hex2rgba($status['color_label'], 0.1);

        $this->label = $status['label'];

        $this->buttonOptions = ArrayHelper::merge($this->buttonOptions, [
            'style' => "background-color: {$backgroundColor};color:{$status['color_label']}",
        ]);

        Html::addCssClass($this->buttonOptions, ['widget' => 'badge badge-clean text-uppercase px-3 py-2']);

        parent::init();
    }

    /**
     * @return ProposalStatus[]
     *
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function getModels()
    {
        if (!isset($this->_models)) {
            $this->setModels($this->getQuery()->createCommand()->queryAll());
        }

        return $this->_models;
    }

    /**
     * @param ProposalStatus[] $models
     */
    public function setModels($models)
    {
        if (!is_array($models)) {
            $models = ArrayHelper::toArray($models);
        }

        $this->_models = ArrayHelper::index($models, 'id');
    }

    /**
     * @param ProposalStatusQuery $query
     */
    public function setQuery($query)
    {
        $this->_query = $query;
    }

    /**
     * @return ProposalStatusQuery
     *
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if (!isset($this->_query)) {
            $this->_query = ProposalStatus::find();
        }

        return $this->_query;
    }
}
