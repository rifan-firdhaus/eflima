<?php namespace modules\content\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\content\models\PostTaxonomy;
use modules\core\db\ActiveQuery;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\content\models\PostTaxonomy]].
 *
 * @see    PostTaxonomy
 */
class PostTaxonomyQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return PostTaxonomy[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return PostTaxonomy|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
