<?php

namespace modules\finance\models\queries;

use modules\core\db\ActiveQuery;
use modules\core\models\traits\VisibilityQuery;
use modules\finance\models\Currency;

/**
 * This is the ActiveQuery class for [[\modules\finance\models\Currency]].
 *
 * @see Currency
 */
class CurrencyQuery extends ActiveQuery
{
    use VisibilityQuery;

    /**
     * @inheritdoc
     *
     * @return Currency[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return Currency|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
