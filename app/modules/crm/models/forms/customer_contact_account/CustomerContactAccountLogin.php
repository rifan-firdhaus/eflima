<?php namespace modules\crm\models\forms\customer_contact_account;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\crm\models\CustomerContactAccount;
use Yii;
use yii\base\InvalidConfigException;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class CustomerContactAccountLogin extends CustomerContactAccount
{

    public $remember_me;
    protected $_current;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['password', 'username'], 'required'],
            ['username', 'validateUsername'],
            ['password', 'validatePassword'],
            ['remember_me', 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return 'login';
    }

    /**
     * @param string $attribute
     *
     * @throws InvalidConfigException
     */
    public function validateUsername($attribute)
    {
        if (!$this->getCurrent()) {
            $this->addError($attribute, Yii::t('app', 'Username is not registered yet.'));

            return;
        }

        if ($this->getCurrent()->is_blocked) {
            $this->addError($attribute, Yii::t('app', 'Account is blocked'));

            return;
        }
    }

    /**
     * @param string $attribute
     *
     * @throws InvalidConfigException
     */
    public function validatePassword($attribute)
    {
        if ($this->hasErrors()) {
            return;
        }

        if (!$this->getCurrent()->verifyPassword($this->{$attribute})) {
            $this->addError($attribute, Yii::t('app', "Incorrect password"));
        }
    }

    /**
     * @return null|CustomerContactAccount
     * @throws InvalidConfigException
     */
    public function getCurrent()
    {
        if (!$this->_current) {
            $this->_current = CustomerContactAccount::find()->username($this->username)->one();
        }

        return $this->_current;
    }

    /**
     * @param int $duration
     *
     * @return bool
     * @throws InvalidConfigException
     */
    public function login($duration = 0)
    {
        if (!$this->validate()) {
            return false;
        }

        if ($this->remember_me && $duration === 0) {
            $duration = 24 * 60 * 60 * 60;
        }

        return Yii::$app->user->login($this->getCurrent(), $duration);
    }

    /**
     * @inheritdoc
     */
    public function loadDefaultValues($skipIfSet = true)
    {
        parent::loadDefaultValues($skipIfSet);

        if (!$skipIfSet || !isset($this->remember_me)) {
            $this->remember_me = true;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'remember_me' => Yii::t('app', 'Remember me'),
        ]);
    }
}
