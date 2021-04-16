<?php namespace modules\crm\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\core\db\ActiveRecord;
use modules\core\models\traits\VisibilityModel;
use modules\crm\models\queries\LeadFollowUpTypeQuery;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property int    $id         [int(10) unsigned]
 * @property string $label
 * @property string $description
 * @property bool   $is_enabled [tinyint(1)]
 * @property int    $creator_id [int(11) unsigned]
 * @property int    $created_at [int(11) unsigned]
 * @property int    $updater_id [int(11) unsigned]
 * @property int    $updated_at [int(11) unsigned]
 */
class LeadFollowUpType extends ActiveRecord
{
    use VisibilityModel;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%lead_follow_up_type}}';
    }

    /**
     * @inheritdoc
     *
     * @return LeadFollowUpTypeQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new LeadFollowUpTypeQuery(get_called_class());

        return $query->alias("lead_follow_up_type");
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['label'],
                'required',
                'on' => ['admin/add', 'admin/update'],
            ],
            [
                ['label'],
                'unique',
            ],
            [
                ['description'],
                'safe',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'label' => Yii::t('app', 'Label'),
            'description' => Yii::t('app', 'Description'),
            'is_enabled' => Yii::t('app', 'Is Enabled'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
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

        $behaviors['blamable'] = [
            'class' => BlameableBehavior::class,
            'createdByAttribute' => 'creator_id',
            'updatedByAttribute' => 'updater_id',
        ];

        return $behaviors;
    }
}
