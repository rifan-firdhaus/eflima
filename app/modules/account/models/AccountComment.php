<?php namespace modules\account\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\components\CommentRelation;
use modules\account\models\queries\AccountCommentQuery;
use modules\account\models\queries\AccountQuery;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Account              $account
 * @property bool                 $isMe
 * @property null|mixed           $relatedModel
 * @property CommentRelation|null $relatedObject
 *
 * @property int                  $id         [int(10) unsigned]
 * @property string               $model
 * @property string               $model_id
 * @property int                  $account_id [int(11) unsigned]
 * @property int                  $parent_id  [int(11) unsigned]
 * @property string               $comment
 * @property int                  $posted_at  [int(11) unsigned]
 */
class AccountComment extends ActiveRecord
{
    public $uploaded_attachments = [];
    protected $_relatedModel;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account_comment}}';
    }

    /**
     * @inheritdoc
     *
     * @return AccountCommentQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new AccountCommentQuery(get_called_class());

        return $query->alias("account_comment");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['comment'],
                'required',
                'on' => ['admin/add', 'admin/update'],
            ],
            [
                'model_id',
                'required',
                'when' => function ($model) {
                    return !empty($model->model);
                },
            ],
            [
                'model',
                'in',
                'range' => array_keys(CommentRelation::map()),
            ],
            [
                'model_id',
                'validateRelatedModel',
            ],
        ];
    }

    /**
     * @throws InvalidConfigException
     */
    public function validateRelatedModel()
    {
        if ($this->hasErrors() || empty($this->model)) {
            return;
        }

        $relation = CommentRelation::get($this->model);

        $relation->validate($this->getRelatedModel(), $this);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['timestamp'] = [
            'class' => TimestampBehavior::class,
            'createdAtAttribute' => 'posted_at',
            'updatedAtAttribute' => false,
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
            'model' => Yii::t('app', 'Model'),
            'model_id' => Yii::t('app', 'Model ID'),
            'account_id' => Yii::t('app', 'Account ID'),
            'parent_id' => Yii::t('app', 'Parent ID'),
            'comment' => Yii::t('app', 'Comment'),
            'posted_at' => Yii::t('app', 'Posted At'),
        ];
    }

    /**
     * @return ActiveQuery|AccountQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::class, ['id' => 'account_id'])->alias('account_of_comment');
    }


    /**
     * @return CommentRelation|null
     *
     * @throws InvalidConfigException
     */
    public function getRelatedObject()
    {
        if (empty($this->model)) {
            return null;
        }

        return CommentRelation::get($this->model);
    }

    /**
     * @return mixed|null
     * @throws InvalidConfigException
     */
    public function getRelatedModel()
    {
        if (empty($this->model)) {
            return null;
        }

        if (!isset($this->_relatedModel)) {
            $this->_relatedModel = $this->getRelatedObject()->getModel($this->model_id);
        }

        return $this->_relatedModel;
    }

    /**
     * @param null|int|string $accountId
     *
     * @return bool
     */
    public function getIsMe($accountId = null)
    {
        if (!$accountId) {
            $accountId = Yii::$app->user->id;
        }

        return $accountId == $this->account_id;
    }
}
