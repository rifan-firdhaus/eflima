<?php namespace modules\support\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Exception;
use modules\account\web\admin\Controller;
use modules\account\web\admin\View;
use modules\core\components\HookTrait;
use modules\core\web\ViewBlockEvent;
use modules\crm\models\Customer;
use modules\crm\models\CustomerContact;
use modules\support\models\forms\ticket\TicketSearch;
use modules\support\models\Ticket;
use modules\task\models\forms\task\TaskSearch;
use modules\task\models\query\TaskQuery;
use modules\ui\widgets\Card;
use modules\ui\widgets\Icon;
use modules\ui\widgets\Menu;
use Yii;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\base\ModelEvent;
use yii\bootstrap4\ButtonDropdown;
use yii\db\Expression;
use yii\helpers\Html;

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

            $controller->view->on(
                'block:crm/admin/customer/components/view-layout:begin',
                [$this, 'registerCustomerViewMenu']
            );

            $controller->view->on(
                'block:crm/admin/customer/view:begin',
                [$this, 'registerCustomerMoreActionMenu']
            );

            $controller->view->on(
                'block:crm/admin/customer/view:summary:end',
                [$this, 'registerWidgetForCustomer']
            );

            Event::on(TaskSearch::class, TaskSearch::EVENT_INIT, [$this, 'onTaskSearchModelInit']);
        }
    }

    /**
     * @param ModelEvent $event
     */
    public function onTaskSearchModelInit($event)
    {
        /** @var TaskSearch $model */
        $model = $event->sender;

        if (!isset($model->params['model'])) {
            return;
        }

        if (!isset($model->params['models'])) {
            $model->params['models'] = [];
        }

        if ($model->params['model'] === 'customer' && isset($model->params['model_id'])) {
            $model->params['models'][] = function ($query) use ($model) {
                /** @var TaskQuery $query */

                $query->leftJoin(Ticket::tableName(), ['ticket.id' => new Expression('[[task.model_id]]'), 'task.model' => 'ticket'])
                    ->leftJoin(CustomerContact::tableName(), ['customer_contact.id' => new Expression('[[ticket.contact_id]]')]);

                return ['customer_contact.customer_id' => $model->params['model_id']];
            };
        }
    }

    /**
     * @param ViewBlockEvent $event
     */
    public function registerCustomerViewMenu($event)
    {
        /** @var Customer $customer */
        $customer = $event->viewParams['model'];

        Event::on(Menu::class, Menu::EVENT_INIT, function ($menuEvent) use ($customer) {
            /** @var Menu $menu */

            $menu = $menuEvent->sender;

            if ($menu->realId !== 'customer-view-menu') {
                return;
            }

            $menu->items['ticket'] = [
                'label' => Yii::t('app', 'Ticket'),
                'icon' => 'i8:two-tickets',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'url' => ['/support/admin/ticket/index', 'view' => 'customer', 'customer_id' => $customer->id],
            ];
        });
    }

    /**
     * @param View $view
     */
    protected function registerMenu($view)
    {
        $view->menu->addItems([
            'main/support' => [
                'label' => Yii::t('app', 'Support'),
                'icon' => 'i8:online-support',
                'sort' => 1,
                'options' => [
                    'class' => 'heading',
                ],
            ],
            'main/support/ticket' => [
                'label' => Yii::t('app', 'Ticket'),
                'icon' => 'i8:two-tickets',
                'url' => ['/support/admin/ticket/index'],
                'sort' => 1,
                'linkOptions' => [
                    'data-lazy-link' => true,
                    'data-lazy-container' => '#main-container',
                ],
            ],
            'main/support/knowledge-base' => [
                'label' => Yii::t('app', 'Knowledge Base'),
                'icon' => 'i8:open-book',
                'url' => ['/support/admin/knowledge-base/index'],
                'sort' => 1,
                'linkOptions' => [
                    'data-lazy-link' => true,
                    'data-lazy-container' => '#main-container',
                ],
            ],
            'setting/ticket' => [
                'label' => Yii::t('app', 'Ticket'),
                'icon' => 'i8:two-tickets',
                'url' => ['/core/admin/setting/index', 'section' => 'ticket'],
                'linkOptions' => [
                    'data-lazy-container' => '#main-container',
                    'data-lazy-link' => true,
                ],
            ],
        ]);
    }


    /**
     * @param ViewBlockEvent $event
     */
    public function registerCustomerMoreActionMenu($event)
    {
        /** @var Customer $model */
        $model = $event->viewParams['model'];

        Event::on(ButtonDropdown::class, ButtonDropdown::EVENT_INIT, function ($widgetEvent) use ($model) {
            /** @var ButtonDropdown $buttonDropdown */
            $buttonDropdown = $widgetEvent->sender;

            if ($buttonDropdown->realId === 'customer-more-action') {
                $buttonDropdown->dropdown['items'][] = [
                    'label' => Icon::show('i8:two-tickets', ['class' => 'icon icons8-size mr-2']) . Yii::t('app', 'Add {object}', [
                            'object' => Yii::t('app', 'Ticket'),
                        ]),
                    'url' => ['/support/admin/ticket/add', 'contact_id' => $model->primaryContact->id],
                    'linkOptions' => [
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal' => 'ticket-form-modal',
                        'data-lazy-modal-size' => 'modal-lg',
                    ],
                ];
            }
        });
    }

    /**
     * @param ViewBlockEvent $event
     *
     * @return string
     *
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function registerWidgetForCustomer($event)
    {
        /**
         * @var Customer $customer
         * @var View     $view
         */

        $customer = $event->viewParams['model'];
        $view = $event->sender;
        $searchModel = new TicketSearch([
            'params' => [
                'customer_id' => $customer->id,
                'addUrl' => ['/support/admin/ticket/add', 'customer_id' => $customer->id],
            ],
        ]);

        $searchModel->getQuery()->joinWith('contact')->andWhere(['contact_of_ticket.customer_id' => $customer->id]);

        ob_start();
        ob_implicit_flush(false);

        $card = Card::begin([
            'title' => Yii::t('app', 'Ticket Overview'),
            'icon' => 'i8:two-tickets',
            'bodyOptions' => false,
            'options' => [
                'class' => 'card mb-3 border border-bottom-0 rounded shadow-sm overflow-hidden',
            ],
            'content' => $view->render('@modules/support/views/admin/ticket/components/data-statistic', [
                'searchModel' => $searchModel,
                'searchAction' => ['/support/admin/ticket/index', 'customer_id' => $customer->id, 'view' => 'customer'],
            ]),
        ]);

        $card->addToHeader(Html::a([
            'url' => ['/support/admin/ticket/index', 'customer_id' => $customer->id, 'view' => 'customer'],
            'label' => Yii::t('app', 'See More'),
            'icon' => 'i8:double-right',
            'class' => 'btn btn-light btn-sm',
        ]));

        Card::end();

        $card = ob_get_clean();

        echo Html::tag('div', $card, [
            'class' => 'col-md-12',
        ]);
    }
}