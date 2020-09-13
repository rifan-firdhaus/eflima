<?php namespace modules\note\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\web\admin\Controller;
use modules\account\web\admin\View;
use modules\core\components\HookTrait;
use modules\note\assets\admin\NoteAsset;
use Yii;
use yii\base\Event;
use yii\helpers\Json;

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

            $controller->view->on(View::EVENT_BEGIN_PAGE, [$this, 'beginPage']);
        }
    }

    /**
     * @param View $view
     */
    public function registerMenu($view)
    {
        $view->menu->addItems([
            'sidenav/top/note' => [
                'label' => Yii::t('app', 'Note'),
                'icon' => 'i8:note',
                'url' => ['/note/admin/note/index'],
                'linkOptions' => [
                    'id' => 'note-button',
                ],
            ],
        ]);
    }

    /**
     * @param Event $event
     */
    public function beginPage($event)
    {
        /** @var View $view */
        $view = $event->sender;


        if ($view->getRequestedViewFile() === Yii::getAlias('@modules/account/views/layouts/admin/main.php')) {
            $view->addBlock('account/layouts/admin/main:begin', $view->render('@modules/note/views/admin/note/components/container', [
                'configurations' => [
                    'id' => "global-note-container",
                ],
            ]));

            NoteAsset::register($view);

            $options = Json::encode([
                'container' => "#global-note-container-{$view->uniqueId}"
            ]);

            $view->registerJs("window.note = new Note({$options})");
        }
    }
}