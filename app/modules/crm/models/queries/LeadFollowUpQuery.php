<?php namespace modules\crm\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\db\ActiveQuery;
use modules\core\models\traits\VisibilityQuery;
use modules\crm\models\LeadFollowUp;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\crm\models\LeadFollowUp]].
 *
 * @see    \modules\crm\models\LeadFollowUp
 */
class LeadFollowUpQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return LeadFollowUp[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return LeadFollowUp|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
