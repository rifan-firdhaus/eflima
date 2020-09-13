<?php

namespace modules\account\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\Account as AccountModule;
use modules\account\models\queries\StaffAccountQuery;
use modules\account\models\queries\StaffQuery;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\file_manager\helpers\ImageVersion;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property StaffAccount $account
 * @property string               $name
 * @property array                $historyParams
 * @property bool                 $isRoot
 *
 * @property int                  $id         [int(10) unsigned]
 * @property int                  $account_id [int(11) unsigned]
 * @property string               $first_name
 * @property string               $last_name
 * @property int                  $created_at [int(11) unsigned]
 * @property int                  $updated_at [int(11) unsigned]
 */
class Staff extends ActiveRecord
{
    /** @var Staff|null */
    protected static $_root;

    /** @var StaffAccount */
    public $accountModel;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%staff}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['first_name', 'last_name'],
                'string',
                'on'  => ['admin/add','admin/update']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios['install'] = [];

        return ArrayHelper::merge(parent::scenarios(), $scenarios);
    }

    /**
     * @inheritdoc
     */
    public function transactions()
    {
        $transactions = parent::transactions();

        $transactions['default'] = self::OP_ALL;
        $transactions['admin/add'] = self::OP_ALL;
        $transactions['admin/update'] = self::OP_ALL;

        return $transactions;
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
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'account_id' => Yii::t('app', 'Account ID'),
            'first_name' => Yii::t('app', 'First Name'),
            'last_name' => Yii::t('app', 'Last Name'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return ActiveQuery|StaffAccountQuery
     */
    public function getAccount()
    {
        return $this->hasOne(StaffAccount::class, ['id' => 'account_id'])->alias('account_of_staff');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return implode(' ', array_filter([$this->first_name, $this->last_name]));
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($this->accountModel) {
            if (!($this->accountModel instanceof StaffAccount)) {
                throw new InvalidConfigException("\$this->accountModel must instance of " . StaffAccount::class);
            }

            if (!$this->accountModel->save()) {
                return false;
            }

            $this->account_id = $this->accountModel->id;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if (in_array($this->scenario, ['admin/add', 'admin/update'])) {
            $history = [
                'params' => $this->getHistoryParams(),
            ];

            if ($this->scenario === 'admin/add') {
                $history['description'] = 'Adding staff - {username}';
            } else {
                $history['description'] = 'Updating staff - {username}';
            }

            $historyEvent = $this->scenario === 'admin/add' ? 'staff.add' : 'staff.update';
            $history['tag'] = $this->scenario === 'admin/add' ? 'add' : 'update';

            AccountModule::history()->save($historyEvent, $history);
        }

        if ($insert && empty($this->account->avatar)) {
            $avatarFile = $this->account->getFilePath('avatar', $this->account->username . '-' . rand(10, 900) . '.jpg');

            ImageVersion::instance()->placeholder($this->name)->save($avatarFile);

            $this->account->avatar = basename($avatarFile);

            $this->account->save(false);
        }
    }

    /**
     * @return array
     */
    public function getHistoryParams()
    {
        $params = $this->getAttributes(['id', 'first_name', 'last_name']);
        $params = array_merge($params, $this->account->getAttributes(['username', 'email']));

        return $params;
    }

    /**
     * @return bool
     * @throws InvalidConfigException
     */
    public function getIsRoot()
    {
        return $this->id == self::root()->id;
    }

    /**
     * @param bool $refresh
     *
     * @return Staff
     * @throws InvalidConfigException
     */
    public static function root($refresh = false)
    {
        if (!self::$_root || $refresh) {
            self::$_root = self::find()->orderBy(['staff.id' => SORT_ASC])->one();
        }

        return self::$_root;
    }

    /**
     * @inheritdoc
     *
     * @return StaffQuery
     */
    public static function find()
    {
        $query = new StaffQuery(get_called_class());

        return $query->alias("staff");
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        $isAccountModelValid = $this->accountModel && !$this->accountModel->validate();

        if (!parent::beforeValidate() || $isAccountModelValid) {
            return false;
        }

        return true;
    }
}
