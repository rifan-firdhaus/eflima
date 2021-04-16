<?php namespace modules\project\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use app\modules\account\web\admin\Application as AdminApplication;
use modules\account\models\Staff;
use modules\core\components\HookTrait;
use modules\core\components\SettingRenderer;
use modules\crm\models\Customer;
use modules\finance\models\Proposal;
use modules\project\models\Project;
use modules\project\models\ProjectMember;
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

        Event::on(Staff::class, Staff::EVENT_BEFORE_DELETE, [ProjectMember::class, 'deleteAllMemberRelatedToDeletedStaff']);
        Event::on(Customer::class, Customer::EVENT_BEFORE_DELETE, [Project::class, 'deleteAllProjectRelatedToDeletedCustomer']);
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
