<?php namespace modules\content\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\content\models\queries\PostTaxonomyQuery;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property PostTaxonomy   $parent
 * @property PostTaxonomy[] $children
 *
 * @property int            $id            [int(10) unsigned]
 * @property int            $parent_id     [int(11) unsigned]
 * @property string         $title
 * @property string         $picture
 * @property string         $type_id       [varchar(36)]
 * @property string         $content
 * @property bool           $is_enabled    [tinyint(1)]
 * @property int            $created_at    [int(11) unsigned]
 * @property int            $updated_at    [int(11) unsigned]
 */
class PostTaxonomy extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%post_taxonomy}}';
    }

    /**
     * @inheritdoc
     *
     * @return PostTaxonomyQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new PostTaxonomyQuery(get_called_class());

        return $query->alias("post_taxonomy");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id', 'is_enabled', 'created_at', 'updated_at'], 'integer'],
            [['title'], 'required'],
            [['title', 'picture', 'content'], 'string'],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => PostTaxonomy::class, 'targetAttribute' => ['parent_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'parent_id' => Yii::t('app', 'Parent ID'),
            'title' => Yii::t('app', 'Title'),
            'picture' => Yii::t('app', 'Picture'),
            'content' => Yii::t('app', 'Content'),
            'is_enabled' => Yii::t('app', 'Is Enabled'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return ActiveQuery|PostTaxonomyQuery
     */
    public function getParent()
    {
        return $this->hasOne(PostTaxonomy::class, ['id' => 'parent_id']);
    }

    /**
     * @return ActiveQuery|PostTaxonomyQuery
     */
    public function getChildren()
    {
        return $this->hasMany(PostTaxonomy::class, ['parent_id' => 'id']);
    }
}
