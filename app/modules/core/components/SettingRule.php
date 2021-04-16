<?php namespace modules\core\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use yii\rbac\Rule;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class SettingRule extends Rule
{
    public $name = 'settingRule';

    /**
     * @inheritDoc
     */
    public function execute($user, $item, $params)
    {
        // TODO: Implement execute() method.
    }
}
