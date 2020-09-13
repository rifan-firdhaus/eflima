<?php namespace modules\project\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\db\ActiveQuery;
use modules\project\models\ProjectDiscussionTopic;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\project\models\ProjectDiscussionTopic]].
 *
 * @see    ProjectDiscussionTopic
 */
class ProjectDiscussionTopicQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return ProjectDiscussionTopic[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return ProjectDiscussionTopic|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
