<?php namespace modules\calendar\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\web\admin\Controller;
use modules\account\web\admin\View;
use modules\account\widgets\history\HistoryWidget;
use modules\account\widgets\history\HistoryWidgetEvent;
use modules\core\components\HookTrait;
use Yii;
use yii\base\Event;
use yii\helpers\Html;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class AdminHook
{
    use HookTrait;

    protected $historyOptions = [
        'event_member.add' => [
            'icon' => 'i8:paper-plane',
            'iconOptions' => ['class' => 'icon bg-success'],
        ],
        'event_member.delete' => [
            'icon' => 'i8:paper-plane',
            'iconOptions' => ['class' => 'icon bg-warning'],
        ],
    ];

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

            Event::on(HistoryWidget::class, HistoryWidget::EVEMT_RENDER_ITEM, [$this, 'renderHistoryWidgetItem']);
        }
    }

    /**
     * @param View $view
     */
    public function registerMenu($view)
    {
        $view->menu->addItems([
            'main/event' => [
                'label' => Yii::t('app', 'Event'),
                'icon' => 'i8:event',
                'url' => ['/calendar/admin/event/index'],
                'sort' => -1,
                'visible' => Yii::$app->user->can('admin.event.list'),
                'linkOptions' => [
                    'data-lazy-container' => '#main-container',
                    'data-lazy-link' => true,
                ],
            ],
        ]);
    }

    /**
     * @param HistoryWidgetEvent $event
     */
    public function renderHistoryWidgetItem($event)
    {
        /** @var HistoryWidget $widget */
        $widget = $event->sender;
        $model = $event->model;

        if (in_array($model->key, [
            'event.add',
            'event.update',
            'event.delete',
        ])) {
            $event->params['name'] = Html::a([
                'url' => ['/calendar/admin/event/view', 'id' => $model->params['id']],
                'label' => Html::encode($model->params['name']),
                'data-lazy-container' => '#main-container',
                'data-lazy-modal' => 'event-view-modal',
                'data-lazy-modal-size' => 'modal-lg',
                'class' => 'important',
            ]);
        } elseif (in_array($model->key, [
            'event_member.add',
            'event_member.delete',
        ])) {
            $event->params['event_name'] = Html::a([
                'url' => ['/calendar/admin/event/view', 'id' => $model->params['event_id']],
                'label' => Html::encode($model->params['event_name']),
                'data-lazy-container' => '#main-container',
                'data-lazy-modal' => 'event-view-modal',
                'data-lazy-modal-size' => 'modal-lg',
                'class' => 'important',
            ]);
            $event->params['staff_name'] = Html::a([
                'label' => Html::encode($model->params['staff_name']),
                'url' => ['/account/admin/staff/profile', 'id' => $model->params['staff_id']],
                'class' => 'important',
            ]);
        }

        if (isset($this->historyOptions[$model->key])) {
            foreach ($this->historyOptions[$model->key] AS $attribute => $value) {
                $event->{$attribute} = $value;
            }
        }
    }
}
