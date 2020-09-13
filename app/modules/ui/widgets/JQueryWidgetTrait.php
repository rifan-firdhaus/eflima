<?php namespace modules\ui\widgets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use yii\helpers\Json;
use yii\web\JqueryAsset;
use yii\web\View;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
trait JQueryWidgetTrait
{
    /**
     * @var array the event handlers for the underlying Bootstrap JS plugin.
     * Please refer to the corresponding Bootstrap plugin Web page for possible events.
     * For example, [this page](http://getbootstrap.com/javascript/#modals) shows
     * how to use the "Modal" plugin and the supported events (e.g. "shown").
     */
    public $jsEvents = [];


    /**
     * Initializes the widget.
     * This method will register the bootstrap asset bundle. If you override this method,
     * make sure you call the parent implementation first.
     */
    public function init()
    {
        parent::init();

        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
    }

    /**
     * Registers a specific Bootstrap plugin and the related events
     *
     * @param string $name the name of the Bootstrap plugin
     */
    protected function registerPlugin($name)
    {
        $view = $this->getView();

        JqueryAsset::register($view);

        $id = $this->options['id'];

        if ($this->jsOptions !== false) {
            $options = empty($this->jsOptions) ? '' : Json::htmlEncode($this->jsOptions);
            $js = "jQuery('#$id').$name($options);";
            $view->registerJs($js);
        }

        $this->registerJsEvents();
    }

    /**
     * @return View the view object that can be used to render views or view files.
     * @see \yii\base\Widget::getView()
     */
    abstract function getView();

    /**
     * Registers JS event handlers that are listed in [[clientEvents]].
     */
    protected function registerJsEvents()
    {
        if (!empty($this->jsEvents)) {
            $id = $this->options['id'];
            $js = [];
            foreach ($this->jsEvents as $event => $handler) {
                $js[] = "jQuery('#$id').on('$event', $handler);";
            }
            $this->getView()->registerJs(implode("\n", $js));
        }
    }

}