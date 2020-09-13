<?php namespace modules\address\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\address\models\Country;
use modules\core\db\ActiveQuery;
use modules\core\models\traits\VisibilityQuery;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[Country]].
 *
 * @see    Country
 */
class CountryQuery extends ActiveQuery
{
    use VisibilityQuery;

    /**
     * @inheritdoc
     *
     * @return Country[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return Country|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
