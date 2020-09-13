<?php namespace modules\content\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\web\admin\Controller;
use modules\account\web\admin\Controller as AdminController;
use Yii;
use yii\base\Component;
use yii\base\Event;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property string $label
 * @property string $id
 */
class PostType extends Component
{
    public $menu;
    public $icon;
    public $taxonomies = [];
    protected $id;
    protected $_label;

    public function init()
    {
        parent::init();

        if ($this->menu && Yii::$app->params['isAdmin']) {
            Event::on(AdminController::class, AdminController::EVENT_BEFORE_ACTION, function ($event) {
                /**
                 * @var Event      $event
                 * @var Controller $controller
                 */

                $controller = $event->sender;

                $controller->view->menu->addItem($this->menu, [
                    'label' => $this->label,
                    'icon' => $this->icon,
                    'url' => ['/content/admin/post/index', 'type' => $this->id],
                    'linkOptions' => [
                        'data-lazy-container' => '#main-container',
                        'data-lazy-link' => true,
                    ],
                ]);
            });
        }
    }

    public function getLabel()
    {
        return $this->_label;
    }

    public function setLabel($label)
    {
        $this->_label = $label;
    }
}