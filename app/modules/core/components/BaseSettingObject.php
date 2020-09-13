<?php namespace modules\core\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use yii\base\Component;
use yii\base\InvalidArgumentException;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property SettingRenderer $renderer
 */
class BaseSettingObject extends Component
{
    public $_renderer;

    /**
     * @return void
     */
    public function render()
    {

    }

    /**
     * @return SettingRenderer
     */
    public function getRenderer()
    {
        return $this->_renderer;
    }

    /**
     * @param $renderer
     */
    public function setRenderer($renderer)
    {
        if (!$renderer instanceof SettingRenderer) {
            throw new InvalidArgumentException('Renderer must be instance of' . SettingRenderer::class);
        }

        $this->_renderer = $renderer;
    }
}