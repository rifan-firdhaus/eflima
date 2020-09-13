<?php namespace modules\finance\models\queries;

use modules\core\db\ActiveQuery;
use modules\core\models\traits\VisibilityQuery;
use modules\finance\models\Tax;

/**
 * This is the ActiveQuery class for [[modules\finance\models\Tax]].
 *
 * @see Tax
 */
class TaxQuery extends ActiveQuery
{
    use VisibilityQuery;

    /**
     * @inheritdoc
     *
     * @return Tax[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return Tax|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
