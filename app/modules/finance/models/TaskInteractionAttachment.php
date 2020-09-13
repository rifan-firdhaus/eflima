<?php  namespace modules\finance\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use Yii;

/**
* @author Rifan Firdhaus Widigdo
<rifanfirdhaus@gmail.com>
*
*/
class TaskInteractionAttachment extends \modules\core\db\ActiveRecord
{
/**
* @inheritdoc
*/
public static function tableName()
{
return '{{%task_interaction_attachment}}';
}

/**
* @inheritdoc
*/
public function rules()
{
return [
            [['interaction_id', 'file'], 'required'],
            [['interaction_id'], 'integer'],
            [['file'], 'string'],
        ];
}

/**
* @inheritdoc
*/
public function attributeLabels()
{
return [
    'id' => Yii::t('app', 'ID'),
    'interaction_id' => Yii::t('app', 'Interaction ID'),
    'file' => Yii::t('app', 'File'),
];
}
    
  /**
  * @inheritdoc
  * @return \modules\finance\models\queries\TaskInteractionAttachmentQuery the active query used by this AR class.
  */
  public static function find()
  {
  $query = new \modules\finance\models\queries\TaskInteractionAttachmentQuery(get_called_class());

  return $query->alias("task_interaction_attachment");
  }
}
