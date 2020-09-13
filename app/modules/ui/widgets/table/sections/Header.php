<?php namespace modules\ui\widgets\table\sections;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\ui\widgets\table\rows\Row;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property null|Row $mainRow
 */
class Header extends Section
{
    /** @var Row */
    protected $_mainRow;

    public $tagName = 'thead';

    /**
     * @param Row|array $row
     *
     * @return Row
     * @throws InvalidConfigException
     */
    public function setMainRow($row)
    {
        if (!($row instanceof Row)) {
            $row = $this->addRow($row);
        }

        $this->_mainRow = $row;

        return $this->_mainRow;
    }

    /**
     * @return Row|null
     *
     * @throws InvalidConfigException
     */
    public function getMainRow()
    {
        if ($this->_mainRow === null) {
            $rows = array_values($this->rows);

            if (count($rows) > 0) {
                $this->setMainRow($rows[0]);
            }
        }

        return $this->_mainRow;
    }

    /**
     * @inheritdoc
     */
    public function addRow($row = [], $id = null)
    {
        $isMain = ArrayHelper::remove($row, 'main', false);

        if (!isset($row['cell']['tagName'])) {
            $row['cell']['tagName'] = 'th';
        }

        $row = parent::addRow($row, $id);

        if ($isMain) {
            $this->setMainRow($row);
        }

        return $row;
    }
}