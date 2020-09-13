<?php namespace modules\ui\widgets\form\fields;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\ui\widgets\form\Form;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
abstract class Field extends Component
{
    const EVENT_RENDER = 'eventRender';
    const EVENT_INIT = 'eventInit';
    const EVENT_NORMALIZE = 'eventNormalize';

    public $label;
    public $hint;

    /** @var Form */
    public $form;

    public $options = [
        'class' => 'form-group',
    ];
    public $inputOptions;
    public $labelOptions = [];
    public $hintOptions = [
        'class' => 'form-text form-hint',
        'tag' => 'small',
    ];

    /** @var bool|array */
    public $inputWrapperOptions = false;

    public static $_lastSort = 0;

    public $sort;

    /** @var callable */
    public $renderer;

    public $visible = true;
    public $inputOnly = false;
    public $selectors = [];
    public $visibility = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->trigger(self::EVENT_INIT);

        if (!isset($this->sort)) {
            $this->sort = self::$_lastSort;

            self::$_lastSort++;
        }
    }

    /**
     * @return string
     *
     * @throws Exception
     */
    public function render()
    {
        if ($this->visible === false || (is_callable($this->visible) && !call_user_func($this->visible, $this))) {
            return;
        }

        $this->normalize();

        $event = new FieldEvent();

        $this->trigger(self::EVENT_RENDER, $event);

        if ($event->handled && !empty($event->renderedInput)) {
            return $event->renderedInput;
        }

        if ($this->renderer && is_callable($this->renderer)) {
            return call_user_func($this->renderer, $this);
        }

        return $this->build();
    }

    /**
     * @return string
     */
    public function build()
    {

        if ($this->visibility) {
            $this->form->fieldVisibility[$this->selectors['field']] = $this->buildVisibility($this->visibility);
        }

        $input = $this->input();

        if ($this->inputOnly) {
            return $input;
        }

        return $this->begin() . $this->label() . $this->wrapInput($input) . $this->end();
    }

    /**
     * @return string
     *
     */
    public function __toString()
    {
        try {
            return $this->render();
        } catch (Exception $e) {
            return $e;
        } catch (Throwable $e) {
            return $e;
        }
    }

    /**
     * @return string
     */
    public function label()
    {
        if ($this->label === false) {
            return '';
        }

        $inputId = isset($this->labelOptions['for']) ? $this->labelOptions['for'] : ArrayHelper::getValue($this->inputOptions, 'id');

        return Html::label($this->label, $inputId, $this->labelOptions);
    }

    /**
     * @return string
     */
    public function hint()
    {
        if ($this->hint === false) {
            return '';
        }


        return Html::tag(ArrayHelper::remove($this->hintOptions, 'tag', 'div'), $this->hint, $this->hintOptions);
    }

    /**
     * @return string
     */
    public function input()
    {

    }

    /**
     * @return string
     */
    protected function begin()
    {
        $tag = ArrayHelper::remove($this->options, 'tag', 'div');

        return Html::beginTag($tag, $this->options);
    }

    /**
     * @return string
     */
    protected function end()
    {
        $tag = ArrayHelper::remove($this->options, 'tag', 'div');

        return Html::endTag($tag);
    }

    /**
     * @param string $input
     *
     * @return string
     */
    protected function wrapInput($input)
    {
        if ($this->inputWrapperOptions === false) {
            return $input . $this->hint();
        }

        $tag = ArrayHelper::remove($this->inputWrapperOptions, 'tag', 'div');

        return Html::tag($tag, $input . $this->hint(), $this->inputWrapperOptions);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    protected function normalize()
    {
        if (!isset($this->inputOptions['id'])) {
            $this->inputOptions['id'] = Yii::$app->security->generateRandomString(16);
        }

        if (!isset($this->inputOptions['data-rid'])) {
            $this->inputOptions['data-rid'] = $this->inputOptions['id'];
        }

        if (!isset($this->options['id'])) {
            $this->options['id'] = Inflector::camel2id(basename(FileHelper::normalizePath(get_called_class(), '/'))) . '-' . $this->inputOptions['id'];
        }

        if (!isset($this->options['data-rid'])) {
            $this->options['data-rid'] = Inflector::camel2id(basename(FileHelper::normalizePath(get_called_class(), '/'))) . '-' . $this->inputOptions['data-rid'];
        }

        $this->selectors['field'] = Html::cssSelector($this->options);
        $this->selectors['input'] = Html::cssSelector($this->inputOptions);
        $this->selectors['label'] = Html::cssSelector($this->labelOptions);

        $this->trigger(self::EVENT_NORMALIZE);
    }

    //    public function buildVisibility($visibility)
    //    {
    //        $result = [];
    //
    //        $result[] = $visibility[0];
    //
    //        if (!is_array($visibility[1])) {
    //            $result[] = '#' . Html::getInputId($visibility[1], $visibility[2]);
    //            $result[] = $visibility[3];
    //        } else {
    //            array_shift($visibility);
    //
    //            foreach ($visibility AS $vis) {
    //                $result[] = $this->buildVisibility($vis);
    //            }
    //        }
    //
    //        return $result;
    //    }
}