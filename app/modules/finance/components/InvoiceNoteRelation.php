<?php namespace modules\finance\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\note\components\NoteRelation;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class InvoiceNoteRelation extends NoteRelation
{
    use InvoiceRelatedTrait;

    /**
     * @inheritDoc
     */
    public function isActive($modelId = null)
    {
        return Yii::$app->user->can('admin.invoice.view.detail');
    }

}
