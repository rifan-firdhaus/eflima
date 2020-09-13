<?php namespace modules\core\base;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use yii\widgets\InputWidget as BaseInputWidget;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class InputWidget extends BaseInputWidget
{
    protected $_id;
    protected $_realId;

    /**
     * Returns the ID of the widget.
     *
     * @param bool $autoGenerate whether to generate an ID if it is not set previously
     *
     * @return string ID of the widget.
     */
    public function getId($autoGenerate = true)
    {
        if ($autoGenerate && $this->_id === null) {
            $this->setId(static::$autoIdPrefix . self::$counter++);
        }

        return $this->_id . '-' . $this->view->uniqueId;
    }

    public function getRealId()
    {
        $this->getId();

        return $this->_realId;
    }

    /**
     * Sets the ID of the widget.
     *
     * @param string $value id of the widget.
     */
    public function setId($value)
    {
        $this->_id = $value;
        $this->_realId = $value;
    }
}