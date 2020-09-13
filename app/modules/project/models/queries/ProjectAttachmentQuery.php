<?php namespace modules\project\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\db\ActiveQuery;
use modules\project\models\ProjectAttachment;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\project\models\ProjectAttachment]].
 *
 * @see    ProjectAttachment
 */
class ProjectAttachmentQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return ProjectAttachment[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return ProjectAttachment|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
