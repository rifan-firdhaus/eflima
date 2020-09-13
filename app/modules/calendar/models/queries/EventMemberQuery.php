<?php namespace modules\calendar\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\calendar\models\EventMember;
use modules\core\db\ActiveQuery;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\calendar\models\EventMember]].
 *
 * @see    EventMember
 */
class EventMemberQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return EventMember[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return EventMember|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
