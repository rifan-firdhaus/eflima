<?php namespace modules\project\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\calendar\components\EventRelation;
use modules\project\widgets\inputs\ProjectInput;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ProjectEventRelation extends EventRelation
{
    use ProjectRelatedTrait;

    public $useDefaultPicker = false;

    /**
     * @inheritDoc
     */
    public function pickerInput($event, $attribute)
    {
        return ProjectInput::widget([
            'model' => $event,
            'attribute' => $attribute,
        ]);
    }
}