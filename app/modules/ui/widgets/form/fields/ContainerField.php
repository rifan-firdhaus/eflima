<?php namespace modules\ui\widgets\form\fields;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use http\Exception\InvalidArgumentException;
use yii\helpers\Html;
use modules\ui\widgets\form\fields\traits\ErrorTrait;
use modules\ui\widgets\form\fields\traits\HorizontalLayoutTrait;
use modules\ui\widgets\form\fields\traits\RequiredTrait;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ContainerField extends Field
{
    use HorizontalLayoutTrait;
    use RequiredTrait;
    use ErrorTrait;

    const LAYOUT_HORIZONTAL = 'horizontal';
    const LAYOUT_VERTICAL = 'vertical';

    public $options = [];
    public $inputOptions = [
        'class' => 'form-row',
    ];

    public $fields = [];

    /**
     * @inheritdoc
     */
    public function input()
    {
        $input = '';

        foreach ($this->fields AS $field) {
            $options = ArrayHelper::getValue($field, 'options', []);
            $sizeClass = ArrayHelper::getValue($field, 'size', 'col');

            Html::addCssClass($options, $sizeClass);

            $field = $this->form->field($field['field']);

            $field->form = $this->form;

            $input .= Html::tag('div', $field->render(), $options);
        }

        $tag = ArrayHelper::remove($this->inputOptions, 'tag', 'div');

        return Html::tag($tag, $input, $this->inputOptions);
    }

    /**
     * @param array $field
     */
    public function addField($field)
    {
        if (!isset($field['field'])) {
            throw new InvalidArgumentException("field is required");
        }

        $this->fields[] = $field;
    }

    /**
     * @param array $fields
     */
    public function addFields($fields)
    {
        array_walk($fields, [$this, 'addField']);
    }

    /**
     * @inheritdoc
     */
    public function normalize()
    {
        $this->normalizeHorizontalLayout();
        $this->normalizeRequired();
        $this->normalizeError();

        parent::normalize();
    }
}