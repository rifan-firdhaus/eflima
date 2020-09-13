<?php namespace modules\content;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use app\modules\account\web\admin\Application as AdminApplication;
use modules\account\web\admin\Controller as AdminController;
use modules\account\web\admin\View as AdminView;
use modules\content\components\PagePostType;
use modules\content\components\PostType;
use modules\core\base\Module;
use Yii;
use yii\base\Event;
use yii\base\InvalidArgumentException;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Content extends Module
{
    public $menu = 'main/content/page';

    /** @var array|PostType[] */
    protected $_postTypes = [];

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        if (Yii::$app instanceof AdminApplication) {
            Event::on(AdminController::class, AdminController::EVENT_BEFORE_ACTION, [$this, 'adminBeforeAction']);
        }

        $this->registerPostType(Yii::createObject(PagePostType::class));
    }

    /**
     * @param PostType $postType
     */
    public function registerPostType($postType)
    {
        $this->_postTypes[$postType->id] = $postType;
    }

    /**
     * @param Event $event
     */
    public function adminBeforeAction($event)
    {
        /** @var AdminController $controller */
        $controller = $event->sender;

        if (!Yii::$app->user->isGuest) {
            $this->registerAdminMenu($controller->view);
        }
    }

    /**
     * @param AdminView $view
     */
    public function registerAdminMenu($view)
    {
        $view->menu->addItems([
            'main/content' => [
                'label' => Yii::t('app', 'Content'),
                'icon' => 'i8:paper',
                'sort' => 2,
                'options' => [
                    'class' => 'heading',
                ],
            ],
        ]);
    }

    /**
     * @param string $id
     *
     * @return PostType
     */
    public function getPostType($id)
    {
        if (!isset($this->_postTypes[$id])) {
            throw new InvalidArgumentException("Post type {$id} is not registered");
        }

        return $this->_postTypes[$id];
    }
}