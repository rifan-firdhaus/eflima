<?php namespace modules\account\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\models\Staff;
use modules\core\db\ActiveQuery;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class StaffQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return Staff[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return Staff|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}