<?php

namespace modules\crm\models\queries;

use modules\core\db\ActiveQuery;
use modules\core\models\traits\VisibilityQuery;
use modules\crm\models\LeadStatus;

/**
 * This is the ActiveQuery class for [[\modules\crm\models\LeadStatus]].
 *
 * @see \modules\crm\models\LeadStatus
 */
class LeadStatusQuery extends ActiveQuery
{
    use VisibilityQuery;

    /**
     * @inheritdoc
     *
     * @return LeadStatus[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return LeadStatus|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
