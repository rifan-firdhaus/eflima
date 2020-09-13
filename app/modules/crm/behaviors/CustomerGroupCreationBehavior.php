<?php namespace modules\crm\behaviors;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\db\ActiveRecord;
use modules\crm\models\CustomerGroup;
use Yii;
use yii\base\Behavior;
use yii\base\ModelEvent;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property  ActiveRecord $owner
 */
class CustomerGroupCreationBehavior extends Behavior
{
    public $attribute;
    public $aliasAttribute;

    /**
     * @inheritDoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
        ];
    }

    /**
     * @param ModelEvent $event
     */
    public function beforeSave($event)
    {
        if (empty($this->owner->{$this->aliasAttribute})) {
            return;
        }

        $expense = new CustomerGroup([
            'name' => $this->owner->{$this->aliasAttribute},
        ]);

        if (!$expense->save()) {
            $this->owner->addError($this->attribute, Yii::t('app', 'Failed tp save group'));

            $event->isValid = false;
        }

        $this->owner->{$this->attribute} = $expense->id;
        $this->owner->{$this->aliasAttribute} = null;
    }
}