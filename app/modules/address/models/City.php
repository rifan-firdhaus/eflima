<?php namespace modules\address\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\address\models\queries\CityQuery;
use modules\core\behaviors\AttributeTypecastBehavior;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\core\models\traits\VisibilityModel;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Province $province
 *
 * @property int      $id            [int(10) unsigned]
 * @property string   $code          [varchar(255)]
 * @property string   $province_code [varchar(255)]
 * @property bool     $is_enabled    [tinyint(1)]
 * @property string   $name
 */
class City extends ActiveRecord
{
    use VisibilityModel;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%city}}';
    }

    /**
     * @inheritdoc
     *
     * @return CityQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new CityQuery(get_called_class());

        return $query->alias("city");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_enabled'], 'integer'],
            [['name'], 'string'],
            [['code', 'province_code'], 'string', 'max' => 255],
            [['code'], 'unique'],
            [['province_code'], 'exist', 'skipOnError' => true, 'targetClass' => Province::className(), 'targetAttribute' => ['province_code' => 'code']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'code' => Yii::t('app', 'Code'),
            'province_code' => Yii::t('app', 'Province'),
            'is_enabled' => Yii::t('app', 'Enabled'),
            'name' => Yii::t('app', 'Name'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getProvince()
    {
        return $this->hasOne(Province::class, ['code' => 'province_code'])->alias('province_of_city');
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['attributeTypecast'] = [
            'class' => AttributeTypecastBehavior::class,
            'attributeTypes' => [
                'id' => AttributeTypecastBehavior::TYPE_INTEGER,
                'is_enabled' => AttributeTypecastBehavior::TYPE_BOOLEAN,
            ],
        ];

        return $behaviors;
    }

    /**
     * @inheritDoc
     */
    public function extraFields()
    {
        return ArrayHelper::merge(parent::extraFields(), [
            'province',
        ]);
    }
}
