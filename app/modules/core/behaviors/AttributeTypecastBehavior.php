<?php namespace modules\core\behaviors;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\db\ActiveRecord;
use yii\base\Event;
use yii\base\InvalidArgumentException;
use yii\behaviors\AttributeTypecastBehavior as BaseAttributeTypecastBehavior;
use yii\helpers\StringHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class AttributeTypecastBehavior extends BaseAttributeTypecastBehavior
{
    public $typecastAfterFind = true;
    public $typecastAfterSave = true;
    public $typecastLoadDefaultValue = true;
    public $skipOnNull = true;
    public $skipOnEmpty = true;

    /**
     * @inheritDoc
     */
    public function events()
    {
        $events = parent::events();

        if ($this->typecastLoadDefaultValue) {
            if (!($this->owner instanceof ActiveRecord)) {
                throw new InvalidArgumentException('Event onLoadDefaultValue only available in ' . ActiveRecord::class . ' class and subclasses');
            }

            $events[ActiveRecord::EVENT_ON_LOAD_DEFAULT_VALUE] = 'onLoadDefaultValue';
        }

        return $events;
    }

    /**
     * Handles owner 'onLoadDefaultValue' event, ensuring attribute typecasting.
     *
     * @param Event $event event instance.
     */
    public function onLoadDefaultValue($event)
    {
        $this->typecastAttributes();

        $this->normalizeOldAttributes();
    }

    /**
     * Set old attributes to normalized value
     */
    protected function normalizeOldAttributes()
    {
        if (!$this->owner->isNewRecord) {
            foreach ($this->attributeTypes as $attribute => $type) {
                $this->owner->setOldAttribute($attribute, $this->owner->{$attribute});
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function typecastValue($value, $type)
    {
        if (is_scalar($type)) {
            if (is_object($value) && method_exists($value, '__toString')) {
                $value = $value->__toString();
            }

            switch ($type) {
                case self::TYPE_INTEGER:
                    if($value === ''){
                        return null;
                    }

                    return (int) $value;
                case self::TYPE_FLOAT:
                    $detail = explode('.', $value);

                    if (isset($detail[1])) {
                        $detail[1] = rtrim($detail[1], '0');
                    }

                    if (empty($detail[1])) {
                        unset($detail[1]);
                    }

                    return implode('.', $detail);
                case self::TYPE_BOOLEAN:
                    return (bool) $value;
                case self::TYPE_STRING:
                    if (is_float($value)) {
                        return StringHelper::floatToString($value);
                    }
                    return (string) $value;
                default:
                    throw new InvalidArgumentException("Unsupported type '{$type}'");
            }
        }

        return call_user_func($type, $value);
    }

    /**
     * @inheritDoc
     */
    public function afterFind($event)
    {
        parent::afterFind($event);

        $this->normalizeOldAttributes();
    }
}