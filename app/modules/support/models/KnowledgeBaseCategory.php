<?php namespace modules\support\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\core\models\traits\VisibilityModel;
use modules\support\models\queries\KnowledgeBaseCategoryQuery;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property KnowledgeBase[] $knowledgeBases
 *
 * @property int             $id         [int(10) unsigned]
 * @property string          $name
 * @property bool            $is_enabled [tinyint(1)]
 * @property int             $created_at [int(11) unsigned]
 * @property int             $updated_at [int(11) unsigned]
 */
class KnowledgeBaseCategory extends ActiveRecord
{
    use VisibilityModel;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%knowledge_base_category}}';
    }

    /**
     * @inheritdoc
     *
     * @return KnowledgeBaseCategoryQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new KnowledgeBaseCategoryQuery(get_called_class());

        return $query->alias("knowledge_base_category");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['name'],
                'required',
            ],
            [
                'is_enabled',
                'boolean',
            ],
            [
                ['description', 'name'],
                'string',
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
            'name' => Yii::t('app', 'Name'),
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

        return $behaviors;
    }

    /**
     * @return ActiveQuery
     */
    public function getKnowledgeBases()
    {
        return $this->hasMany(KnowledgeBase::class, ['category_id' => 'id']);
    }
}
