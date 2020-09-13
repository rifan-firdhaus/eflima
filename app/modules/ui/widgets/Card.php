<?php namespace modules\ui\widgets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use yii\base\Widget;
use yii\helpers\Html;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Card extends Widget
{
    public $title = '';
    public $content = '';
    public $footer = '';

    public $options = [];
    public $headerOptions = [
        'class' => 'card-header',
    ];
    public $titleOptions = [
        'class' => 'card-header-title',
    ];
    public $bodyOptions = [
        'class' => 'card-body',
    ];
    public $footerOptions = [
        'class' => 'card-footer',
    ];
    public $iconOptions = [
        'class' => 'icon card-header-icon',
    ];
    public $icon;

    public $encodeTitle = true;
    public $autoRender = true;

    public $headerItems = [];
    protected $footerItems = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
            $this->options['data-rid'] = $this->getRealId();
        }

        Html::addCssClass($this->options, ['widget' => 'card']);

        parent::init();

        ob_start();
        ob_implicit_flush(false);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $content = ob_get_clean();

        if (!empty($content) && empty($this->content)) {
            $this->content = $content;
        }

        if (!$this->autoRender) {
            return '';
        }

        return $this->beginTag() .
            $this->renderHeader() .
            $this->renderBody($this->content) .
            $this->renderFooter() .
            $this->endTag();
    }

    /**
     * @inheritdoc
     */
    public function beginTag()
    {
        return Html::beginTag('div', $this->options);
    }

    /**
     * @return string
     */
    public function renderHeader()
    {
        $title = $this->renderTitle();

        if ($title) {
            $this->addToHeader($title, true);
        }

        if (empty($this->headerItems)) {
            return '';
        }

        return Html::tag('div', implode('', $this->headerItems), $this->headerOptions);
    }

    /**
     * @return string
     */
    public function renderTitle()
    {
        $title = $this->title;

        if ($this->encodeTitle) {
            $title = Html::encode($title);
        }

        $title = $this->renderIcon() . $title;

        if (empty($title)) {
            return '';
        }

        return Html::tag('div', $title, $this->titleOptions);
    }

    /**
     * @return string
     */
    public function renderIcon()
    {
        if (!$this->icon) {
            return '';
        }

        return Icon::show($this->icon, $this->iconOptions);
    }

    /**
     * @param string $content
     * @param bool   $prepend
     *
     * @return $this
     */
    public function addToHeader($content, $prepend = false)
    {
        if ($prepend) {
            array_unshift($this->headerItems, $content);
        } else {
            $this->headerItems[] = $content;
        }

        return $this;
    }

    /**
     * @param $content
     *
     * @return string
     */
    public function renderBody($content)
    {
        if ($this->bodyOptions === false) {
            return $content;
        }

        return Html::tag('div', $content, $this->bodyOptions);
    }

    /**
     * @return string
     */
    public function renderFooter()
    {
        $this->addToFooter($this->footer);

        $this->footer = implode('', $this->footerItems);

        if (!$this->footer) {
            return '';
        }

        return Html::tag('div', $this->footer, $this->footerOptions);
    }

    /**
     * @param string $content
     * @param bool   $prepend
     *
     * @return $this
     */
    public function addToFooter($content, $prepend = false)
    {
        if ($prepend) {
            array_unshift($this->footerItems, $content);
        } else {
            $this->footerItems[] = $content;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function endTag()
    {
        return Html::endTag('div');
    }

    /**
     * @return void
     */
    public function beginHeader()
    {
        ob_start();
        ob_implicit_flush(false);
    }

    /**
     * @return void
     */
    public function endHeader()
    {
        $content = ob_get_clean();

        if ($content) {
            $this->addToHeader($content);
        }
    }

    /**
     * @return void
     */
    public function beginFooter()
    {
        ob_start();
        ob_implicit_flush(false);
    }

    /**
     * @return void
     */
    public function endFooter()
    {
        $content = ob_get_clean();

        $this->addToHeader($content);
    }
}
