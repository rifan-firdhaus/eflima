<?php namespace modules\account\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\StaffAccount;
use modules\account\web\admin\Controller;
use modules\account\web\admin\View;
use modules\core\components\HookTrait;
use yii\helpers\Html;
use modules\ui\widgets\Icon;
use Yii;
use yii\base\Event;
use yii\bootstrap4\Modal;
use yii\widgets\LinkPager;

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

        $controller->getView();

        Yii::$container->set(LinkPager::class, [
            'options' => [
                'class' => 'pagination justify-content-center m-0',
            ],
            'linkContainerOptions' => [
                'class' => 'page-item',
            ],
            'linkOptions' => [
                'class' => 'page-link',
            ],
            'disabledListItemSubTagOptions' => [
                'tag' => 'a',
                'class' => 'page-link',
            ],
        ]);

        Yii::$container->set(Modal::class, [
            'options' => [
                'tabindex' => false,
            ],
            'closeButton' => [
                'label' => Icon::show('i8:multiply'),
                'class' => 'btn btn-link pr-0 btn-icon',
            ],
        ]);

        if (!Yii::$app->user->isGuest) {
            $this->registerMenu($controller->view);
        }
    }


    /**
     * @param View $view
     */
    public function registerMenu($view)
    {
        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;

        $notificationCount = $account->notificationCount;
        $notificationBadge = Html::tag('div', $notificationCount, [
            'class' => 'account-notification-badge rounded-circle badge badge-danger',
            'data-count' => $notificationCount,
        ]);

        $view->menu->addItems([
            'sidenav/top/sidebar_toggler' => [
                'icon' => 'i8:menu',
                'label' => Yii::t('app', 'Hide/Show Sidebar'),
                'sort' => -10000,
                'linkOptions' => [
                    'class' => 'sidebar-toggler nav-link',
                    'id' => 'sidebar-toggler',
                ],
            ],
            'sidenav/top/notification' => [
                'icon' => 'i8:notification',
                'label' => Yii::t('app', 'Notifications'),
                'content' => $notificationBadge,
                'url' => ['/account/admin/notification/index'],
                'linkOptions' => [
                    'data-lazy-container' => '#notification-panel',
                    'data-lazy-link' => true,
                ],
            ],
            'sidenav/top/chat' => [
                'icon' => 'i8:chat',
                'label' => Yii::t('app', 'Messages'),
            ],
            'sidenav/bottom/logout' => [
                'icon' => 'i8:sign-out',
                'label' => Yii::t('app', 'Sign Out'),
                'url' => ['/account/admin/staff/logout'],
                'sort' => 120,
            ],
            'main/dashboard' => [
                'label' => Yii::t('app', 'Dashboard'),
                'icon' => 'i8:home',
                'url' => ['/account/admin/staff/dashboard'],
                'sort' => -10000,
                'linkOptions' => [
                    'data-lazy-container' => '#main-container',
                    'data-lazy-link' => true,
                ],
            ],
            'main/admin' => [
                'label' => Yii::t('app', 'Staff'),
                'icon' => 'i8:contacts',
                'options' => [
                    'class' => 'heading',
                ],
            ],
            'main/admin/admin' => [
                'label' => Yii::t('app', 'Staffs'),
                'icon' => 'i8:account',
                'url' => ['/account/admin/staff/index'],
                'linkOptions' => [
                    'data-lazy-container' => '#main-container',
                    'data-lazy-link' => true,
                ],
            ],
            'main/admin/acl' => [
                'label' => Yii::t('app', 'Role & Permission'),
                'icon' => 'i8:user-shield',
                'url' => ['/account/admin/role/index'],
                'linkOptions' => [
                    'data-lazy-container' => '#main-container',
                    'data-lazy-link' => true,
                ],
            ],
            'setting/account' => [
                'label' => Yii::t('app', 'Account'),
                'icon' => 'i8:user-menu-male',
                'url' => ['/core/admin/setting/index', 'section' => 'account'],
                'linkOptions' => [
                    'data-lazy-container' => '#main-container',
                    'data-lazy-link' => true,
                ],
            ],
            'setting/company' => [
                'label' => Yii::t('app', 'Company'),
                'icon' => 'i8:smart-card',
                'url' => ['/core/admin/setting/index', 'section' => 'company'],
                'linkOptions' => [
                    'data-lazy-container' => '#main-container',
                    'data-lazy-link' => true,
                ],
            ],
        ]);
    }
}