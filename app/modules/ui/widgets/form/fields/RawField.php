<?php namespace modules\ui\widgets\form\fields;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use yii\helpers\Html;
use modules\ui\widgets\form\fields\traits\ErrorTrait;
use modules\ui\widgets\form\fields\traits\HorizontalLayoutTrait;
use modules\ui\widgets\form\fields\traits\RequiredTrait;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class RawField extends Field
{
    use HorizontalLayoutTrait;
    use ErrorTrait;
    use RequiredTrait;

    const LAYOUT_HORIZONTAL = 'horizontal';
    const LAYOUT_VERTICAL = 'vertical';

    public $input = '';
    public $encode = false;

    /**
     * @inheritdoc
     */
    public function input()
    {
        return $this->encode ? Html::encode($this->input) : $this->input;
    }

    /**
     * @inheritdoc
     */
    protected function normalize()
    {
        $this->normalizeHorizontalLayout();
        $this->normalizeRequired();
        $this->normalizeError();

        parent::normalize();
    }
}