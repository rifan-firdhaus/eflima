<?php namespace modules\account\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Exception;
use modules\account\models\History as HistoryModel;
use modules\core\components\Setting;
use Throwable;
use Yii;
use yii\base\BaseObject;
use yii\db\Exception as DbException;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property bool $isEnabled
 */
class History extends BaseObject
{
    protected $_isEnabled;

    /**
     * @param string $event
     * @param array  $attributes
     * @param array  $relationship
     *
     * @return bool
     * @throws Throwable
     * @throws DbException
     */
    public function save($event, $attributes)
    {
        if (!$this->isEnabled) {
            return true;
        }

        $model = new HistoryModel([
            'key' => $event,
            'at' => microtime(true),
            'executor_id' => Yii::$app->getUser()->getId(),
            'model' => isset($attributes['model']) ? $attributes['model'] : null,
            'model_id' => isset($attributes['model_id']) ? $attributes['model_id'] : null,
        ]);

        $model->setAttributes($attributes);

        $transaction = Yii::$app->db->beginTransaction();

        try {
            if (!$model->save()) {
                $transaction->rollBack();

                return false;
            }
        } catch (Exception $e) {
            $transaction->rollBack();

            throw $e;
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        $transaction->commit();

        return true;
    }

    public function getIsEnabled()
    {
        if (!isset($this->_isEnabled)) {
            /** @var Setting $setting */
            $setting = Yii::$app->setting;
            $this->_isEnabled = (int) (bool) $setting->get('is_history_log_enabled', true);
        }

        return $this->_isEnabled;
    }
}
