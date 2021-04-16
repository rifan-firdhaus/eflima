<?php namespace modules\crm\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\core\models\traits\VisibilityModel;
use modules\crm\models\queries\LeadSourceQuery;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Customer[] $customers
 *
 * @property int        $id         [int(10) unsigned]
 * @property string     $name
 * @property string     $description
 * @property string     $color_label
 * @property bool       $is_enabled [tinyint(1)]
 * @property int        $creator_id [int(11) unsigned]
 * @property int        $created_at [int(11) unsigned]
 * @property int        $updater_id [int(11) unsigned]
 * @property int        $updated_at [int(11) unsigned]
 */
class LeadSource extends ActiveRecord
{
    use VisibilityModel;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%lead_source}}';
    }

    /**
     * @inheritdoc
     * @return LeadSourceQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new LeadSourceQuery(get_called_class());

        return $query->alias("lead_source");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['is_enabled'], 'boolean'],
            [['name', 'description', 'color_label'], 'string'],
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

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios['install'] = $scenarios['default'];
        $scenarios['admin/add'] = $scenarios['default'];
        $scenarios['admin/update'] = $scenarios['admin/add'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'is_enabled' => Yii::t('app', 'Enabled'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getCustomers()
    {
        return $this->hasMany(Customer::class, ['lead_source_id' => 'id']);
    }
}
