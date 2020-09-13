<?php  namespace modules\account\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use Yii;

/**
* @author Rifan Firdhaus Widigdo
<rifanfirdhaus@gmail.com>
*
  *
        * @property AccountComment $comment
    */
class AccountCommentAttachment extends \modules\core\db\ActiveRecord
{
/**
* @inheritdoc
*/
public static function tableName()
{
return '{{%account_comment_attachment}}';
}

/**
* @inheritdoc
*/
public function rules()
{
return [
            [['comment_id', 'file', 'uploaded_at'], 'required'],
            [['comment_id', 'uploaded_at'], 'integer'],
            [['file'], 'string'],
            [['comment_id'], 'exist', 'skipOnError' => true, 'targetClass' => AccountComment::className(), 'targetAttribute' => ['comment_id' => 'id']],
        ];
}

/**
* @inheritdoc
*/
public function attributeLabels()
{
return [
    'id' => Yii::t('app', 'ID'),
    'comment_id' => Yii::t('app', 'Comment ID'),
    'file' => Yii::t('app', 'File'),
    'uploaded_at' => Yii::t('app', 'Uploaded At'),
];
}

  /**
  * @return \modules\core\db\ActiveQuery
  */
  public function getComment()
  {
    return $this->hasOne(AccountComment::className(), ['id' => 'comment_id']);
  }
    
  /**
  * @inheritdoc
  * @return \modules\account\models\queries\AccountCommentAttachmentQuery the active query used by this AR class.
  */
  public static function find()
  {
  $query = new \modules\account\models\queries\AccountCommentAttachmentQuery(get_called_class());

  return $query->alias("account_comment_attachment");
  }
}
