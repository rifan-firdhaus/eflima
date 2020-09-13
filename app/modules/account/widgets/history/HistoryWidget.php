<?php namespace modules\account\widgets\history;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\History;
use modules\account\widgets\history\assets\HistoryAsset;
use modules\ui\widgets\lazy\Lazy;
use yii\base\Widget;
use yii\data\BaseDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\LinkPager;
use function is_array;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class HistoryWidget extends Widget
{
    const EVEMT_RENDER_ITEM = 'eventRenderItem';
    /** @var array|BaseDataProvider */
    public $dataProvider;
    /** @var array|Lazy */
    public $lazy = [
        'class' => Lazy::class,
    ];
    public $linkPager = [
        'class' => LinkPager::class,
        'options' => [
            'class' => 'mt-3 mb-0 pagination justify-content-center',
        ],
    ];
    public $options = [];

    public function init()
    {
        parent::init();

        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->id;
        }

        if (!isset($this->lazy['id'])) {
            $this->lazy['id'] = $this->getRealId() . '-lazy';
        }

        $this->linkPager['pagination'] = $this->dataProvider->pagination;

        $this->registerAssets();
    }

    public function registerAssets()
    {
        HistoryAsset::register($this->view);
    }

    public function run()
    {
        $result = '';

        foreach ($this->getModels() AS $group) {
            $groupResult = '';

            foreach ($group['models'] AS $model) {
                /** @var History $model */

                if (!is_array($model->params)) {
                    $model->params = Json::decode($model->params);
                }

                $event = new HistoryWidgetEvent([
                    'model' => $model,
                ]);

                $this->trigger(self::EVEMT_RENDER_ITEM, $event);

                if ($event->result) {
                    $itemResult = $event->result;
                } else {
                    $itemResult = $this->view->render('@modules/account/widgets/history/history-item', [
                        'model' => $model,
                        'widget' => $this,
                        'icon' => $event->icon,
                        'iconOptions' => $event->iconOptions,
                        'params' => !empty($event->params) ? $event->params : $model->params,
                        'description' => isset($event->description) ? $event->description : $model->description,
                    ]);
                }

                $groupResult .= Html::tag('section', $itemResult, $event->options);
            }

            $date = Html::tag('span', date('d', $group['timestamp']), ['class' => 'history-timeline-group-date']);
            $month = Html::tag('span', date('M Y', $group['timestamp']), ['class' => 'history-timeline-group-month']);
            $year = Html::tag('h3', $date . $month);
            $result .= Html::tag('section', $year . $groupResult, ['class' => 'year']);
        }

        Html::addCssClass($this->options, ['widget' => 'history-timeline']);

        $result = Html::tag('div', Html::tag('div', $result, ['class' => 'history-timeline-wrapper']), $this->options);

        if ($this->linkPager !== false) {
            $result = $result . LinkPager::widget($this->linkPager);
        }

        if ($this->lazy !== false) {
            $class = ArrayHelper::remove($this->lazy, 'class', Lazy::class);

            $class::begin($this->lazy);
            echo $result;
            $class::end();
        } else {
            echo $result;
        }

    }

    /**
     * @return array
     */
    public function getModels()
    {
        /** @var History[] $dataProviderModels */
        $dataProviderModels = $this->dataProvider->models;
        $models = [];

        foreach ($dataProviderModels AS $model) {
            $group = date("Y-m-d", $model->at);

            if (!isset($models[$group])) {
                $models[$group] = [
                    'timestamp' => strtotime(date('Y-m-d 00:00:00', $model->at)),
                    'models' => [],
                ];
            }

            $models[$group]['models'][] = $model;
        }

        return $models;
    }
}
