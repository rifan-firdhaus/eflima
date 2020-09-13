<?php namespace modules\account\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use InvalidArgumentException;
use modules\account\models\queries\AccountNotificationQuery;
use modules\core\db\ActiveRecord;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception as DbException;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property string $renderedUrl
 *
 * @property string $id              [char(16)]
 * @property string $title
 * @property string $body
 * @property string $body_params
 * @property string $title_params
 * @property string $url
 * @property bool   $is_internal_url [tinyint(1)]
 * @property string $data
 * @property string $receiver_type   [char(1)]
 * @property string $category        [varchar(64)]
 * @property int    $at              [int(11) unsigned]
 */
class AccountNotification extends ActiveRecord
{
    const TYPE_INDIVIDUAL = 'I';
    const TYPE_ACCOUNT_TYPE = 'T';

    /** @var array|int[]|string[]|Account[] */
    public $to = [];
    public $toAccountType;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account_notification}}';
    }

    /**
     * @param AccountNotification[] $models
     * @param Account               $account
     *
     * @return bool
     *
     * @throws DbException
     * @throws Throwable
     */
    public static function seenAll($models, $account)
    {
        $transaction = AccountNotification::getDb()->beginTransaction();

        try {
            foreach ($models AS $model) {
                $model->seen($account);
            }
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw $e;
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        $transaction->commit();

        return true;
    }

    /**
     * @param Account $account
     *
     * @return int
     */
    public function seen($account)
    {
        return AccountNotificationReceiver::updateAll(['is_seen' => true], [
            'AND',
            ['is_seen' => false],
            ['notification_id' => $this->id],
            [
                'OR',
                ['account_id' => $account->id],
                ['account_type' => $account->type],
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['timestamp'] = [
            'class' => TimestampBehavior::class,
            'updatedAtAttribute' => false,
            'createdAtAttribute' => 'at',
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function transactions()
    {
        return [
            'default' => self::OP_ALL,
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
            'body' => Yii::t('app', 'Body'),
            'params' => Yii::t('app', 'Params'),
            'data' => Yii::t('app', 'Data'),
            'at' => Yii::t('app', 'At'),
        ];
    }

    /**
     * @inheritDoc
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert) {
            $this->id = self::generateId();
        }

        $this->normalizeAttributes(true);

        return true;
    }

    /**
     * @return string
     * @throws Exception
     * @throws InvalidConfigException
     */
    public static function generateId()
    {
        $id = Yii::$app->security->generateRandomString(16);

        if (self::find()->andWhere(['id' => $id])->exists()) {
            return self::generateId();
        }

        return $id;
    }

    /**
     * @inheritdoc
     *
     * @return AccountNotificationQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new AccountNotificationQuery(get_called_class());

        return $query->alias("account_notification");
    }

    /**
     * @inheritDoc
     */
    public function normalizeAttributes($save = false)
    {
        if ($save) {
            if (!empty($this->to)) {
                $this->receiver_type = self::TYPE_INDIVIDUAL;
            } elseif ($this->toAccountType) {
                $this->receiver_type = self::TYPE_ACCOUNT_TYPE;
            }

            if ($this->is_internal_url && is_array($this->url)) {
                $this->url = Json::encode($this->url);
            }

            if (is_array($this->title_params)) {
                $this->title_params = Json::encode($this->title_params);
            }

            if (is_array($this->body_params)) {
                $this->body_params = Json::encode($this->body_params);
            }
        } else {
            if ($this->is_internal_url) {
                $this->url = Json::decode($this->url);
            }

            if (is_string($this->title_params)) {
                $this->title_params = Json::decode($this->title_params);
            }

            if (is_string($this->body_params)) {
                $this->body_params = Json::decode($this->body_params);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            if (!empty($this->to)) {
                $receivers = [];

                foreach ($this->to AS $to) {
                    if ($to instanceof Account) {
                        $to = $to->id;
                    }

                    $receivers[] = [$this->id, $to];
                }

                $command = AccountNotificationReceiver::getDb()->createCommand();
                $isInserted = $command->batchInsert(AccountNotificationReceiver::tableName(), ['notification_id', 'account_id'], $receivers)->execute();

                if (!$isInserted) {
                    throw new DbException("Failed to insert receiver to database");
                }
            } elseif (!empty($this->toAccountType)) {
                $receivers = [];

                foreach ($this->toAccountType AS $toAccountType) {
                    $receivers[] = [$this->id, $toAccountType];
                }

                $command = AccountNotificationReceiver::getDb()->createCommand();
                $isInserted = $command->batchInsert(AccountNotificationReceiver::tableName(), ['notification_id', 'account_type'], $receivers)->execute();

                if (!$isInserted) {
                    throw new DbException("Failed to insert receiver to database");
                }
            }
        }
    }

    /**
     * @param null|Account $account
     *
     * @return bool
     *
     * @throws InvalidConfigException
     */
    public function getIsSeen($account = null)
    {
        if (is_null($account)) {
            if (Yii::$app->user->isGuest) {
                throw new InvalidArgumentException("$account argument must be set");
            }

            $account = Yii::$app->user->identity;
        }

        return AccountNotification::find()->seen($account)->exists();
    }

    /**
     * @param null|Account $account
     *
     * @return bool
     *
     * @throws InvalidConfigException
     */
    public function getIsRead($account = null)
    {
        if (is_null($account)) {
            if (Yii::$app->user->isGuest) {
                throw new InvalidArgumentException("$account argument must be set");
            }

            $account = Yii::$app->user->identity;
        }

        return AccountNotification::find()->andWhere(['account_notification.id' => $this->id])->read($account)->exists();
    }

    /**
     * @return string|boolean
     */
    public function getRenderedUrl()
    {
        if (!$this->url) {
            return false;
        }

        if ($this->is_internal_url) {
            return Url::to($this->url, true);
        }

        return $this->url;
    }

    /**
     * @param Account $account
     *
     * @return int
     */
    public function read($account)
    {
        return AccountNotificationReceiver::updateAll(['is_read' => true], [
            'AND',
            ['is_read' => false],
            ['notification_id' => $this->id],
            [
                'OR',
                ['account_id' => $account->id],
                ['account_type' => $account->type],
            ],
        ]);
    }
}
