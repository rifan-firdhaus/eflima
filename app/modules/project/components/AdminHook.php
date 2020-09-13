<?php namespace modules\project\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\forms\history\HistorySearch;
use modules\account\models\queries\HistoryQuery;
use modules\account\web\admin\Controller;
use modules\account\web\admin\View;
use modules\account\widgets\history\HistoryWidget;
use modules\account\widgets\history\HistoryWidgetEvent;
use modules\calendar\models\forms\event\EventSearch;
use modules\core\behaviors\AttributeTypecastBehavior;
use modules\core\components\HookTrait;
use modules\core\components\SearchableModelEvent;
use modules\core\db\ModelValidatorsEvent;
use modules\core\web\ViewBlockEvent;
use modules\crm\models\Customer;
use modules\finance\models\Expense;
use modules\finance\models\forms\expense\ExpenseSearch;
use modules\finance\models\forms\invoice\InvoiceSearch;
use modules\finance\models\forms\invoice_payment\InvoicePaymentSearch;
use modules\finance\models\Invoice;
use modules\finance\models\queries\ExpenseQuery;
use modules\finance\models\queries\InvoicePaymentQuery;
use modules\finance\models\queries\InvoiceQuery;
use modules\finance\models\queries\ProductQuery;
use modules\project\assets\admin\ProjectMilestoneTaskFormAsset;
use modules\project\models\Project;
use modules\project\models\ProjectMilestone;
use modules\project\models\ProjectStatus;
use modules\project\models\queries\ProjectMilestoneQuery;
use modules\project\widgets\inputs\ProjectInput;
use modules\project\widgets\inputs\ProjectMilestoneInput;
use modules\support\models\forms\ticket\TicketSearch;
use modules\support\models\queries\TicketQuery;
use modules\support\models\Ticket;
use modules\task\models\forms\task\TaskSearch;
use modules\task\models\query\TaskQuery;
use modules\task\models\Task;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\Icon;
use modules\ui\widgets\Menu;
use Yii;
use yii\base\Event;
use yii\base\ModelEvent;
use yii\bootstrap4\ButtonDropdown;
use yii\db\Expression;
use yii\helpers\Html;
use yii\validators\Validator;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class AdminHook
{
    use HookTrait;

    protected $historyShortDescription = [
        'project.status' => 'Changing status to {status_label}',
    ];

    protected $historyOptions = [
        'project_milestone.move_task' => [
            'icon' => 'i8:slider',
            'iconOptions' => ['class' => 'icon bg-info'],
        ],
        'project.status' => [
            'icon' => 'i8:hammer',
            'iconOptions' => ['class' => 'icon bg-primary'],
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

            $this->customizeInvoiceForm($controller->view);
            $this->customizeExpenseForm($controller->view);
            $this->customizeTicketForm($controller->view);
            $this->customizeTaskForm($controller->view);

            $controller->view->on(
                'block:crm/admin/customer/components/view-layout:begin',
                [$this, 'registerCustomerViewMenu']
            );

            $controller->view->on(
                'block:crm/admin/customer/view:begin',
                [$this, 'registerCustomerMoreActionMenu']
            );

            Event::on(Invoice::class, Invoice::EVENT_INIT, [$this, 'onInvoiceModelInit']);
            Event::on(Expense::class, Expense::EVENT_INIT, [$this, 'onExpenseModelInit']);
            Event::on(Ticket::class, Ticket::EVENT_INIT, [$this, 'onTicketModelInit']);
            Event::on(Task::class, Task::EVENT_INIT, [$this, 'onTaskModelInit']);

            Event::on(TaskSearch::class, TaskSearch::EVENT_INIT, [$this, 'onTaskSearchModelInit']);
            Event::on(HistorySearch::class, HistorySearch::EVENT_INIT, [$this, 'onHistorySearchModelInit']);
            Event::on(TicketSearch::class, TicketSearch::EVENT_QUERY, [$this, 'onTicketSearchModelQuery']);
            Event::on(InvoiceSearch::class, InvoiceSearch::EVENT_QUERY, [$this, 'onInvoiceSearchModelQuery']);
            Event::on(InvoicePaymentSearch::class, InvoicePaymentSearch::EVENT_QUERY, [$this, 'onInvoicePaymentSearchModelQuery']);
            Event::on(ExpenseSearch::class, ExpenseSearch::EVENT_QUERY, [$this, 'onExpenseSearchModelQuery']);

            Event::on(HistoryWidget::class, HistoryWidget::EVEMT_RENDER_ITEM, [$this, 'renderHistoryWidgetItem']);

            if (Yii::$app->hasModule('calendar')) {
                Event::on(EventSearch::class, EventSearch::EVENT_INIT, [$this, 'onEventSearchModelInit']);
            }
        }
    }

    /**
     * @param SearchableModelEvent $event
     */
    public function onInvoiceSearchModelQuery($event)
    {
        /**
         * @var InvoiceSearch $model
         * @var InvoiceQuery  $query
         */
        $model = $event->sender;
        $query = $event->query;

        if (!isset($model->params['project_id'])) {
            return;
        }

        $query->andWhere(['invoice.project_id' => $model->params['project_id']]);
    }

    /**
     * @param SearchableModelEvent $event
     */
    public function onTicketSearchModelQuery($event)
    {
        /**
         * @var TicketSearch $model
         * @var TicketQuery  $query
         */
        $model = $event->sender;
        $query = $event->query;

        if (!isset($model->params['project_id'])) {
            return;
        }

        $query->andWhere(['ticket.project_id' => $model->params['project_id']]);
    }

    /**
     * @param SearchableModelEvent $event
     */
    public function onInvoicePaymentSearchModelQuery($event)
    {
        /**
         * @var InvoicePaymentSearch $model
         * @var InvoicePaymentQuery  $query
         */
        $model = $event->sender;
        $query = $event->query;

        if (!isset($model->params['project_id'])) {
            return;
        }

        $query->andWhere(['invoice_of_payment.project_id' => $model->params['project_id']]);
    }

    /**
     * @param SearchableModelEvent $event
     */
    public function onExpenseSearchModelQuery($event)
    {
        /**
         * @var ExpenseSearch $model
         * @var ExpenseQuery  $query
         */
        $model = $event->sender;
        $query = $event->query;

        if (!isset($model->params['project_id'])) {
            return;
        }

        $query->andWhere(['expense.project_id' => $model->params['project_id']]);
    }

    /**
     * @param ModelEvent $event
     */
    public function onEventSearchModelInit($event)
    {
        /** @var EventSearch $model */
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

                $query->join('LEFT JOIN', Project::tableName(), ['project.id' => new Expression('[[event.model_id]]'), 'event.model' => 'project']);

                return ['project.customer_id' => $model->params['model_id']];
            };
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

                $query->leftJoin(Project::tableName(), ['project.id' => new Expression('[[task.model_id]]'), 'task.model' => 'project']);

                return ['project.customer_id' => $model->params['model_id']];
            };
        }
    }

    /**
     * @param ModelEvent $event
     */
    public function onHistorySearchModelInit($event)
    {
        /** @var HistorySearch $model */
        $model = $event->sender;

        if (!isset($model->params['model'])) {
            return;
        }

        if (!isset($model->params['models'])) {
            $model->params['models'] = [];
        }

        if ($model->params['model'] === Customer::class && isset($model->params['model_id'])) {
            $model->params['models'][] = function ($query) use ($model) {
                /** @var TaskQuery $query */

                $query->leftJoin(Project::tableName(), ['project.id' => new Expression('[[history.model_id]]'), 'history.model' => Project::class]);

                return ['project.customer_id' => $model->params['model_id']];
            };
            $model->params['models'][] = function ($query) use ($model) {
                /** @var HistoryQuery $query */

                $query->leftJoin(['project_task' => Task::tableName()], ['project_task.id' => new Expression('[[history.model_id]]'), 'history.model' => Task::class]);

                $query->leftJoin(['project_of_task' => Project::tableName()], ['project_of_task.id' => new Expression('[[project_task.model_id]]'), 'project_task.model' => 'project']);

                return ['project_of_task.customer_id' => $model->params['model_id']];
            };
        }
    }

    /**
     * @param ModelEvent $event
     */
    public function onInvoiceModelInit($event)
    {
        /** @var Invoice $model */
        $model = $event->sender;

        if (in_array($model->scenario, ['admin/add', 'admin/update']) && !isset($model->project_id)) {
            $model->project_id = Yii::$app->request->get('project_id', null);
        }
    }

    /**
     * @param ModelEvent $event
     */
    public function onExpenseModelInit($event)
    {
        /** @var Invoice $model */
        $model = $event->sender;

        if (in_array($model->scenario, ['admin/add', 'admin/update']) && !isset($model->project_id)) {
            $model->project_id = Yii::$app->request->get('project_id', null);
        }
    }

    /**
     * @param ModelEvent $event
     */
    public function onTicketModelInit($event)
    {
        /** @var Ticket $model */
        $model = $event->sender;

        if (in_array($model->scenario, ['admin/add', 'admin/update']) && !isset($model->project_id)) {
            $model->project_id = Yii::$app->request->get('project_id', null);
        }
    }

    /**
     * @param ModelEvent $event
     */
    public function onTaskModelInit($event)
    {
        /** @var Task $model */
        $model = $event->sender;

        /** @var AttributeTypecastBehavior $attributeTypecast */
        $attributeTypecast = $model->getBehavior('attributeTypecast');

        $attributeTypecast->attributeTypes['milestone_id'] = AttributeTypecastBehavior::TYPE_INTEGER;

        if (in_array($model->scenario, ['admin/add', 'admin/update']) && !isset($model->project_id)) {
            $model->milestone_id = Yii::$app->request->get('milestone_id', null);
        }

    }

    /**
     * @param View $view
     */
    protected function customizeInvoiceForm($view)
    {
        // Register validator
        Event::on(Invoice::class, Invoice::EVENT_CREATE_VALIDATORS, function ($event) {
            /**
             * @var ModelValidatorsEvent $event
             * @var Invoice              $model
             */
            $model = $event->sender;

            if (!in_array($model->scenario, ['admin/add', 'admin/update'])) {
                return;
            }

            $event->validators->append(Validator::createValidator('exist', $model, ['project_id'], [
                'targetClass' => Project::class,
                'skipOnError' => true,
                'skipOnEmpty' => true,
                'targetAttribute' => ['project_id' => 'id'],
            ]));
        });

        $view->on('block:finance/admin/invoice/components/form:begin', function ($blockEvent) {
            /**
             * @var ViewBlockEvent $blockEvent
             * @var Expense        $expense
             */

            $invoice = $blockEvent->viewParams['model'];

            Event::on(CardField::class, CardField::EVENT_INIT, function ($event) use ($invoice) {
                /** @var CardField $card */
                $card = $event->sender;

                // Add field to general card
                if (isset($card->inputOptions['id']) && $card->inputOptions['id'] == "invoice-basic_section-{$card->form->view->uniqueId}") {
                    $card->fields[] = [
                        'attribute' => 'project_id',
                        'label' => Yii::t('app', 'Project'),
                        'sort' => 5,
                        'type' => ActiveField::TYPE_WIDGET,
                        'widget' => [
                            'class' => ProjectInput::class,
                            'allowClear' => true,
                            'prompt' => '',
                            'customerInputSelector' => '#' . Html::getInputId($invoice, 'customer_id'),
                        ],
                    ];
                }
            });
        });
    }

    /**
     * @param View $view
     */
    protected function customizeExpenseForm($view)
    {
        // Register validator
        Event::on(Expense::class, Expense::EVENT_CREATE_VALIDATORS, function ($event) {
            /**
             * @var Expense              $model
             * @var ModelValidatorsEvent $event
             */
            $model = $event->sender;

            if (!in_array($model->scenario, ['admin/add', 'admin/update'])) {
                return;
            }

            $event->validators->append(Validator::createValidator('exist', $model, ['project_id'], [
                'targetClass' => Project::class,
                'skipOnEmpty' => true,
                'skipOnError' => true,
                'targetAttribute' => ['project_id' => 'id'],
                'filter' => function ($query) use ($model) {
                    /** @var ProductQuery $query */

                    return $query->andWhere(['customer_id' => $model->customer_id]);
                },
            ]));
        });

        $view->on('block:finance/admin/expense/components/form:begin', function ($blockEvent) {
            /**
             * @var ViewBlockEvent $blockEvent
             * @var Expense        $expense
             */

            $expense = $blockEvent->viewParams['model'];

            Event::on(CardField::class, CardField::EVENT_INIT, function ($event) use ($expense) {
                /** @var CardField $card */
                $card = $event->sender;

                // Add field to general card
                if (isset($card->inputOptions['id']) && $card->inputOptions['id'] == "expense-basic_section-{$card->form->view->uniqueId}") {
                    $card->fields[] = [
                        'attribute' => 'project_id',
                        'label' => Yii::t('app', 'Project'),
                        'sort' => 6,
                        'type' => ActiveField::TYPE_WIDGET,
                        'widget' => [
                            'class' => ProjectInput::class,
                            'allowClear' => true,
                            'prompt' => '',
                            'customerInputSelector' => '#' . Html::getInputId($expense, 'customer_id'),
                        ],
                    ];
                }
            });
        });
    }

    /**
     * @param View $view
     */
    protected function customizeTicketForm($view)
    {
        // Register validator
        Event::on(Ticket::class, Ticket::EVENT_CREATE_VALIDATORS, function ($event) {
            /**
             * @var Ticket               $model
             * @var ModelValidatorsEvent $event
             */
            $model = $event->sender;

            if (!in_array($model->scenario, ['admin/add', 'admin/update'])) {
                return;
            }

            $event->validators->append(Validator::createValidator('exist', $model, ['project_id'], [
                'targetClass' => Project::class,
                'skipOnError' => true,
                'targetAttribute' => ['project_id' => 'id'],
            ]));
        });

        $view->on('block:support/admin/ticket/components/form:begin', function () {
            Event::on(CardField::class, CardField::EVENT_INIT, function ($event) {
                /** @var CardField $card */
                $card = $event->sender;

                // Add field to general card
                if (isset($card->inputOptions['id']) && $card->inputOptions['id'] == "ticket-basic_section-{$card->form->view->uniqueId}") {
                    $card->fields[] = [
                        'attribute' => 'project_id',
                        'label' => Yii::t('app', 'Project'),
                        'sort' => 6,
                        'type' => ActiveField::TYPE_WIDGET,
                        'widget' => [
                            'class' => ProjectInput::class,
                            'allowClear' => true,
                            'prompt' => '',
                        ],
                    ];
                }
            });
        });
    }

    /**
     * @param View $view
     */
    protected function customizeTaskForm($view)
    {
        // Register validator
        Event::on(Task::class, Task::EVENT_CREATE_VALIDATORS, function ($event) {
            /**
             * @var Task                 $model
             * @var ModelValidatorsEvent $event
             */
            $model = $event->sender;

            if (!in_array($model->scenario, ['admin/add', 'admin/update'])) {
                return;
            }

            $event->validators->append(Validator::createValidator('exist', $model, ['milestone_id'], [
                'targetClass' => ProjectMilestone::class,
                'skipOnError' => true,
                'skipOnEmpty' => true,
                'targetAttribute' => ['milestone_id' => 'id'],
                'filter' => function ($query) use ($model) {
                    /** @var ProjectMilestoneQuery $query */

                    return $query->andWhere(['project_id' => $model->project_id]);
                },
                'when' => function ($model) {
                    /** @var Task $model */

                    return $model->model_id === 'project';
                },
            ]));
        });

        $view->on('block:task/admin/task/components/form:form:end', function ($blockEvent) use ($view) {
            $view->registerJs("new ProjectMilestoneTaskForm($('#{$blockEvent->params['form']->id}'));");
        });
        $view->on('block:task/admin/task/components/form:begin', function ($blockEvent) use ($view) {
            ProjectMilestoneTaskFormAsset::register($view);

            Event::on(CardField::class, CardField::EVENT_INIT, function ($event) {
                /** @var CardField $card */
                $card = $event->sender;

                // Add field to general card

                if (isset($card->inputOptions['id']) && $card->inputOptions['id'] == "task-basic_section-{$card->form->view->uniqueId}") {
                    $card->fields[] = [
                        'attribute' => 'milestone_id',
                        'label' => Yii::t('app', 'Milestone'),
                        'sort' => 8,
                        'type' => ActiveField::TYPE_WIDGET,
                        'widget' => [
                            'class' => ProjectMilestoneInput::class,
                            'allowClear' => true,
                            'prompt' => '',
                        ],
                    ];
                }
            });
        });
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

            $menu->items['project'] = [
                'label' => Yii::t('app', 'Project'),
                'url' => ['/project/admin/project/index', 'customer_id' => $customer->id, 'view' => 'customer'],
                'icon' => 'i8:idea',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
            ];
        });
    }

    /**
     * @param View $view
     */
    public function registerMenu($view)
    {
        $view->menu->addItems([
            'main/project' => [
                'label' => Yii::t('app', 'Project'),
                'icon' => 'i8:idea',
                'url' => ['/project/admin/project/index'],
                'sort' => 0,
                'linkOptions' => [
                    'data-lazy-container' => '#main-container',
                    'data-lazy-link' => true,
                ],
            ],
            'setting/project' => [
                'label' => Yii::t('app', 'Project'),
                'url' => ['/core/admin/setting/index', 'section' => 'project'],
                'icon' => 'i8:idea',
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
            'project_milestone.add',
            'project_milestone.update',
            'project_milestone.delete',
            'project_milestone.move_task',
        ])) {
            $event->params['project_name'] = Html::a([
                'label' => Html::encode($model->params['project_name']),
                'url' => ['/project/admin/project/view', 'id' => $model->params['project_id']],
                'data-lazy-container' => '#main-container',
                'data-lazy-modal' => 'project-view-modal',
                'class' => 'important',
            ]);

            $event->params['name'] = Html::a([
                'label' => Html::encode($model->params['name']),
                'url' => ['/project/admin/project-milestone/update', 'id' => $model->params['id']],
                'data-lazy-container' => '#main-container',
                'data-lazy-modal' => 'project-milestone-form-modal',
                'class' => 'important',
                'data-lazy-modal-size' => 'modal-md',
            ]);

            if ($model->key === 'project_milestone.move_task') {
                if ($widget->realId === 'task-history') {
                    $event->description = 'Moving from milestone "{name}" to "{to_name}"';
                }

                $event->params['task_title'] = Html::a([
                    'label' => Html::encode($model->params['task_title']),
                    'url' => ['/task/admin/task/view', 'id' => $model->params['task_id']],
                    'class' => 'important',
                    'data-lazy-container' => '#main-container',
                    'data-lazy-modal' => 'task-view-modal',
                ]);

                $event->params['to_name'] = Html::a([
                    'label' => Html::encode($model->params['to_name']),
                    'url' => ['/project/admin/project-milestone/update', 'id' => $model->params['to_id']],
                    'data-lazy-container' => '#main-container',
                    'data-lazy-modal' => 'project-milestone-form-modal',
                    'class' => 'important',
                    'data-lazy-modal-size' => 'modal-md',
                ]);
            }
        } elseif (in_array($model->key, [
            'project.add',
            'project.update',
            'project.delete',
            'project.status',
        ])) {
            $event->params['name'] = Html::a([
                'label' => Html::encode($model->params['name']),
                'url' => ['/project/admin/project/view', 'id' => $model->params['id']],
                'data-lazy-container' => '#main-container',
                'data-lazy-modal' => 'project-view-modal',
                'class' => 'important',
            ]);

            $event->params['customer_name'] = Html::a([
                'label' => Html::encode($model->params['customer_name']),
                'url' => ['/crm/admin/customer/view', 'id' => $model->params['customer_id']],
                'data-lazy-container' => '#main-container',
                'data-lazy-modal' => 'customer-view-modal',
                'class' => 'important',
            ]);

            if ($model->key === 'project.status') {
                $statusColor = ProjectStatus::find()
                    ->andWhere(['id' => $model->params['status_id']])
                    ->select('color_label')
                    ->createCommand()
                    ->queryScalar();
                $event->params['status_label'] = Html::tag('span', $model->params['status_label'], [
                    'style' => ($statusColor ? "color:{$statusColor}" : false),
                    'class' => 'important',
                ]);
            }
        }

        if (isset($this->historyOptions[$model->key])) {
            foreach ($this->historyOptions[$model->key] AS $attribute => $value) {
                $event->{$attribute} = $value;
            }
        }

        if ($widget->realId == 'project-history') {
            if (isset($this->historyShortDescription[$model->key])) {
                $event->description = $this->historyShortDescription[$model->key];
            }
        }
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
                    'label' => Icon::show('i8:idea', ['class' => 'icon icons8-size mr-2']) . Yii::t('app', 'Add {object}', [
                            'object' => Yii::t('app', 'Project'),
                        ]),
                    'url' => ['/project/admin/project/add', 'customer_id' => $model->id],
                    'linkOptions' => [
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal' => 'project-form-modal',
                    ],
                ];
            }
        });
    }
}
