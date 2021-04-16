<?php namespace modules\crm\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use Exception;
use modules\core\components\Setting;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\core\models\traits\VisibilityModel;
use modules\crm\models\queries\LeadQuery;
use modules\crm\models\queries\LeadStatusQuery;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception as DbException;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Lead[] $leads
 *
 * @property int    $id         [int(10) unsigned]
 * @property string $label
 * @property string $description
 * @property string $color_label
 * @property bool   $is_enabled [tinyint(1)]
 * @property int    $order      [smallint(3) unsigned]
 * @property int    $creator_id [int(11) unsigned]
 * @property int    $created_at [int(11) unsigned]
 * @property int    $updater_id [int(11) unsigned]
 * @property int    $updated_at [int(11) unsigned]
 */
class LeadStatus extends ActiveRecord
{
    use VisibilityModel;

    public $is_default_status;
    public $is_converted_status;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%lead_status}}';
    }

    /**
     * @inheritdoc
     * @return LeadStatusQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new LeadStatusQuery(get_called_class());

        return $query->alias("lead_status");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['label'], 'required', 'on' => ['admin/add', 'admin/update']],
            [['is_enabled', 'is_converted_status', 'is_default_status'], 'boolean'],
            [['label', 'description', 'color_label'], 'string'],
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

        return $scenarios;
    }

    /**
     * @inheritDoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        /** @var Setting $setting */
        $setting = Yii::$app->setting;

        if ($this->is_default_status && !$setting->set('lead/default_status', $this->id)) {
            throw new DbException("Failed to set status as default");
        }

        if ($this->is_converted_status && !$setting->set('lead/converted_status', $this->id)) {
            throw new DbException("Failed to set status as converted status");
        }
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
            'is_enabled' => Yii::t('app', 'Enabled'),
            'is_default_status' => Yii::t('app', 'Set as Default Status'),
            'is_converted_status' => Yii::t('app', 'Set as Converted Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return ActiveQuery|LeadQuery
     */
    public function getLeads()
    {
        return $this->hasMany(Lead::class, ['status_id' => 'id'])->alias('leads_of_status');
    }

    /**
     * @param array $sort
     *
     * @return bool
     *
     * @throws Throwable
     * @throws InvalidConfigException
     */
    public static function sort($sort = [])
    {
        $models = self::find()->indexBy('id')->all();

        $transaction = self::getDb()->beginTransaction();

        try {
            foreach ($sort AS $order => $statusId) {
                if (!isset($models[$statusId])) {
                    continue;
                }

                $model = $models[$statusId];

                $model->order = $order;

                if (!$model->save(false)) {
                    $transaction->rollBack();

                    return false;
                }
            }

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();

            throw $e;
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }


    /**
     * @param $sort
     *
     * @return bool
     *
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function sortTask($sort)
    {
        $models = Lead::find()->andWhere(['status_id' => $this->id, 'id' => $sort])->indexBy('id')->all();

        $transaction = Lead::getDb()->beginTransaction();

        try {
            foreach ($sort AS $order => $taskId) {
                if (!isset($models[$taskId])) {
                    continue;
                }

                $model = $models[$taskId];

                $model->order = $order;

                if (!$model->save(false)) {
                    $transaction->rollBack();

                    return false;
                }
            }

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();

            throw $e;
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }


    /**
     * @param string|int $leadId
     * @param string|int $statusId
     * @param array      $sort
     *
     * @return bool
     *
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function moveTask($leadId, $statusId, $sort = [])
    {
        $lead = Lead::find()->andWhere(['id' => $leadId])->one();

        if (!$lead) {
            return false;
        }

        $status = LeadStatus::find()->andWhere(['id' => $statusId])->one();

        if (!$status) {
            return false;
        }

        $lead->status_id = $status->id;

        if (!$lead->save(false)) {

            return false;
        }

        if (!$status->sortTask($sort)) {
            return false;
        }

        return true;
    }

}
