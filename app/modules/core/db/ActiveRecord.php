<?php namespace modules\core\db;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord as BaseActiveRecord;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @method ActiveQuery hasMany($class, array $link) see [[BaseActiveRecord::hasMany()]] for more info
 * @method ActiveQuery hasOne($class, array $link) see [[BaseActiveRecord::hasOne()]] for more info
 */
class ActiveRecord extends BaseActiveRecord
{
    const EVENT_ON_LOAD_DEFAULT_VALUE = 'onLoadDefaultValue';
    const EVENT_CREATE_VALIDATORS = 'eventCreateValidators';

    /**
     * @inheritdoc
     *
     * @return ActiveQuery
     * @throws InvalidConfigException
     */
    public static function find()
    {
        return Yii::createObject(ActiveQuery::class, [get_called_class()]);
    }

    /**
     * @inheritDoc
     */
    public function afterFind()
    {
        parent::afterFind();

        $this->normalizeAttributes();
    }

    public function normalizeAttributes($save = false)
    {

    }

    /**
     * @inheritDoc
     */
    public function afterRefresh()
    {
        parent::afterRefresh();

        $this->normalizeAttributes();
    }

    /**
     * @inheritDoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $this->normalizeAttributes();
    }

    /**
     * @inheritDoc
     */
    public function beforeSave($insert)
    {
        $this->normalizeAttributes(true);

        return parent::beforeSave($insert);
    }

    /**
     * @inheritDoc
     */
    public function loadDefaultValues($skipIfSet = true)
    {
        parent::loadDefaultValues($skipIfSet);

        $this->trigger(self::EVENT_ON_LOAD_DEFAULT_VALUE);

        return $this;
    }

    public function createValidators()
    {
        $validators = parent::createValidators();

        $this->trigger(self::EVENT_CREATE_VALIDATORS,new ModelValidatorsEvent([
            'validators' => $validators
        ]));

        return $validators;
    }
}