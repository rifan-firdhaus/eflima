<?php namespace modules\crm\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\Account;
use modules\core\db\ActiveQuery;
use modules\crm\models\queries\CustomerContactAccountQuery;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\JsExpression;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Customer $profile
 */
class CustomerContactAccount extends Account
{
    /** @var CustomerContact */
    public $customerContactModel;

    /**
     * @inheritdoc
     */
    public static function find()
    {
        $query = new CustomerContactAccountQuery(get_called_class());

        return $query->alias('customer_account');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $passwordInputId = Html::getInputId($this, 'password');
        $hasAccessInputId = $this->customerContactModel ? Html::getInputId($this->customerContactModel, 'has_customer_area_access') : null;
        $hasCustomerAccess = function ($model) {
            /** @var CustomerContactAccount $model */

            if ($model->customerContactModel) {
                return $model->customerContactModel->has_customer_area_access;
            }

            return true;
        };

        return [
            [
                ['password'],
                'required',
                'on' => 'admin/add',
                'when' => $hasCustomerAccess,
                'whenClient' => $hasAccessInputId ? new JsExpression("function(){return $('#{$hasAccessInputId}[type=checkbox]').is(':checked')}") : null,
            ],
            [
                ['password'],
                'string',
                'min' => 6,
            ],
            [
                ['password_repeat'],
                'required',
                'when' => $hasCustomerAccess,
                'whenClient' => new JsExpression("function(){return $('#{$passwordInputId}').val() !== '' && $('#{$hasAccessInputId}[type=checkbox]').is(':checked')}"),
            ],
            [
                'password_repeat',
                'compare',
                'compareAttribute' => 'password',
                'message' => Yii::t('app', 'Value does\'t match with password'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios['admin/add'] = [];
        $scenarios['admin/update'] = [];

        return ArrayHelper::merge(parent::scenarios(), $scenarios);
    }

    /**
     * @return ActiveQuery
     */
    public function getProfile()
    {
        return $this->hasOne(Customer::class, ['account_id' => 'id'])->alias('profile_of_account');
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
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->type = 'customer';

        if ($this->isAttributeChanged('email')) {
            $this->username = $this->email;
        }

        return true;
    }
}
