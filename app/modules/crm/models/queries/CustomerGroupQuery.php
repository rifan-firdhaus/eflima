<?php

namespace modules\crm\models\queries;

use modules\core\db\ActiveQuery;
use modules\core\models\traits\VisibilityQuery;
use modules\crm\models\CustomerGroup;

/**
 * This is the ActiveQuery class for [[\modules\crm\models\CustomerGroup]].
 *
 * @see \modules\crm\models\CustomerGroup
 */
class CustomerGroupQuery extends ActiveQuery
{
    use VisibilityQuery;

    /**
     * @inheritdoc
     * @return CustomerGroup[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return CustomerGroup|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
