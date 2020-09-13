<?php namespace modules\finance\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\core\behaviors\AttributeTypecastBehavior;
use modules\core\db\ActiveRecord;
use modules\core\models\traits\VisibilityModel;
use modules\finance\models\queries\ProductQuery;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property int    $id         [int(10) unsigned]
 * @property string $name
 * @property string $description
 * @property string $price      [decimal(25,10)]
 * @property bool   $is_enabled [tinyint(1)]
 * @property int    $created_at [int(11) unsigned]
 * @property int    $updated_at [int(11) unsigned]
 */
class Product extends ActiveRecord
{
    use VisibilityModel;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product}}';
    }

    /**
     * @inheritdoc
     *
     * @return ProductQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new ProductQuery(get_called_class());

        return $query->alias("product");
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['timestamp'] = [
            'class' => TimestampBehavior::class,
        ];

        $behaviors['attributeTypecast'] = [
            'class' => AttributeTypecastBehavior::class,
            'attributeTypes' => [
                'price' => AttributeTypecastBehavior::TYPE_FLOAT,
            ],
        ];

        return $behaviors;
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
                'on' => ['admin/add', 'admin/update'],
            ],
            [
                ['name', 'description'],
                'string',
            ],
            [
                ['is_enabled'],
                'boolean',
            ],
            [
                ['price'],
                'number',
                'min' => 0,
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
            'description' => Yii::t('app', 'Description'),
            'price' => Yii::t('app', 'Price'),
            'is_enabled' => Yii::t('app', 'Enabled'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
