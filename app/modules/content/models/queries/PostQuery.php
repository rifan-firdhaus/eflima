<?php namespace modules\content\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\content\models\Post;
use modules\core\db\ActiveQuery;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\content\models\Post]].
 *
 * @see    Post
 */
class PostQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return Post[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return Post|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }


    /**
     * @return $this
     */
    public function published()
    {
        return $this->andWhere(["{$this->getAlias()}.is_published" => true]);
    }

    /**
     * @return $this
     */
    public function notPublished()
    {
        return $this->andWhere(["{$this->getAlias()}.is_published" => false]);
    }
}
