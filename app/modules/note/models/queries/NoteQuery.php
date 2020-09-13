<?php namespace modules\note\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\db\ActiveQuery;
use modules\note\models\Note;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\note\models\Note]].
 *
 * @see    Note
 */
class NoteQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return Note[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return Note|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
