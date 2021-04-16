<?php namespace modules\calendar\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use app\modules\account\web\admin\Application as AdminApplication;
use modules\account\models\Staff;
use modules\calendar\models\EventMember;
use modules\core\components\HookTrait;
use Yii;
use yii\base\Event;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Hook
{
    use HookTrait;

    protected function __construct()
    {
        if (Yii::$app instanceof AdminApplication) {
            AdminHook::instance();
        }

        Event::on(Staff::class, Staff::EVENT_AFTER_DELETE, [EventMember::class, 'deleteAllMemberRelatedToDeletedStaff']);
    }
}
