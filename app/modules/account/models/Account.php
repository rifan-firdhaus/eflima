<?php namespace modules\account\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\queries\AccountQuery;
use modules\core\behaviors\AttributeTypecastBehavior;
use modules\core\db\ActiveRecord;
use modules\file_manager\behaviors\FileUploaderBehavior;
use modules\file_manager\web\UploadedFile;
use Yii;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Exception as DbException;
use yii\web\IdentityInterface;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * - FileUploaderBehavior methods
 * @method bool uploadFile(string $attribute)
 * @method bool deleteFile(string $attribute, string | null $fileName = null)
 * @method string|bool getFilePath(string $attribute, string | null $fileName = null)
 * @method getFileUrl(string $attribute, bool | string $scheme = true, string | null $fileName = null)
 * @method bool|string getFileVersionPath(string $attribute, string $version = 'original')
 * @method bool|string getFileVersionUrl(string $attribute, string $version = 'original', string | bool $scheme = true)
 * @method array getFileMetadata(string $attribute, string | null $fileName = null)
 * @method string getFileAttributeByAlias(string $alias)
 *
 * - Getter
 * @property string              $authKey
 * @property AccountPreference[] $preferences
 * @property AccountContact      $contact
 * @property AccountSession[]    $sessions
 *
 * - Database Properties
 *
 * @property int                 $id                              [int(10) unsigned]
 * @property string              $username
 * @property string              $email
 * @property string              $password
 * @property string              $type                            [varchar(16)]
 * @property bool                $is_blocked                      [tinyint(1)]
 * @property string              $avatar
 * @property string              $access_token
 * @property string              $auth_key
 * @property string              $password_reset_token
 * @property int                 $password_reset_token_expired_at [int(11) unsigned]
 * @property int                 $last_activity_at                [int(11) unsigned]
 * @property int                 $confirmed_at                    [int(11) unsigned]
 * @property int                 $creator_id                      [int(11) unsigned]
 * @property int                 $created_at                      [int(11) unsigned]
 * @property int                 $updater_id                      [int(11) unsigned]
 * @property int                 $updated_at                      [int(11) unsigned]
 */
class Account extends ActiveRecord implements IdentityInterface
{
    /** @var UploadedFile|null */
    public $uploaded_avatar;
    public $password_repeat;
    public $revisor_password;

    /** @var AccountContact */
    public $contactModel;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return "{{%account}}";
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return self::find()->andWhere(['account.id' => $id, 'is_blocked' => false])->one();
    }

    /**
     * @inheritdoc
     *
     * @return AccountQuery
     */
    public static function find()
    {
        $query = Yii::createObject(AccountQuery::class, [get_called_class()]);

        return $query->alias('account');
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
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

        $behaviors['attributeTypecast'] = [
            'class' => AttributeTypecastBehavior::class,
            'attributeTypes' => [
                'is_blocked' => AttributeTypecastBehavior::TYPE_BOOLEAN,
            ],
        ];

        $behaviors['fileUploader'] = [
            'class' => FileUploaderBehavior::class,
            'attributes' => [
                'avatar' => [
                    'alias' => 'uploaded_avatar',
                    'base_path' => '@webroot/protected/system/account/avatar',
                    'base_url' => '@web/protected/system/account/avatar',
                ],
            ],
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @param string $password
     *
     * @return bool
     */
    public function verifyPassword($password)
    {
        return Yii::$app->getSecurity()->validatePassword($password, $this->password);
    }

    /**
     * @return ActiveQuery
     */
    public function getContact()
    {
        return $this->hasOne(AccountContact::class, ['account_id' => 'id'])->alias('contact_of_account');
    }

    /**
     * @return ActiveQuery
     */
    public function getPreferences()
    {
        return $this->hasMany(AccountPreference::class, ['account_id' => 'id'])->alias('preferences_of_account')->inverseOf('account');
    }

    /**
     * @param string     $key
     * @param null|mixed $default
     *
     * @return mixed
     */
    public function getPreferenceValue($key, $default = null)
    {
        $model = $this->getPreference($key)->one();

        if (!$model || empty($model->value)) {
            return $default;
        }

        return $model->value;
    }

    /**
     * @param string $key
     *
     * @return ActiveQuery
     */
    public function getPreference($key)
    {
        return $this->hasOne(AccountPreference::class, ['account_id' => 'id'])->andWhere(['account_preference.key' => $key]);
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public function setPreference($key, $value)
    {
        $model = $this->getPreference($key)->one();

        if (!$model) {
            $model = new AccountPreference(['key' => $key]);
        }

        $model->value = $value;

        return $model->save(false);
    }

    /**
     * @return ActiveQuery
     */
    public function getSessions()
    {
        return $this->hasMany(AccountSession::class, ['account_id' => 'id'])->alias('sessions_of_account');
    }

    /**
     * @return bool
     */
    public function unblock()
    {
        return $this->block(false);
    }

    /**
     * @param bool $block
     *
     * @return bool
     */
    public function block($block = true)
    {
        if ($this->is_blocked !== (boolean) $block) {
            $this->is_blocked = (boolean) $block;

            return $this->save(false);
        }

        if ($this->is_blocked) {
            $this->addError('is_blocked', Yii::t('app', 'Account has already been blocked'));
        } else {
            $this->addError('is_blocked', Yii::t('app', 'You can\'t unblock account that is not blocked'));
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'username' => Yii::t('app', 'Username'),
            'email' => Yii::t('app', 'Email'),
            'password' => Yii::t('app', 'Password'),
            'revisor_password' => Yii::t('app', 'Your Password'),
            'type' => Yii::t('app', 'Type'),
            'uploaded_avatar' => Yii::t('app', 'Avatar'),
            'avatar' => Yii::t('app', 'Avatar'),
            'is_blocked' => Yii::t('app', 'Blocked'),
            'access_token' => Yii::t('app', 'Access Token'),
            'auth_key' => Yii::t('app', 'Auth Key'),
            'password_reset_token' => Yii::t('app', 'Password Reset Token'),
            'password_reset_token_expired_at' => Yii::t('app', 'Password Reset Token Expired At'),
            'confirmed_at' => Yii::t('app', 'Confirmed At'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    public function attributeHints()
    {
        return [
            'revisor_password' => Yii::t('app', 'For security reason, In order to set new password for this account, you need to confirm your identitu by entering your password'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (!empty($this->password) && $this->isAttributeChanged('password')) {
            $this->password = Yii::$app->security->generatePasswordHash($this->password);
        } else {
            $this->password = $this->getOldAttribute('password');
        }

        if ($insert) {
            $this->auth_key = Yii::$app->security->generateRandomString();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        if (!parent::beforeValidate() || ($this->contactModel && !$this->contactModel->validate())) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($this->contactModel) {
            $this->contactModel->account_id = $this->id;

            if (!$this->contactModel->save()) {
                throw new ErrorException("Failed to save contact of this account");
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete();

        $comments = AccountComment::find()->andWhere(['account_id' => $this->id])->all();

        foreach ($comments AS $comment) {
            if (!$comment->delete()) {
                throw new DbException('Failed to delete related comments');
            }
        }

        $preferences = AccountPreference::find()->andWhere(['account_id' => $this->id])->all();

        foreach ($preferences AS $preference) {
            if (!$preference->delete()) {
                throw new DbException('Account preferences can\'t be deleted');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        if (!empty($this->contact) && !$this->contact->delete()) {
            throw new DbException('Failed to delete related contact');
        }

        return true;
    }

    /**
     * @return bool
     */
    public function setActive()
    {
        $this->last_activity_at = time();

        return $this->save(false);
    }

    /**
     * TODO: implement sending email
     *
     * @return bool
     * @throws Exception
     */
    public function resetPasswordRequest()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString(64);
        $this->password_reset_token_expired_at = strtotime('+12 hours');

        return $this->save(false);
    }

    /**
     * TODO: Implement the function
     */
    public function sendResetPasswordEmail()
    {

    }
}
