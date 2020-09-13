<?php namespace modules\address\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\address\models\queries\ProvinceQuery;
use modules\core\behaviors\AttributeTypecastBehavior;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\core\models\traits\VisibilityModel;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property City[]  $cities
 * @property Country $country
 *
 * @property string  $code         [varchar(255)]
 * @property string  $country_code [char(3)]
 * @property bool    $is_enabled   [tinyint(1)]
 * @property string  $name
 */
class Province extends ActiveRecord
{
    use VisibilityModel;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%province}}';
    }

    /**
     * @inheritdoc
     *
     * @return ProvinceQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new ProvinceQuery(get_called_class());

        return $query->alias("province");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_enabled'], 'integer'],
            [['name'], 'required'],
            [['code'], 'string', 'max' => 255],
            [['country_code'], 'string', 'max' => 3],
            [['code'], 'unique'],
            [['country_code'], 'exist', 'skipOnError' => true, 'targetClass' => Country::className(), 'targetAttribute' => ['country_code' => 'code']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $default = parent::scenarios();

        $scenarios['admin/add'] = $default['default'];
        $scenarios['admin/update'] = $default['default'];

        return ArrayHelper::merge($scenarios, $default);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'code' => Yii::t('app', 'Code'),
            'country_code' => Yii::t('app', 'Country'),
            'is_enabled' => Yii::t('app', 'Enabled'),
            'name' => Yii::t('app', 'Name'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getCities()
    {
        return $this->hasMany(City::className(), ['province_code' => 'code'])->alias('cities_of_province');
    }

    /**
     * @return ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::class, ['code' => 'country_code'])->alias('country_of_province');
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
            'country',
        ]);
    }
}
