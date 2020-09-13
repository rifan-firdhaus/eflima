<?php namespace modules\content\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\content\models\PostTaxonomyRelationship;
use modules\core\db\ActiveQuery;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\content\models\PostTaxonomyRelationship]].
 *
 * @see    PostTaxonomyRelationship
 */
class PostTaxonomyRelationshipQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return PostTaxonomyRelationship[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return PostTaxonomyRelationship|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
