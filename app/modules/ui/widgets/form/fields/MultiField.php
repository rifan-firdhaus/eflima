<?php namespace modules\ui\widgets\form\fields;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class MultiField extends Field
{
    public $fields = [];

    /**
     * @inheritdoc
     */
    public function input()
    {
        return $this->renderFields();
    }

    /**
     * @return string
     * @throws Exception
     * @throws InvalidConfigException
     */
    protected function renderFields()
    {
        return $this->form->fields($this->fields);
    }

    /**
     * @param array $field
     */
    public function addField($field)
    {
        $this->fields[] = $field;
    }

    /**
     * @param array $fields
     */
    public function addFields($fields)
    {
        array_walk($fields, [$this, 'addField']);
    }
}