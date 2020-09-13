<?php namespace modules\account\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\address\models\City;
use modules\address\models\Province;
use modules\address\models\queries\CityQuery;
use modules\address\models\queries\ProvinceQuery;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Account       $account
 * @property Province|null $province
 * @property City|null     $city
 *
 * @property int           $id               [int(10) unsigned]
 * @property int           $account_id       [int(11) unsigned]
 * @property string        $phone
 * @property string        $mobile
 * @property string        $city_name
 * @property int           $city_id          [int(11) unsigned]
 * @property string        $province_name
 * @property string        $province_code
 * @property string        $country_code     [char(3)]
 * @property string        $postal_code
 * @property string        $address
 * @property string        $facebook
 * @property string        $twitter
 * @property string        $instagram
 * @property string        $pinterest
 * @property string        $linkedin
 * @property string        $whatsapp
 * @property string        $line
 * @property string        $wechat
 * @property string        $telegram
 * @property string        $github
 */
class AccountContact extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account_contact}}';
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return parent::find()->alias("account_contact");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'phone',
                    'address',
                    'facebook',
                    'twitter',
                    'instagram',
                    'pinterest',
                    'linkedin',
                    'whatsapp',
                    'line',
                    'wechat',
                    'telegram',
                    'github',
                ],
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
            'account_id' => Yii::t('app', 'Account'),
            'phone' => Yii::t('app', 'Phone'),
            'address' => Yii::t('app', 'Address'),
            'facebook' => Yii::t('app', 'Facebook'),
            'twitter' => Yii::t('app', 'Twitter'),
            'instagram' => Yii::t('app', 'Instagram'),
            'pinterest' => Yii::t('app', 'Pinterest'),
            'linkedin' => Yii::t('app', 'Linkedin'),
            'whatsapp' => Yii::t('app', 'Whatsapp'),
            'line' => Yii::t('app', 'Line'),
            'wechat' => Yii::t('app', 'Wechat'),
            'telegram' => Yii::t('app', 'Telegram'),
            'github' => Yii::t('app', 'Github'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::class, ['id' => 'account_id'])->alias('account_of_contact');
    }

    /**
     * @return ActiveQuery|CityQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::class, ['id' => 'city_id'])->alias('city_of_contact');
    }

    /**
     * @return ActiveQuery|ProvinceQuery
     */
    public function getProvince()
    {
        return $this->hasOne(Province::class, ['code' => 'province_code'])->alias('province_of_contact');
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios['install'] = $scenarios['default'];
        $scenarios['admin/add'] = $scenarios['default'];
        $scenarios['admin/update'] = $scenarios['admin/add'];

        return $scenarios;
    }
}
