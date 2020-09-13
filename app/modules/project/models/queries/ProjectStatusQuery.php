<?php namespace modules\project\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\db\ActiveQuery;
use modules\core\models\traits\VisibilityQuery;
use modules\project\models\ProjectStatus;

/**
 * @author Rifan Firdhaus Widigdo
 * <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\project\models\ProjectStatus]].
 *
 * @see    ProjectStatus
 */
class ProjectStatusQuery extends ActiveQuery
{
    use VisibilityQuery;
    /**
     * @inheritdoc
     *
     * @return ProjectStatus[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return ProjectStatus|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
