<?php namespace modules\crm\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\db\ActiveQuery;
use modules\crm\models\LeadAssignee;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\crm\models\LeadAssignee]].
 *
 * @see    LeadAssignee
 */
class LeadAssigneeQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return LeadAssignee[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return LeadAssignee|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
