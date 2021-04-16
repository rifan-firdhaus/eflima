<?php namespace modules\account\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\Account as AccountModule;
use modules\account\models\queries\StaffAccountQuery;
use modules\account\models\queries\StaffQuery;
use modules\core\db\ActiveQuery;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\web\JsExpression;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property int   $notificationCount
 * @property Staff $profile
 */
class StaffAccount extends Account
{
    public $role;

    /**
     * @inheritdoc
     */
    public static function find()
    {
        $query = new StaffAccountQuery(get_called_class());

        return $query->alias('staff_account');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $passwordInputId = Html::getInputId($this, 'password');

        // TODO: add username regex validation

        return [
            [
                ['username', 'email', 'role'],
                'required',
                'on' => ['admin/add', 'admin/update'],
            ],
            [
                ['uploaded_avatar'],
                'image',
            ],
            [
                ['email'],
                'email',
            ],
            [
                ['username', 'email'],
                'unique',
            ],
            [
                ['username'],
                'string',
                'min' => 4,
                'max' => 32,
            ],
            [
                ['password'],
                'required',
                'on' => 'admin/add',
            ],
            [
                ['password'],
                'string',
                'min' => 6,
            ],
            [
                ['password_repeat'],
                'required',
                'when' => function ($model) {
                    /** @var StaffAccount $model */

                    return !empty($model->password);
                },
                'whenClient' => new JsExpression("function(){return $('#{$passwordInputId}').val() !== ''}"),
            ],
            [
                'password_repeat',
                'compare',
                'compareAttribute' => 'password',
                'message' => Yii::t('app', 'Value does\'t match with password'),
            ],
            [
                'revisor_password',
                'required',
                'when' => function ($model) {
                    /** @var StaffAccount $model */

                    return !$model->isNewRecord && $this->scenario === 'admin/update' && !empty($this->password);
                },
            ],
            [
                'revisor_password',
                'validateRevisorPassword',
                'when' => function ($model) {
                    /** @var StaffAccount $model */

                    return !$model->isNewRecord && $this->scenario === 'admin/update' && !empty($this->password);
                },
            ],
        ];
    }

    /**
     * @param string $attribute
     */
    public function validateRevisorPassword($attribute)
    {
        if ($this->hasErrors($attribute)) {
            return;
        }

        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;

        if (!$account->verifyPassword($this->{$attribute})) {
            $this->addError($attribute, Yii::t('app', 'Invalid password'));
        }
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios['install'] = $scenarios['default'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return array_merge(parent::attributeHints(), [
            'password' => Yii::t('app', 'Be wise to choose password, avoid use birthday, name or personal identity for password'),
            'password_repeat' => Yii::t('app', 'Retype your password to make sure your password is not typo'),
        ]);
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
     * @return ActiveQuery|StaffQuery
     */
    public function getProfile()
    {
        return $this->hasOne(Staff::class, ['account_id' => 'id'])->alias('profile_of_account');
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return self::find()->andWhere(['staff_account.id' => $id, 'is_blocked' => false])->with(["profile"])->one();
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->type = 'staff';

        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if (
            array_key_exists('is_blocked', $changedAttributes) &&
            $changedAttributes['is_blocked'] != $this->is_blocked
        ) {
            $historyAction = $this->is_blocked ? 'Blocking' : 'Unblocking';
            $historyEvent = $this->is_blocked ? 'block' : 'unblock';

            AccountModule::history()->save("staff.{$historyEvent}", [
                'params' => $this->profile->getHistoryParams(),
                'description' => "{$historyAction} staff - {username}",
                'tag' => $historyEvent,
            ]);
        }

        $authManager = Yii::$app->authManager;

        if ($this->role) {
            if ($authManager->getRolesByUser($this->id)) {
                $authManager->revokeAll($this->id);
            }

            $role = $authManager->getRole($this->role);

            $authManager->assign($role, $this->id);
        }
    }

    /**
     * @return int
     *
     * @throws InvalidConfigException
     */
    public function getNotificationCount()
    {
        return (int) AccountNotification::find()->notSeen($this)->count();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'password_repeat' => Yii::t('app', 'Repeat'),
        ]);
    }
}
