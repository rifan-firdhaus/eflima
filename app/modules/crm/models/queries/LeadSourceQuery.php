<?php

namespace modules\crm\models\queries;

use modules\core\db\ActiveQuery;
use modules\core\models\traits\VisibilityQuery;
use modules\crm\models\LeadSource;

/**
 * This is the ActiveQuery class for [[\modules\crm\models\LeadSource]].
 *
 * @see \modules\crm\models\LeadSource
 */
class LeadSourceQuery extends ActiveQuery
{
    use VisibilityQuery;

    /**
     * @inheritdoc
     * @return LeadSource[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return LeadSource|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
