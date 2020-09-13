<?php namespace modules\support\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\core\db\ActiveRecord;
use modules\core\models\traits\VisibilityModel;
use modules\support\models\queries\TicketPredefinedReplyQuery;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property int    $id         [int(10) unsigned]
 * @property string $title
 * @property string $content
 * @property bool   $is_enabled [tinyint(1)]
 * @property int    $created_at [int(11) unsigned]
 * @property int    $updated_at [int(11) unsigned]
 */
class TicketPredefinedReply extends ActiveRecord
{
    use VisibilityModel;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ticket_predefined_reply}}';
    }

    /**
     * @inheritdoc
     *
     * @return TicketPredefinedReplyQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new TicketPredefinedReplyQuery(get_called_class());

        return $query->alias("ticket_predefined_reply");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'content'], 'required','on' => ['admin/add','admin/update']],
            [['title', 'content'], 'string'],
            [['is_enabled'], 'boolean'],
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
            'title' => Yii::t('app', 'Title'),
            'content' => Yii::t('app', 'Content'),
            'is_enabled' => Yii::t('app', 'Enabled'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
