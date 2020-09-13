<?php namespace modules\content\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\content\models\queries\PostQuery;
use modules\content\models\queries\PostTaxonomyQuery;
use modules\content\models\queries\PostTaxonomyRelationshipQuery;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 *
 * @property Post         $post
 * @property PostTaxonomy $taxonomy
 *
 * @property int          $id          [int(10) unsigned]
 * @property int          $post_id     [int(11) unsigned]
 * @property int          $taxonomy_id [int(11) unsigned]
 */
class PostTaxonomyRelationship extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%post_taxonomy_relationship}}';
    }

    /**
     * @inheritdoc
     * @return PostTaxonomyRelationshipQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new PostTaxonomyRelationshipQuery(get_called_class());

        return $query->alias("post_taxonomy_relationship");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['post_id', 'taxonomy_id'], 'required'],
            [['post_id', 'taxonomy_id'], 'integer'],
            [['post_id'], 'exist', 'skipOnError' => true, 'targetClass' => Post::class, 'targetAttribute' => ['post_id' => 'id']],
            [['taxonomy_id'], 'exist', 'skipOnError' => true, 'targetClass' => PostTaxonomy::class, 'targetAttribute' => ['taxonomy_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'post_id' => Yii::t('app', 'Post ID'),
            'taxonomy_id' => Yii::t('app', 'Taxonomy ID'),
        ];
    }

    /**
     * @return ActiveQuery|PostQuery
     */
    public function getPost()
    {
        return $this->hasOne(Post::class, ['id' => 'post_id']);
    }

    /**
     * @return ActiveQuery|PostTaxonomyQuery
     */
    public function getTaxonomy()
    {
        return $this->hasOne(PostTaxonomy::class, ['id' => 'taxonomy_id']);
    }
}
