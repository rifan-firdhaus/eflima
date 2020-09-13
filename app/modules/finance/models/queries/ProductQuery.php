<?php namespace modules\finance\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\db\ActiveQuery;
use modules\core\models\traits\VisibilityQuery;
use modules\finance\models\Product;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\finance\models\Product]].
 *
 * @see    Product
 */
class ProductQuery extends ActiveQuery
{
    use VisibilityQuery;

    /**
     * @inheritdoc
     *
     * @return Product[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return Product|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
