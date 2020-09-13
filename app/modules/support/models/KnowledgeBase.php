<?php namespace modules\support\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\core\models\traits\VisibilityModel;
use modules\support\behaviors\KnowledgeBaseCategoryCreationBehavior;
use modules\support\models\queries\KnowledgeBaseQuery;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property KnowledgeBaseCategory $category
 *
 * @property int                   $id          [int(10) unsigned]
 * @property int                   $category_id [int(11) unsigned]
 * @property string                $title
 * @property string                $content
 * @property bool                  $is_enabled  [tinyint(1)]
 * @property int                   $created_at  [int(11) unsigned]
 * @property int                   $updated_at  [int(11) unsigned]
 */
class KnowledgeBase extends ActiveRecord
{
    use VisibilityModel;
    public $new_category;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%knowledge_base}}';
    }

    /**
     * @inheritdoc
     *
     * @return KnowledgeBaseQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new KnowledgeBaseQuery(get_called_class());

        return $query->alias("knowledge_base");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['title', 'content', 'category_id'],
                'required',
                'on' => ['admin/add', 'admin/update'],
            ],
            [
                'is_enabled',
                'boolean',
            ],
            [
                "category_id",
                'exist',
                'targetRelation' => 'category',
                'when' => function ($model) {
                    return empty($model->new_category);
                },
            ],
            [
                ['title', 'content'],
                'string',
            ],
            [
                ['new_category'],
                'safe',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'category_id' => Yii::t('app', 'Category'),
            'title' => Yii::t('app', 'Title'),
            'content' => Yii::t('app', 'Content'),
            'is_enabled' => Yii::t('app', 'Enabled'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['timestamp'] = [
            'class' => TimestampBehavior::class,
        ];

        $behaviors['expenseCategoryCreation'] = [
            'class' => KnowledgeBaseCategoryCreationBehavior::class,
            'attribute' => 'category_id',
            'aliasAttribute' => 'new_category',
        ];

        return $behaviors;
    }

    /**
     * @return ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(KnowledgeBaseCategory::class, ['id' => 'category_id'])->alias('category_of_knowledge_base');
    }
}
