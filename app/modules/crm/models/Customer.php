<?php namespace modules\crm\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\address\models\Country;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\crm\behaviors\CustomerGroupCreationBehavior;
use modules\crm\models\queries\CustomerQuery;
use modules\file_manager\behaviors\FileUploaderBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception as DbException;
use yii\helpers\Html;
use yii\web\JsExpression;
use function array_filter;
use function array_keys;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property CustomerGroup     $group
 * @property Customer          $lead
 * @property LeadSource        $leadSource
 * @property LeadStatus        $leadStatus
 * @property CustomerContact[] $contacts
 * @property Customer          $convertedCustomer
 * @property null|string       $typeText
 * @property Country           $country
 * @property CustomerContact   $primaryContact
 * @property mixed             $fullAddress
 * @property string            $name
 *
 * @property int               $id             [int(10) unsigned]
 * @property int               $group_id       [int(11) unsigned]
 * @property int               $lead_id        [int(11) unsigned]
 * @property string            $company_name
 * @property string            $company_logo
 * @property string            $vat_number
 * @property string            $currency_code  [char(3)]
 * @property string            $city
 * @property string            $province
 * @property string            $country_code   [char(3)]
 * @property string            $address
 * @property string            $type           [char(1)]
 * @property string            $phone
 * @property string            $postal_code
 * @property string            $fax
 * @property string            $email
 * @property bool              $is_archieved   [tinyint(1)]
 * @property int               $created_at     [int(11) unsigned]
 * @property int               $updated_at     [int(11) unsigned]
 */
class Customer extends ActiveRecord
{
    const TYPE_COMPANY = 'C';
    const TYPE_PERSONAL = 'I';

    public $new_group;

    /** @var CustomerContact */
    public $primaryContactModel;

    public $uploaded_company_logo;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer}}';
    }

    /**
     * @inheritdoc
     *
     * @return CustomerQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new CustomerQuery(get_called_class());

        return $query->alias("customer");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $typeInputId = Html::getInputId($this, 'type');

        return [
            [['type'], 'in', 'range' => array_keys(self::types())],
            [
                ['company_name'],
                'required',
                'when' => function ($model) {
                    /** @var Customer $model */

                    return $model->type === self::TYPE_COMPANY;
                },
                'whenClient' => new JsExpression("function(){return $('#$typeInputId').find('[type=radio]:checked').val() === '" . self::TYPE_COMPANY . "'}"),
            ],
            [
                'company_logo',
                'image',
            ],
            ['email', 'email'],
            [['phone', 'new_group', 'fax', 'address', 'province', 'country_code', 'vat_number', 'postal_code', 'city'], 'safe'],
        ];
    }

    /**
     * @param bool $type
     *
     * @return array|string|null
     */
    public static function types($type = false)
    {
        $types = [
            self::TYPE_PERSONAL => Yii::t('app', 'Personal'),
            self::TYPE_COMPANY => Yii::t('app', 'Company'),
        ];

        if ($type !== false) {
            return isset($types[$type]) ? $types[$type] : null;
        }

        return $types;
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

        $behaviors['customerGroupCreation'] = [
            'class' => CustomerGroupCreationBehavior::class,
            'attribute' => 'group_id',
            'aliasAttribute' => 'new_group',
        ];

        $behaviors['fileUploader'] = [
            'class' => FileUploaderBehavior::class,
            'attributes' => [
                'company_logo' => [
                    'alias' => 'uploaded_company_logo',
                    'base_path' => '@webroot/protected/system/customer/company-logo',
                    'base_url' => '@web/protected/system/customer/company-logo',
                ],
            ],
        ];

        return $behaviors;
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
            'group_id' => Yii::t('app', 'Group'),
            'lead_id' => Yii::t('app', 'Lead'),
            'lead_status_id' => Yii::t('app', 'Status'),
            'lead_source_id' => Yii::t('app', 'Source'),
            'company_name' => Yii::t('app', 'Company Name'),
            'currency_code' => Yii::t('app', 'Currency'),
            'vat_number' => Yii::t('app', 'VAT Number'),
            'city' => Yii::t('app', 'City'),
            'province' => Yii::t('app', 'Province'),
            'country_code' => Yii::t('app', 'Country'),
            'address' => Yii::t('app', 'Address'),
            'type' => Yii::t('app', 'Type'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'uploaded_company_logo' => Yii::t('app', 'Logo'),
            'company_logo' => Yii::t('app', 'Logo'),
        ];
    }

    /**
     * @return null|string
     */
    public function getTypeText()
    {
        return self::types($this->type);
    }

    /**
     * @return ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(CustomerGroup::class, ['id' => 'group_id'])->alias('group_of_customer');
    }

    /**
     * @return ActiveQuery
     */
    public function getLead()
    {
        return $this->hasOne(Customer::class, ['id' => 'lead_id'])->alias('lead_of_customer');
    }

    /**
     * @return ActiveQuery
     */
    public function getConvertedCustomer()
    {
        return $this->hasOne(Customer::class, ['lead_id' => 'id'])->alias('converted_customer_of_lead');
    }

    /**
     * @return ActiveQuery
     */
    public function getLeadSource()
    {
        return $this->hasOne(LeadSource::class, ['id' => 'lead_source_id'])->alias('source_of_lead');
    }

    /**
     * @return ActiveQuery
     */
    public function getLeadStatus()
    {
        return $this->hasOne(LeadStatus::class, ['id' => 'lead_status_id'])->alias('status_of_lead');
    }

    /**
     * @return ActiveQuery
     */
    public function getContacts()
    {
        return $this->hasMany(CustomerContact::class, ['customer_id' => 'id'])->alias('contacts_of_customer');
    }

    /**
     * @return ActiveQuery
     */
    public function getPrimaryContact()
    {
        return $this->hasOne(CustomerContact::class, ['customer_id' => 'id'])->alias('primary_contact_of_customer');
    }

    /**
     * @return ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::class, ['code' => 'country_code'])->alias('country_of_customer');
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($this->primaryContactModel) {
            $this->primaryContactModel->customer_id = $this->id;

            if (!$this->primaryContactModel->save()) {
                throw new DbException('Failed to save primary contact');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($this->primaryContactModel && $this->type === self::TYPE_PERSONAL) {
            $attributes = $this->primaryContactModel->getAttributes([
                'address',
                'postal_code',
                'province',
                'city',
                'country_code',
                'phone',
                'email',
            ]);
            $attributes['company_name'] = $this->primaryContactModel->name;

            $this->setAttributes($attributes);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function loadDefaultValues($skipIfSet = true)
    {
        if (!$skipIfSet || !isset($this->type)) {
            $this->type = self::TYPE_COMPANY;
        }

        if (!isset($this->currency_code) || !$skipIfSet) {
            $this->currency_code = Yii::$app->setting->get('finance/base_currency');
        }

        return parent::loadDefaultValues($skipIfSet); // TODO: Change the autogenerated stub
    }

    /**
     * @return string
     */
    public function getName()
    {
        if ($this->type === self::TYPE_PERSONAL) {
            return $this->primaryContact->name;
        }

        return $this->company_name;
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
