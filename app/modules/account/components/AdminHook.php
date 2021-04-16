<?php namespace modules\account\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\StaffAccount;
use modules\account\web\admin\Controller;
use modules\account\web\admin\View;
use modules\core\components\HookTrait;
use modules\core\controllers\admin\SettingController;
use modules\ui\widgets\Icon;
use modules\ui\widgets\lazy\Lazy;
use Yii;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\bootstrap4\Modal;
use yii\filters\AccessControl;
use yii\helpers\Html;
use yii\web\Response;
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

        Event::on(SettingController::class, SettingController::EVENT_INIT, [$this, 'registerSettingPermission']);

        Yii::$app->response->on(Response::EVENT_BEFORE_SEND, [$this, 'beforeSendResponse']);
    }

    public function beforeSendResponse()
    {
        $req = Yii::$app->request;
        $res = Yii::$app->response;

        if ($req->isGet && $res->statusCode == 200 && (!$req->isAjax || ($res->data instanceof Lazy && $req->headers->get('X-Page','true') === 'true'))) {
            Yii::$app->user->setReturnUrl(Yii::$app->request->absoluteUrl);
        }
    }

    /**
     * @param Event $event
     *
     * @throws InvalidConfigException
     */
    public function registerSettingPermission($event)
    {
        /**
         * @var SettingController $settingController
         * @var AccessControl     $accessBehaviors
         */
        $settingController = $event->sender;
        $accessBehaviors = $settingController->getBehavior('access');

        $accessBehaviors->rules[] = Yii::createObject(array_merge([
            'allow' => true,
            'actions' => ['index'],
            'verbs' => ['GET', 'POST'],
            'roles' => ['admin.setting.account'],
            'matchCallback' => function () {
                return Yii::$app->request->get('section') === 'account';
            },
        ], $accessBehaviors->ruleConfig));

        $accessBehaviors->rules[] = Yii::createObject(array_merge([
            'allow' => true,
            'actions' => ['index'],
            'verbs' => ['GET', 'POST'],
            'roles' => ['admin.setting.company'],
            'matchCallback' => function () {
                return Yii::$app->request->get('section') === 'company';
            },
        ], $accessBehaviors->ruleConfig));
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
                'visible' => Yii::$app->user->can('admin.staff.list'),
                'linkOptions' => [
                    'data-lazy-container' => '#main-container',
                    'data-lazy-link' => true,
                ],
            ],
            'main/admin/acl' => [
                'label' => Yii::t('app', 'Role & Permission'),
                'icon' => 'i8:user-shield',
                'url' => ['/account/admin/role/index'],
                'visible' => Yii::$app->user->can('admin.staff.role.list'),
                'linkOptions' => [
                    'data-lazy-container' => '#main-container',
                    'data-lazy-link' => true,
                ],
            ],
            'setting/account' => [
                'label' => Yii::t('app', 'Account'),
                'icon' => 'i8:user-menu-male',
                'url' => ['/core/admin/setting/index', 'section' => 'account'],
                'visible' => Yii::$app->user->can('admin.setting.account'),
                'linkOptions' => [
                    'data-lazy-container' => '#main-container',
                    'data-lazy-link' => true,
                ],
            ],
            'setting/company' => [
                'label' => Yii::t('app', 'Company'),
                'icon' => 'i8:smart-card',
                'url' => ['/core/admin/setting/index', 'section' => 'company'],
                'visible' => Yii::$app->user->can('admin.setting.company'),
                'linkOptions' => [
                    'data-lazy-container' => '#main-container',
                    'data-lazy-link' => true,
                ],
            ],
        ]);
    }
}
