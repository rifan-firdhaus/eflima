<?php namespace modules\core\db;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\models\Setting;
use Throwable;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
trait MigrationSettingInstaller
{
    /**
     * @return bool
     *
     * @throws Throwable
     * @throws InvalidConfigException
     * @throws StaleObjectException
     */
    protected function unregisterSettings()
    {
        foreach ($this->settings() AS $setting) {
            $time = $this->beginCommand("Register \"{$setting['id']}\" setting");

            $model = Setting::find()->andWhere(['id' => $setting['id']])->one();

            if ($model && !$model->delete()) {
                return false;
            }

            $this->endCommand($time);
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function registerSettings()
    {
        foreach ($this->settings() AS $setting) {
            $time = $this->beginCommand("Register \"{$setting['id']}\" setting");

            $model = new Setting($setting);

            if (!$model->save()) {
                return false;
            }

            $this->endCommand($time);
        }

        return true;
    }
}