<?php namespace modules\file_manager;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use app\modules\account\web\admin\Application as AdminApplication;
use modules\account\web\admin\Controller;
use modules\account\web\admin\View;
use modules\core\base\Module;
use modules\file_manager\helpers\ImageVersion;
use Yii;
use yii\base\Event;
use yii\imagine\Image;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class FileManager extends Module
{
    public function init()
    {
        parent::init();

        if (Yii::$app instanceof AdminApplication) {
            Event::on(Controller::class, Controller::EVENT_BEFORE_ACTION, [$this, 'adminBeforeAction']);
        }

        ImageVersion::instance()->register('thumbnail', function ($original, $derivative) {
            return Image::thumbnail($original, 175, 175)->save($derivative);
        });
    }

    /**
     * @param Event $event
     */
    public function adminBeforeAction($event)
    {
        /** @var Controller $controller */
        $controller = $event->sender;

        if (!Yii::$app->user->isGuest) {
            $this->registerAdminMenu($controller->view);
        }
    }

    /**
     * @param View $view
     */
    protected function registerAdminMenu($view)
    {
        $view->menu->addItems([
            //            'main/file_manager' => [
            //                'label' => Yii::t('app', 'File Manager'),
            //                'icon' => 'i8:folder',
            //                'url' => ['/file_manager/admin/file/index'],
            //                'sort' => 1,
            //                'linkOptions' => [
            //                    'data-lazy-link' => true,
            //                    'data-lazy-container' => '#main-container',
            //                ],
            //            ],
        ]);
    }
}