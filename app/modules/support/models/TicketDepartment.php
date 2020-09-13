<?php

namespace modules\support\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\core\models\traits\VisibilityModel;
use modules\support\models\queries\TicketDepartmentQuery;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Ticket[] $tickets
 *
 * @property int      $id         [int(10) unsigned]
 * @property string   $name
 * @property bool     $is_enabled [tinyint(1)]
 * @property string   $description
 * @property int      $created_at [int(11) unsigned]
 * @property int      $updated_at [int(11) unsigned]
 */
class TicketDepartment extends ActiveRecord
{
    use VisibilityModel;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ticket_department}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name', 'description'], 'string'],
            [['is_enabled'], 'boolean'],
            [['is_enabled'], 'default', 'value' => 1],
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
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'is_enabled' => Yii::t('app', 'Enabled'),
            'description' => Yii::t('app', 'Description'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getTickets()
    {
        return $this->hasMany(Ticket::class, ['status_id' => 'id'])->alias('tickets_of_status');
    }

    /**
     * @inheritdoc
     *
     * @return TicketDepartmentQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new TicketDepartmentQuery(get_called_class());

        return $query->alias("ticket_department");
    }
}
