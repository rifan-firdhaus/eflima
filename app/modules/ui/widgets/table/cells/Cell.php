<?php namespace modules\ui\widgets\table\cells;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use yii\helpers\Html;
use modules\ui\widgets\table\rows\Row;
use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Cell extends Component
{
    /** @var Row */
    public $row;
    public $content;
    public $name;
    public $format = 'text';
    public $options = [];

    const H_ALIGN_LEFT = 'left';
    const H_ALIGN_CENTER = 'center';
    const H_ALIGN_RIGHT = 'right';

    const V_ALIGN_TOP = 'top';
    const V_ALIGN_CENTER = 'middle';
    const V_ALIGN_BOTTOM = 'bottom';

    public $hAlign;
    public $vAlign;
    public $width;

    public $tagName = 'td';
    public $colspan = 1;

    /**
     * @return string
     */
    public function render()
    {
        $this->normalize();

        return Html::tag($this->tagName, $this->renderContent(), $this->options);
    }

    /**
     * @return string
     */
    public function renderContent()
    {
        if ($this->content instanceof Closure) {
            return call_user_func($this->content, $this);
        }

        return $this->formatContent($this->content);
    }

    /**
     * @param mixed $content
     *
     * @return mixed
     */
    public function formatContent($content)
    {
        if ($this->format === false) {
            return $content;
        }

        $args = [$content];

        if (is_array($this->format)) {
            $_args = $this->format;
            $function = array_shift($_args);
            $args = ArrayHelper::merge($args, $_args);
        } else {
            $function = $this->format;
        }

        $function = 'as' . ucfirst($function);

        return call_user_func_array([Yii::$app->formatter, $function], $args);
    }

    /**
     * @return void
     */
    public function normalize()
    {
        $this->options['data-column'] = $this->name;

        if ($this->hAlign) {
            Html::addCssClass($this->options, 'text-' . $this->hAlign);
        }

        if ($this->vAlign) {
            Html::addCssClass($this->options, 'align-' . $this->vAlign);
        }

        if ($this->colspan > 1) {
            $this->options['colspan'] = $this->colspan;
        }

        if ($this->width) {
            $this->options['width'] = $this->width;
        }
    }
}