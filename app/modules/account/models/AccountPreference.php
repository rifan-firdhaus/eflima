<?php namespace modules\account\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\queries\AccountQuery;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Account $account
 *
 * @property int     $id         [int(10) unsigned]
 * @property int     $account_id [int(10) unsigned]
 * @property string  $key
 * @property string  $value
 * @property int     $updated_at [int(10) unsigned]
 */
class AccountPreference extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return "{{%account_preference}}";
    }

    /**
     * @param int    $accountId
     * @param string $key
     *
     * @return ActiveQuery
     * @throws InvalidConfigException
     */
    public static function findByKey($accountId, $key)
    {
        return self::find()->andWhere(['account_id' => $accountId, 'key' => $key]);
    }

    /**
     * @return ActiveQuery|AccountQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::class, ['id' => 'account_id'])->alias('preference_of_account')->inverseOf('preferences');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['account_id'], 'required'],
            [['account_id', 'updated_at'], 'integer'],
            [['value'], 'string'],
            [['key'], 'string', 'max' => 255],
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
            'createdAtAttribute' => false,
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
            'account_id' => Yii::t('app', 'Account'),
            'key' => Yii::t('app', 'Key'),
            'value' => Yii::t('app', 'Value'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}