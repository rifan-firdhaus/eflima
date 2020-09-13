<?php namespace modules\note\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\db\ActiveQuery;
use modules\note\models\NoteAttachment;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\note\models\NoteAttachment]].
 *
 * @see    NoteAttachment
 */
class NoteAttachmentQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return NoteAttachment[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return NoteAttachment|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
