<?php namespace modules\address\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\address\models\queries\CountryQuery;
use modules\core\behaviors\AttributeTypecastBehavior;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\core\models\traits\VisibilityModel;
use Throwable;
use Yii;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Province[]  $provinces
 * @property null|string $continent
 *
 * @property string      $code           [char(3)]
 * @property string      $iso2           [char(2)]
 * @property string      $name
 * @property string      $phone_code
 * @property bool        $is_enabled     [tinyint(1)]
 * @property string      $continent_code [char(2)]
 * @property string      $currency_code  [char(3)]
 */
class Country extends ActiveRecord
{
    use VisibilityModel;

    const CONTINENT_ASIA = 'AS';
    const CONTINENT_EUROPE = 'EU';
    const CONTINENT_ANTARTICA = 'AN';
    const CONTINENT_AFRICA = 'AF';
    const CONTINENT_NORTH_AMERICA = 'NA';
    const CONTINENT_OCEANIA = 'OC';
    const CONTINENT_SOUTH_AMERICA = 'SA';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%country}}';
    }

    /**
     * @inheritdoc
     *
     * @return CountryQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new CountryQuery(get_called_class());

        return $query->alias("country");
    }

    /**
     * @return null|string
     */
    public function getContinent()
    {
        return self::continents($this->continent_code);
    }

    /**
     * @param bool|string $continent
     *
     * @return array|string|null
     */
    public static function continents($continent = false)
    {
        $continents = [
            self::CONTINENT_NORTH_AMERICA => Yii::t('app', 'North America'),
            self::CONTINENT_SOUTH_AMERICA => Yii::t('app', 'South America'),
            self::CONTINENT_EUROPE => Yii::t('app', 'Europe'),
            self::CONTINENT_ASIA => Yii::t('app', 'Asia'),
            self::CONTINENT_AFRICA => Yii::t('app', 'Africa'),
            self::CONTINENT_OCEANIA => Yii::t('app', 'Oceania'),
            self::CONTINENT_ANTARTICA => Yii::t('app', 'Antartica'),
        ];

        if ($continent !== false) {
            return isset($continents[$continent]) ? $continents[$continent] : null;
        }

        return $continents;
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
    public function rules()
    {
        return [
            [['name', 'phone_code'], 'string'],
            [['is_enabled'], 'boolean'],
            [['code', 'currency_code'], 'string', 'max' => 3],
            [['iso2', 'continent_code'], 'string', 'max' => 2],
            [['iso2'], 'unique'],
            [['code'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'code' => Yii::t('app', 'ISO3 Code'),
            'iso2' => Yii::t('app', 'ISO2 Code'),
            'name' => Yii::t('app', 'Name'),
            'phone_code' => Yii::t('app', 'Phone Code'),
            'is_enabled' => Yii::t('app', 'Enabled'),
            'continent_code' => Yii::t('app', 'Continent'),
            'currency_code' => Yii::t('app', 'Currency Code'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete();

        $this->deleteRelations();
    }

    /**
     * @throws Exception
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function deleteRelations()
    {
        foreach ($this->provinces AS $province) {
            if (!$province->delete()) {
                throw new Exception('Failed to delete related province');
            }
        }
    }

    /**
     * @return ActiveQuery
     */
    public function getProvinces()
    {
        return $this->hasMany(Province::className(), ['country_code' => 'code'])->alias('provinces_of_country');
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
    public function fields()
    {
        return ArrayHelper::merge(parent::fields(), [
            'continent' => 'continent',
        ]);
    }
}
