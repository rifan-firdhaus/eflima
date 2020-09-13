<?php namespace modules\core\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\web\admin\Controller;
use modules\account\web\admin\View;
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
    protected function registerMenu($view)
    {
        $view->menu->addItems([
            'sidenav/bottom/setting' => [
                'label' => Yii::t('app', 'Settings'),
                'sort' => 101,
                'icon' => 'i8:settings',
                'url' => ['/core/admin/setting/menu'],
                'linkOptions' => [
                    'data-lazy-container' => '#side-panel',
                    'data-lazy-link' => true,
                ],
            ],
            'setting' => [
                'label' => Yii::t('app', 'Settings'),
                'icon' => 'i8:settings',
                'url' => ['/core/admin/setting/menu'],
                'linkOptions' => [
                    'data-lazy-container' => '#side-panel',
                    'data-lazy-link' => true,
                ],
            ],
            'setting/general' => [
                'label' => Yii::t('app', 'General'),
                'url' => ['/core/admin/setting/index', 'section' => 'general'],
                'icon' => 'i8:activity-feed',
                'linkOptions' => [
                    'data-lazy-container' => '#main-container',
                    'data-lazy-link' => true,
                ],
            ],
            'setting/integration' => [
                'label' => Yii::t('app', 'Third Party Integration'),
                'url' => ['/core/admin/setting/index', 'section' => 'pusher'],
                'icon' => 'i8:connect',
                'sort' => 99999999,
                'linkOptions' => [
                    'data-lazy-container' => '#main-container',
                    'data-lazy-link' => true,
                ],
            ],
        ]);
    }
}