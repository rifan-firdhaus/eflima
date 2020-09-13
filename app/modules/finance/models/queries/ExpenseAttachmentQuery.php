<?php namespace modules\finance\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\db\ActiveQuery;
use modules\finance\models\ExpenseAttachment;

/**
 * @author Rifan Firdhaus Widigdo
 * <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\finance\models\ExpenseAttachment]].
 *
 * @see    ExpenseAttachment
 */
class ExpenseAttachmentQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return ExpenseAttachment[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return ExpenseAttachment|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
