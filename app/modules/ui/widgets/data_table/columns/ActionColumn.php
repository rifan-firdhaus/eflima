<?php namespace modules\ui\widgets\data_table\columns;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use yii\helpers\Html;
use modules\ui\widgets\table\cells\Cell;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Url;
use function array_filter;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ActionColumn extends Column
{
    public $buttons = [];
    public $urlCreator;
    public $format = 'raw';
    public $controller;

    public $contentCell = [
        'class' => Cell::class,
        'vAlign' => Cell::V_ALIGN_CENTER,
        'hAlign' => Cell::H_ALIGN_RIGHT,
        'options' => [
            'class' => 'action-column',
        ],
    ];

    public function init()
    {
        parent::init();

        $this->defaultButtons();
    }

    /**
     * Register default buttons
     */
    public function defaultButtons()
    {
        if (!isset($this->buttons['update'])) {
            $this->buttons['update'] = [
                'value' => [
                    'icon' => 'i8:edit',
                    'label' => Yii::t('app', 'Update'),
                    'data-lazy-container' => '#main#',
                ],
            ];
        }

        if (!isset($this->buttons['view'])) {
            $this->buttons['view'] = [
                'value' => [
                    'icon' => 'i8:eye',
                    'label' => Yii::t('app', 'View'),
                    'data-lazy-container' => '#main#',
                ],
            ];
        }

        if (!isset($this->buttons['delete'])) {
            $this->buttons['delete'] = [
                'value' => [
                    'icon' => 'i8:trash',
                    'label' => Yii::t('app', 'Delete'),
                    'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure?', [
                        'object_name' => Yii::t('app', 'this item'),
                    ]),
                    'class' => 'text-danger',
                    'data-lazy-container' => '#main#',
                    'data-lazy-options' => ['scroll' => false],
                ],
            ];
        }
    }

    /**
     * @inheritdoc
     */
    protected function renderContent($model, $id, $index)
    {
        $buttons = [];

        foreach (array_filter($this->buttons) AS $key => $button) {
            if (!is_array($button)) {
                $button = ['value' => $button];
            }

            if (isset($button['visible'])) {
                if (is_callable($button['visible'])) {
                    $button['visible'] = call_user_func($button['visible'], $model, $id, $index);
                }

                if (!$button['visible']) {
                    continue;
                }
            }

            if ($button['value'] instanceof Closure) {
                $url = $this->createUrl($key, $model, $id, $index);

                $button['value'] = call_user_func($button['value'], $url, $model, $id, $index);
            }

            if (is_array($button['value'])) {
                if(!isset($button['value']['data-toggle'])){
                    $button['value']['data-toggle'] = 'tooltip';
                }

                $label = isset($button['value']['label']) ? ArrayHelper::remove($button['value'], 'label') : Inflector::humanize($key);

                if (!isset($button['value']['url'])) {
                    $button['value']['url'] = $this->createUrl($key, $model, $id, $index);
                }

                $button['value']['title'] = $label;

                $buttons[] = Html::a($button['value']);
            } else {
                $buttons[] = $button['value'];
            }
        }

        return implode("", $buttons);
    }

    /**
     * @param $action
     * @param $model
     * @param $id
     * @param $index
     *
     * @return mixed
     */
    public function createUrl($action, $model, $id, $index)
    {
        if (is_callable($this->urlCreator)) {
            return call_user_func($this->urlCreator, $action, $model, $id, $index, $this);
        }

        $params = is_array($id) ? $id : [$this->dataTable->idAttribute => (string) $id];

        $params[0] = $this->controller ? $this->controller . '/' . $action : $action;

        return Url::toRoute($params);
    }
}
