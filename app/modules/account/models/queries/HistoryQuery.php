<?php namespace modules\account\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\models\History;
use modules\core\db\ActiveQuery;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\account\models\History]].
 *
 * @see    History
 */
class HistoryQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return History[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return History|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
