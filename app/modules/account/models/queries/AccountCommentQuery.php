<?php namespace modules\account\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\AccountComment;
use modules\core\db\ActiveQuery;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\account\models\AccountComment]].
 *
 * @see    AccountComment
 */
class AccountCommentQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return AccountComment[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return AccountComment|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
