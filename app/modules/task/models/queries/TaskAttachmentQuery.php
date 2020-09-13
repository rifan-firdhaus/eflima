<?php namespace modules\task\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\db\ActiveQuery;
use modules\task\models\TaskAttachment;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\task\models\TaskAttachment]].
 *
 * @see    TaskAttachment
 */
class TaskAttachmentQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return TaskAttachment[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return TaskAttachment|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
