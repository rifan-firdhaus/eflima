<?php namespace modules\core\widgets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use yii\widgets\Block as BaseBlock;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Block extends BaseBlock
{
    public $append = true;
    public $params = [];
    public $renderInPlace = false;

    /** @inheritdoc */
    public function init()
    {
        $this->trigger(self::EVENT_INIT);

        ob_start();
        ob_implicit_flush(false);
    }

    /** @inheritdoc */
    public function run()
    {
        $block = ob_get_clean();

        if (!$this->append || !isset($this->view->blocks[$this->getId()])) {
            $this->view->blocks[$this->getId()] = $block;
        } elseif ($this->append) {
            $this->view->blocks[$this->getId()] .= $block;
        }

        if ($this->renderInPlace) {
            echo $this->view->blocks[$this->getId()];
        }
    }
}