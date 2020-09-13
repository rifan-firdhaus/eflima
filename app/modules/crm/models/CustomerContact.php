<?php

namespace modules\crm\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\address\models\Country;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\crm\models\queries\CustomerContactQuery;
use modules\file_manager\helpers\ImageVersion;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;
use yii\helpers\Inflector;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property CustomerContactAccount $account
 * @property Customer               $customer
 * @property string                 $name
 * @property Country                $country
 *
 * @property int                    $id                       [int(10) unsigned]
 * @property int                    $customer_id              [int(11) unsigned]
 * @property int                    $account_id               [int(11) unsigned]
 * @property string                 $first_name
 * @property string                 $last_name
 * @property bool                   $is_primary               [tinyint(1)]
 * @property bool                   $has_customer_area_access [tinyint(1)]
 * @property string                 $phone
 * @property string                 $mobile
 * @property string                 $city
 * @property string                 $email
 * @property string                 $province
 * @property string                 $country_code             [char(3)]
 * @property string                 $postal_code
 * @property string                 $address
 * @property string                 $facebook
 * @property string                 $twitter
 * @property string                 $instagram
 * @property string                 $pinterest
 * @property string                 $linkedin
 * @property string                 $whatsapp
 * @property string                 $line
 * @property string                 $wechat
 * @property string                 $telegram
 * @property string                 $github
 * @property int                    $created_at               [int(11) unsigned]
 * @property int                    $updated_at               [int(11) unsigned]
 */
class CustomerContact extends ActiveRecord
{
    /** @var CustomerContactAccount */
    public $accountModel;

    protected $deletedAccountId;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_contact}}';
    }

    /**
     * @inheritdoc
     *
     * @return CustomerContactQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new CustomerContactQuery(get_called_class());

        return $query->alias("customer_contact");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['has_customer_area_access'], 'boolean'],
            [
                [
                    'phone',
                    'mobile',
                    'whatsapp',
                    'line',
                    'github',
                    'bbm',
                    'twitter',
                    'instagram',
                    'wechat',
                    'telegram',
                    'pinterest',
                    'address',
                    'country_code',
                    'province',
                    'city',
                    'address',
                    'postal_code',
                ],
                'safe',
            ],
            ['email', 'email'],
            [
                ['first_name', 'last_name'],
                'required',
            ],
            [
                'customer_id',
                'exist',
                'targetRelation' => 'customer',
            ],
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
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios['admin/add'] = $scenarios['default'];
        $scenarios['admin/update'] = $scenarios['admin/add'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($this->accountModel && $this->has_customer_area_access) {
            if (!($this->accountModel instanceof CustomerContactAccount)) {
                throw new InvalidConfigException("\$this->accountModel must instance of " . CustomerContactAccount::class);
            }

            $this->accountModel->email = $this->email;

            if (!$this->accountModel->save()) {
                return false;
            }

            $this->account_id = $this->accountModel->id;
        } elseif ($this->scenario === 'admin/update' && !$this->has_customer_area_access && $this->account) {
            $this->deletedAccountId = $this->account_id;
            $this->account_id = null;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert && empty($this->account->avatar)) {
            $avatarFile = $this->account->getFilePath('avatar', Inflector::slug($this->name) . '-' . rand(10, 900) . '.jpg');

            ImageVersion::instance()->placeholder($this->name)->save($avatarFile);

            $this->account->avatar = basename($avatarFile);

            $this->account->save(false);
        }

        if ($this->deletedAccountId) {
            $account = CustomerContactAccount::find()->andWhere(['id' => $this->deletedAccountId])->one();

            if ($account && !$account->delete()) {
                throw new Exception('Failed to delete corresponding account');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function transactions()
    {
        $transactions = parent::transactions();

        $transactions['admin/add'] = self::OP_ALL;
        $transactions['admin/update'] = self::OP_ALL;

        return $transactions;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'customer_id' => Yii::t('app', 'Customer'),
            'account_id' => Yii::t('app', 'Account'),
            'first_name' => Yii::t('app', 'First Name'),
            'last_name' => Yii::t('app', 'Last Name'),
            'is_primary' => Yii::t('app', 'Is Primary'),
            'has_customer_area_access' => Yii::t('app', 'Has Customer Area Access'),
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
            'country_code' => Yii::t('app', 'Country'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(CustomerContactAccount::class, ['id' => 'account_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::class, ['code' => 'country_code'])->alias('country_of_contact');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return implode(' ', array_filter([$this->first_name, $this->last_name]));
    }

    /**
     * @return string
     */
    public function getFullAddress()
    {
        $components = $this->getAttributes(['address', 'city', 'province']);

        if ($this->country_code) {
            $components['country'] = $this->country->name;
        }

        $components = array_filter($components);

        return implode(', ', $components);
    }
}
