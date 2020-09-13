<?php namespace modules\quick_access\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\web\admin\Controller;
use modules\account\web\admin\View;
use modules\core\components\HookTrait;
use modules\quick_access\assets\admin\QuickSearchAsset;
use Yii;
use yii\base\Event;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class AdminHook
{
    use HookTrait;

    public function __construct()
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

            $controller->view->on(View::EVENT_BEGIN_PAGE, [$this, 'beginPage']);
        }
    }

    /**
     * @param Event $event
     */
    public function beginPage($event)
    {
        /** @var View $view */
        $view = $event->sender;

        // render search view
        $view->addBlock('account/layouts/admin/main:begin', $view->render('@modules/quick_access/views/admin/default/components/search-bar'));

        // Register task asset
        if ($view->getRequestedViewFile() === Yii::getAlias('@modules/account/views/layouts/admin/main.php')) {
            QuickSearchAsset::register($view);
        }
    }

    /**
     * @param View $view
     */
    public function registerMenu($view)
    {
        $view->menu->addItems([
            'sidenav/bottom/quick_search' => [
                'label' => Yii::t('app', 'Search'),
                'icon' => 'i8:search',
                'sort' => 1,
                'linkOptions' => [
                    'id' => 'quick-search-button',
                ],
            ],
            'sidenav/bottom/quick_add' => [
                'label' => Yii::t('app', 'Quick Add'),
                'icon' => 'i8:plus',
                'sort' => 1,
                'url' => ['/quick_access/admin/default/quick-add'],
                'linkOptions' => [
                    'data-lazy-container' => '#side-panel',
                    'data-lazy-link' => true,
                ],
            ],
            'quick_add/staff' => [
                'label' => Yii::t('app', 'Staff'),
                'icon' => 'i8:account',
                'sort' => 1,
            ],
            'sidenav/bottom/bookmark' => [
                'label' => Yii::t('app', 'Bookmark'),
                'icon' => 'i8:push-pin',
                'sort' => 1,
            ],
            'quick_access/quick_add/staff' => [
                'label' => Yii::t('app', 'Staff'),
                'url' => ['/account/admin/staff/add'],
                'icon' => 'i8:account',
                'linkOptions' => [
                    'data-lazy-modal' => 'staff-form-modal',
                    'data-lazy-container' => '#main-container',
                    'data-lazy-link' => true,
                    'class' => 'nav-link side-panel-close',
                ],
            ],
        ]);
    }
}