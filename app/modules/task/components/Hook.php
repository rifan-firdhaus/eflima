<?php namespace modules\task\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use app\modules\account\web\admin\Application as AdminApplication;
use modules\core\components\HookTrait;
use modules\core\components\SettingRenderer;
use Yii;
use yii\base\Event;
use yii\base\InvalidConfigException;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Hook
{
    use HookTrait;

    protected function __construct()
    {
        Event::on(SettingRenderer::class, SettingRenderer::EVENT_INIT, [$this, 'registerSetting']);

        if (Yii::$app instanceof AdminApplication) {
            AdminHook::instance();
        }
    }

    /**
     * @param Event $event
     *
     * @throws InvalidConfigException
     */
    public function registerSetting($event)
    {
        /** @var SettingRenderer $renderer */
        $renderer = $event->sender;

        $renderer->addObject(SettingObject::class);
    }
}