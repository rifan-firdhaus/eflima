<?php namespace modules\finance\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\finance\models\InvoicePayment;
use modules\note\components\NoteRelation;
use modules\note\models\Note;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ProposalNoteRelation extends NoteRelation
{
    use ProposalRelatedTrait;

    /**
     * @inheritDoc
     */
    public function isActive($modelId = null)
    {
        return Yii::$app->user->can('admin.proposal.view.detail');
    }
}
