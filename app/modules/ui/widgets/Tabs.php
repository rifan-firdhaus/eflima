<?php namespace modules\ui\widgets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use yii\base\Widget;
use yii\helpers\Html;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Tabs extends Widget
{
    /**
     * @var array
     * - label
     * - options
     * - content
     * - id
     * - linkOptions
     */
    public $navigations = [];
    public $contents = [];
    public $contentOptions = [];
    public $contentWrapperOptions = [
        'class' => 'tab-content',
    ];
    public $navigation = [
        'class' => Menu::class,
    ];
    public $active;
    public $autoRender = true;
    public $activeCssClass = 'active';
    protected $currentId;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $this->navigation['options']['role'] = 'tablist';

        if (!$this->autoRender) {
            return '';
        }

        return $this->renderNavigation() . $this->renderContent();
    }

    public function renderNavigation()
    {
        $class = ArrayHelper::remove($this->navigation, 'class', Menu::class);

        $this->navigation['items'] = $this->navigations;
        $this->navigation['active'] = $this->active;
        $this->navigation['activeCssClass'] = $this->activeCssClass;

        if (!isset($this->navigation['options'])) {
            $this->navigation['options'] = [];
        }

        if (!isset($this->navigation['itemOptions'])) {
            $this->navigation['itemOptions'] = [];
        }

        if (!isset($this->navigation['linkOptions'])) {
            $this->navigation['linkOptions'] = [];
        }

        Html::addCssClass($this->navigation['options'], ['widget' => 'nav']);
        Html::addCssClass($this->navigation['itemOptions'], ['widget' => 'nav-item']);
        Html::addCssClass($this->navigation['linkOptions'], ['widget' => 'nav-link']);

        return $class::widget($this->navigation);
    }

    public function renderContent()
    {
        $result = "";

        Html::addCssClass($this->contentOptions, ['widget' => 'tab-pane']);
        Html::addCssClass($this->contentWrapperOptions, ['widget' => 'tab-content']);

        $this->contentOptions['role'] = 'tabanel';

        foreach ($this->contents AS $id => $contentOptions) {
            $content = ArrayHelper::remove($contentOptions, 'content', '');
            $contentOptions['id'] = $id;
            $contentOptions['aria-labelledby'] = $id;
            $contentOptions = ArrayHelper::merge($contentOptions, $this->contentOptions);

            if ($this->active === $id) {
                Html::addCssClass($contentOptions, $this->activeCssClass);
            }

            $result .= Html::tag('div', $content, $contentOptions);
        }

        return Html::tag('div', $result, $this->contentWrapperOptions);
    }

    public function beginItem($id, $navOptions, $content = [])
    {
        if ($this->currentId) {
            throw new InvalidConfigException("You need to close the item with \$tab->endItem() before starting begin new item");
        }

        $this->currentId = $id;

        $this->addItem($id, $navOptions, $content);

        ob_start();
        ob_implicit_flush(false);
    }

    public function addItem($id, $navOptions, $content = [])
    {
        if (is_string($navOptions)) {
            $navOptions = ['label' => $navOptions];
        }

        $navOptions['id'] = $navOptions;

        if (!isset($navOptions['url'])) {
            $this->asTabNavigation($id, $navOptions);
        }

        $this->navigations[$id] = $navOptions;

        if (is_string($content)) {
            $content = ['content' => $content];
        }

        if (!empty($content)) {
            $this->contents[$id] = $content;
        }
    }

    protected function asTabNavigation($id, &$navOptions)
    {
        $navOptions['url'] = "#{$id}";
        $navOptions['linkOptions']['id'] = "{$id}-tab";
        $navOptions['linkOptions']['role'] = 'tab';
        $navOptions['linkOptions']['aria-controls'] = $id;
        $navOptions['linkOptions']['data-toggle'] = 'tab';

        if ($this->active === null) {
            $this->active = $id;
        }
    }

    public function endItem()
    {
        $content = ob_get_clean();

        $this->contents[$this->currentId]['content'] = $content;

        $this->currentId = null;

        return $content;
    }
}