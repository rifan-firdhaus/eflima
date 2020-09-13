<?php namespace modules\support\behaviors;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\db\ActiveRecord;
use modules\finance\models\ExpenseCategory;
use modules\support\models\KnowledgeBaseCategory;
use Yii;
use yii\base\Behavior;
use yii\base\ModelEvent;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property  ActiveRecord $owner
 */
class KnowledgeBaseCategoryCreationBehavior extends Behavior
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

        $model = new KnowledgeBaseCategory([
            'name' => $this->owner->{$this->aliasAttribute},
        ]);

        if (!$model->save()) {
            $this->owner->addError($this->attribute, Yii::t('app', 'Failed to save {object}',[
                'object' => Yii::t('app','Category')
            ]));

            $event->isValid = false;
        }

        $this->owner->{$this->attribute} = $model->id;
        $this->owner->{$this->aliasAttribute} = null;
    }
}