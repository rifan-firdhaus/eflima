<?php

namespace modules\crm\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\core\helpers\Common;
use modules\core\models\traits\VisibilityModel;
use modules\crm\models\queries\CustomerGroupQuery;
use Yii;
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
 * @property int        $created_at [int(11) unsigned]
 * @property int        $updated_at [int(11) unsigned]
 */
class CustomerGroup extends ActiveRecord
{
    use VisibilityModel;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_group}}';
    }

    /**
     * @inheritdoc
     *
     * @return CustomerGroupQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new CustomerGroupQuery(get_called_class());

        return $query->alias("customer_group");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'unique'],
            [['name', 'description'], 'string'],
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

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (empty($this->color_label)) {
            $this->color_label = Common::randomHexColor();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

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
            'is_enabled' => Yii::t('app', 'Is Enabled'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getCustomers()
    {
        return $this->hasMany(Customer::class, ['group_id' => 'id']);
    }
}
