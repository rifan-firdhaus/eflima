<?php namespace modules\core\web;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\core\widgets\Block;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\web\View as BaseView;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class View extends BaseView
{

    public $bodyClass = [];
    protected $_temp = [];
    protected $_blockQueues = [];
    protected $_current = [];

    /**
     * @param string $id
     * @param array  $params
     * @param string $default
     *
     * @return string
     */
    public function block($id, $params = [], $default = '')
    {
        if (substr($id, 0, 1) === '@') {
            $id = $this->_current['blockId'] . ':' . substr($id, 1);
        }

        ob_start();
        ob_implicit_flush(false);

        $event = new ViewBlockEvent([
            'params' => $params,
            'viewParams' => $this->_current['params'],
        ]);

        $this->trigger("block:{$id}", $event);

        $result = trim(ob_get_clean());

        if ($result) {
            if (!isset($this->blocks[$id])) {
                $this->blocks[$id] = $result;
            } else {
                $this->blocks[$id] .= $result;
            }
        }

        $result = isset($this->blocks[$id]) ? $this->blocks[$id] : $default;

        unset($this->blocks[$id]);

        if (YII_ENV_DEV) {
            return "<!-- BLOCK {$id} -->" . $result;
        }

        return $result;
    }

    /**
     * @param string           $id
     * @param Closure|callable $callback
     */
    public function enqueueBlock($id, $callback)
    {
        if (!is_callable($callback)) {
            return;
        }

        $this->_blockQueues[$id][] = $callback;
    }

    /**
     * @param string $id
     * @param bool   $renderInPlace
     *
     * @return Block
     */
    public function beginBlock($id, $renderInPlace = false)
    {
        return Block::begin([
            'id' => $id,
            'renderInPlace' => $renderInPlace,
            'view' => $this,
        ]);
    }

    public function addBlock($id, $content)
    {
        if (!isset($this->blocks[$id])) {
            $this->blocks[$id] = $content;
        } else {
            $this->blocks[$id] .= $content;
        }
    }

    /**
     * End block
     */
    public function endBlock()
    {
        Block::end();
    }

    /**
     * @inheritdoc
     */
    public function getRequestedViewFile()
    {
        return parent::getRequestedViewFile();
    }

    /**
     * @inheritdoc
     */
    public function renderFile($viewFile, $params = [], $context = null)
    {
        $viewFile = Yii::getAlias($viewFile);
        $file = FileHelper::normalizePath($viewFile);
        $appDir = FileHelper::normalizePath(Yii::getAlias('@modules'));
        $blockId = trim(substr($file, strlen($appDir)), DIRECTORY_SEPARATOR);
        $blockId = FileHelper::normalizePath(substr($blockId, 0, strrpos($blockId, '.')), '/');
        $blockId = implode('', explode('views/', $blockId, 2));

        $this->_temp[] = $this->_current = compact('file', 'blockId', 'params');

        $result = parent::renderFile($viewFile, $params, $context);

        array_pop($this->_temp);

        if ($this->_temp) {
            $this->_current = $this->_temp[count($this->_temp) - 1];
        }

        return $result;
    }

    /**
     * @param string $class
     *
     * @return $this
     */
    public function addBodyClass($class)
    {
        if (is_array($class)) {
            $this->bodyClass = ArrayHelper::merge($this->bodyClass, $class);
        } else {
            $this->bodyClass[] = $class;
        }

        return $this;
    }

    /**
     * @param string $content
     *
     * @return false|string
     */
    public function renderAjaxContent($content)
    {
        ob_start();
        ob_implicit_flush(false);

        $this->beginPage();
        $this->head();
        $this->beginBody();
        echo $content;
        $this->endBody();
        $this->endPage(true);

        return ob_get_clean();
    }
}