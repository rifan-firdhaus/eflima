<?php namespace modules\ui\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\web\admin\Controller;
use modules\account\web\admin\View;
use modules\core\components\HookTrait;
use Yii;
use yii\base\Event;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class AdminHook
{
    use HookTrait;

    protected function __construct()
    {
        Event::on(Controller::class, Controller::EVENT_BEFORE_ACTION, [$this, 'beforeAction']);
    }


    /**
     * @param Event $event
     */
    public function beforeAction($event)
    {
        /** @var Controller $controller */
        $controller = $event->sender;

        if (!Yii::$app->user->isGuest) {
            $this->registerMenu($controller->view);
        }
    }

    /**
     * @param View $view
     */
    public function registerMenu($view)
    {
    }
}