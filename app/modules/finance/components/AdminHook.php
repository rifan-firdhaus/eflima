<?php namespace modules\finance\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\forms\history\HistorySearch;
use modules\account\models\queries\HistoryQuery;
use modules\account\web\admin\Controller;
use modules\account\web\admin\View;
use modules\account\widgets\history\HistoryWidget;
use modules\account\widgets\history\HistoryWidgetEvent;
use modules\core\components\HookTrait;
use modules\core\web\ViewBlockEvent;
use modules\crm\models\Customer;
use modules\finance\models\Currency;
use modules\finance\models\Expense;
use modules\finance\models\forms\expense\ExpenseSearch;
use modules\finance\models\forms\invoice\InvoiceSearch;
use modules\finance\models\Invoice;
use modules\finance\models\InvoiceItem;
use modules\finance\widgets\inputs\CurrencyInput;
use modules\task\models\forms\task\TaskSearch;
use modules\task\models\query\TaskQuery;
use modules\task\models\Task;
use modules\ui\widgets\Card;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\Icon;
use modules\ui\widgets\Menu;
use modules\ui\widgets\table\cells\Cell;
use Yii;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\base\ModelEvent;
use yii\bootstrap4\ButtonDropdown;
use yii\db\Exception;
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
        'invoice.add' => 'Adding Invoice',
        'invoice.update' => 'Updating Invoice',
        'invoice.pay' => 'Record payment {amount}',
        'invoice_item.add' => 'Adding item "{name}"',
        'invoice_item.update' => 'Updating item "{name}"',
        'invoice_item.delete' => 'Deleting item "{name}"',

        'expense.add' => 'Adding Expense',
        'expense.update' => 'Updating Expense',
    ];

    protected $historyOptions = [
        'invoice_item.add' => [
            'icon' => 'i8:add-shopping-cart',
            'iconOptions' => ['class' => 'icon bg-info'],
        ],
        'invoice_item.update' => [
            'icon' => 'i8:shopping-cart',
        ],
        'invoice_item.delete' => [
            'icon' => 'i8:clear-shopping-cart',
            'iconOptions' => ['class' => 'icon bg-warning'],
        ],
        'invoice.pay' => [
            'icon' => 'i8:receive-cash',
            'iconOptions' => ['class' => 'icon bg-success'],
        ],

        'expense.billed' => [
            'icon' => 'i8:add-shopping-cart',
            'iconOptions' => ['class' => 'icon bg-success'],
        ],
        'expense.unbilled' => [
            'icon' => 'i8:clear-shopping-cart',
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

            Event::on(TaskSearch::class, TaskSearch::EVENT_INIT, [$this, 'onTaskSearchModelInit']);
            Event::on(HistorySearch::class, HistorySearch::EVENT_INIT, [$this, 'onHistorySearchModelInit']);
            Event::on(HistoryWidget::class, HistoryWidget::EVEMT_RENDER_ITEM, [$this, 'renderHistoryWidgetItem']);

            $this->customizeCustomerForm($controller->view);

            $controller->view->on(
                'block:finance/admin/invoice-item/components/item-row:render',
                [$this, 'renderInvoiceItemRow']
            );

            $controller->view->on(
                'block:crm/admin/customer/components/view-layout:begin',
                [$this, 'registerCustomerViewMenu']
            );

            $controller->view->on(
                'block:crm/admin/customer/view:begin',
                [$this, 'registerCustomerMoreActionMenu']
            );

            $controller->view->on(
                'block:crm/admin/customer/view:summary:begin',
                [$this, 'registerWidgetForCustomer']
            );

            $controller->view->on('block:crm/admin/customer/components/data-table:begin', [$this, 'customizeCustomerDataTable']);
        }
    }

    /**
     * @param ModelEvent $event
     */
    public function onHistorySearchModelInit($event)
    {
        /** @var TaskSearch $model */
        $model = $event->sender;

        if (!isset($model->params['model'])) {
            return;
        }

        if (!isset($model->params['models'])) {
            $model->params['models'] = [];
        }

        if ($model->params['model'] === Customer::class && isset($model->params['model_id'])) {
            $model->params['models'][] = function ($query) use ($model) {
                /** @var HistoryQuery $query */

                $query->leftJoin(Invoice::tableName(), [
                    'history.model' => Invoice::class,
                    'invoice.id' => new Expression('[[history.model_id]]'),
                    'invoice.customer_id' => $model->params['model_id'],
                ]);

                $query->leftJoin(Expense::tableName(), [
                    'history.model' => Expense::class,
                    'expense.id' => new Expression('[[history.model_id]]'),
                    'expense.customer_id' => $model->params['model_id'],
                ]);

                $query->leftJoin(['finance_task' => Task::tableName()], [
                    'finance_task.id' => new Expression('[[history.model_id]]'),
                    'history.model' => Task::class,
                ]);

                $query->leftJoin(['expense_of_task' => Expense::tableName()], [
                    'finance_task.model' => 'expense',
                    'expense_of_task.id' => new Expression('[[finance_task.model_id]]'),
                    'expense_of_task.customer_id' => $model->params['model_id'],
                ]);
                $query->leftJoin(['invoice_of_task' => Invoice::tableName()], [
                    'finance_task.model' => 'invoice',
                    'invoice_of_task.id' => new Expression('[[finance_task.model_id]]'),
                    'invoice_of_task.customer_id' => $model->params['model_id'],
                ]);

                return [
                    'OR',
                    ['IS NOT', 'invoice.id', null],
                    ['IS NOT', 'expense.id', null],
                    ['IS NOT', 'invoice_of_task.id', null],
                    ['IS NOT', 'invoice_of_task.id', null],
                ];
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

                $query->leftJoin(Expense::tableName(), ['expense.id' => new Expression('[[task.model_id]]'), 'task.model' => 'expense']);

                return ['expense.customer_id' => $model->params['model_id']];
            };

            $model->params['models'][] = function ($query) use ($model) {
                /** @var TaskQuery $query */

                $query->leftJoin(Invoice::tableName(), ['invoice.id' => new Expression('[[task.model_id]]'), 'task.model' => 'invoice']);

                return ['invoice.customer_id' => $model->params['model_id']];
            };
        }
    }

    /**
     * @param HistoryWidgetEvent $event
     *
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function renderHistoryWidgetItem($event)
    {
        /** @var HistoryWidget $widget */
        $widget = $event->sender;
        $model = $event->model;

        if (in_array($model->key, [
            'invoice_item.add',
            'invoice_item.update',
            'invoice_item.delete',
            'invoice.pay',
        ])) {
            $event->params['invoice_number'] = Html::a([
                'url' => ['/finance/admin/invoice/view', 'id' => $model->params['id']],
                'label' => Html::encode($model->params['invoice_number']),
                'data-lazy-container' => '#main-container',
                'data-lazy-modal' => 'invoice-view-modal',
                'class' => 'important',
            ]);

            if ($model->key === 'invoice.pay') {
                $event->params['amount'] = Html::tag('span', Yii::$app->formatter->asCurrency($model->params['amount']), [
                    'class' => 'important text-success',
                ]);
            } elseif (in_array($model->key, [
                'invoice_item.add',
                'invoice_item.update',
                'invoice_item.delete',
            ])) {
                $event->params['name'] = $model->key === 'invoice_item.delete' ? $model->params['name'] : Html::a([
                    'url' => ['/finance/admin/invoice-item/update', 'id' => $model->params['id']],
                    'label' => Html::encode($model->params['name']),
                    'data-lazy-container' => '#main-container',
                    'data-lazy-modal-size' => 'modal-md',
                    'class' => 'important',
                    'data-lazy-modal' => 'invoice-item-view-modal',
                ]);
            }
        } elseif (in_array($model->key, [
            'invoice.add',
            'invoice.update',
            'invoice.delete',
        ])) {
            $event->params['number'] = Html::a([
                'url' => ['/finance/admin/invoice/view', 'id' => $model->params['id']],
                'label' => Html::encode($model->params['number']),
                'data-lazy-container' => '#main-container',
                'data-lazy-modal' => 'invoice-view-modal',
                'class' => 'important',
            ]);
            $event->params['customer_name'] = Html::a([
                'url' => ['/crm/admin/customer/view', 'id' => $model->params['customer_id']],
                'label' => Html::encode($model->params['customer_name']),
                'data-lazy-container' => '#main-container',
                'data-lazy-modal' => 'customer-view-modal',
                'class' => 'important',
            ]);
        } elseif (in_array($model->key, [
            'expense.add',
            'expense.update',
            'expense.delete',
            'expense.billed',
            'expense.unbilled',
        ])) {
            $event->params['name'] = Html::a([
                'url' => ['/finance/admin/expense/view', 'id' => $model->params['id']],
                'label' => Html::encode($model->params['name']),
                'data-lazy-container' => '#main-container',
                'data-lazy-modal' => 'expense-view-modal',
                'class' => 'important',
            ]);

            if (in_array($model->key, ['expense.billed', 'expense.unbilled'])) {
                $event->params['invoice_number'] = Html::a([
                    'url' => ['/finance/admin/invoice/view', 'id' => $model->params['invoice_id']],
                    'label' => Html::encode($model->params['invoice_number']),
                    'data-lazy-container' => '#main-container',
                    'data-lazy-modal' => 'invoice-view-modal',
                    'class' => 'important',
                ]);
            } else {
                $event->params['customer_name'] = Html::a([
                    'url' => ['/crm/admin/customer/view', 'id' => $model->params['customer_id']],
                    'label' => Html::encode($model->params['customer_name']),
                    'data-lazy-container' => '#main-container',
                    'data-lazy-modal' => 'customer-view-modal',
                    'class' => 'important',
                ]);
            }
        }

        if (isset($this->historyOptions[$model->key])) {
            foreach ($this->historyOptions[$model->key] AS $attribute => $value) {
                $event->{$attribute} = $value;
            }
        }

        if (in_array($widget->realId, ['invoice-history', 'expense-history'])) {
            if (isset($this->historyShortDescription[$model->key])) {
                $event->description = $this->historyShortDescription[$model->key];
            }
        }
    }

    /**
     * @param ViewBlockEvent $event
     */
    public function customizeCustomerDataTable($event)
    {
        $viewParams = $event->params;

        $viewParams['dataTableOptions']['columns'][] = [
            'attribute' => 'currency_code',
            'sort' => 2,
            'contentCell' => [
                'hAlign' => Cell::H_ALIGN_CENTER,
                'vAlign' => Cell::V_ALIGN_CENTER,
            ],
            'headerCell' => [
                'hAlign' => Cell::H_ALIGN_CENTER,
            ],
        ];

        $viewParams['dataTableOptions']['columns'][] = [
            'attribute' => 'due',
            'label' => 'due',
            'sort' => 9,
            'format' => 'raw',
            'contentCell' => [
                'hAlign' => Cell::H_ALIGN_RIGHT,
                'vAlign' => Cell::V_ALIGN_CENTER,
            ],
            'headerCell' => [
                'hAlign' => Cell::H_ALIGN_RIGHT,
            ],
            'content' => function ($model) {
                /** @var Customer $model */
                $data = Invoice::find()->hasPaymentDue()
                    ->select([
                        'total_due' => 'SUM(total_due)',
                        'real_total_due' => 'SUM(real_total_due)',
                    ])
                    ->andWhere(['customer_id' => $model->id])
                    ->createCommand()
                    ->queryOne();

                $totalDue = Html::a(Yii::$app->formatter->asCurrency($data['total_due'], $model->currency_code), [
                    '/finance/admin/invoice/index',
                    'view' => 'customer',
                    'customer_id' => $model->id,
                    'InvoiceSearch' => [
                        'has_due' => 1,
                    ],
                ], [
                    'data-lazy-container' => '#main#',
                    'class' => $data['total_due'] > 0 ? 'text-danger font-weight-bold' : '',
                ]);
                $realTotalDue = Html::tag('div', Yii::$app->formatter->asCurrency($data['real_total_due']), [
                    'class' => 'data-table-secondary-text',
                ]);

                return $totalDue . $realTotalDue;
            },
        ];
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

            $menu->items['transaction'] = [
                'label' => Yii::t('app', 'Transaction'),
                'icon' => 'i8:money-transfer',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'items' => [
                    [
                        'label' => Yii::t('app', 'Invoice'),
                        'icon' => 'i8:cash',
                        'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                        'url' => ['/finance/admin/invoice/index', 'view' => 'customer', 'customer_id' => $customer->id],
                    ],
                    [
                        'label' => Yii::t('app', 'Payment'),
                        'icon' => 'i8:receive-cash',
                        'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                        'url' => ['/finance/admin/invoice-payment/index', 'view' => 'customer', 'customer_id' => $customer->id],
                    ],
                    [
                        'label' => Yii::t('app', 'Expense'),
                        'icon' => 'i8:money-transfer',
                        'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                        'url' => ['/finance/admin/expense/index', 'view' => 'customer', 'customer_id' => $customer->id],
                    ],
                ],
            ];
        });
    }

    /**
     * @param ViewBlockEvent $event
     */
    public function renderInvoiceItemRow($event)
    {
        /**
         * @var View        $view
         * @var InvoiceItem $model
         */

        $view = $event->sender;
        $model = $event->params['model'];

        if ($model->type === 'expense') {
            echo $view->render('@modules/finance/views/admin/expense/components/invoice-item-row', [
                'model' => $model,
            ]);
        }
    }

    /**
     * @param View $view
     */
    protected function customizeCustomerForm($view)
    {
        // Register validator
        Event::on(Customer::class, Customer::EVENT_INIT, function ($event) {
            /** @var Customer $model */
            $model = $event->sender;

            if (!in_array($model->scenario, ['admin/add', 'admin/update'])) {
                return;
            }

            $model->validators->append(Validator::createValidator('exist', $model, ['currency_code'], [
                'targetClass' => Currency::class,
                'skipOnError' => true,
                'targetAttribute' => ['currency_code' => 'code'],
            ]));
        });

        $view->on('block:crm/admin/customer/components/form:begin', function () {
            Event::on(CardField::class, CardField::EVENT_INIT, function ($event) {
                /** @var CardField $card */
                $card = $event->sender;

                // Add field to general card
                if (isset($card->inputOptions['id']) && $card->inputOptions['id'] == "customer-general_section-{$card->form->view->uniqueId}") {
                    $card->fields[0]['fields'][0]['field']['fields'][] = [
                        'attribute' => 'currency_code',
                        'type' => ActiveField::TYPE_WIDGET,
                        'label' => Yii::t('app', 'Currency'),
                        'widget' => [
                            'class' => CurrencyInput::class,
                            'is_enabled' => true,
                        ],
                    ];
                }
            });
        });
    }

    /**
     * @param View $view
     */
    protected function registerMenu($view)
    {
        $view->menu->addItems([
            'main/transaction' => [
                'label' => Yii::t('app', 'Transaction'),
                'icon' => 'i8:user-menu-male',
                'sort' => 1,
                'options' => [
                    'class' => 'heading',
                ],
            ],
            'main/transaction/invoice' => [
                'label' => Yii::t('app', 'Invoice'),
                'icon' => 'i8:cash',
                'url' => ['/finance/admin/invoice/index'],
                'sort' => 1,
                'linkOptions' => [
                    'data-lazy-link' => true,
                    'data-lazy-container' => '#main-container',
                ],
            ],
            'main/transaction/expense' => [
                'label' => Yii::t('app', 'Expense'),
                'icon' => 'i8:money-transfer',
                'url' => ['/finance/admin/expense/index'],
                'sort' => 1,
                'linkOptions' => [
                    'data-lazy-link' => true,
                    'data-lazy-container' => '#main-container',
                ],
            ],
            'main/transaction/payment' => [
                'label' => Yii::t('app', 'Payment'),
                'icon' => 'i8:receive-cash',
                'url' => ['/finance/admin/invoice-payment/index'],
                'sort' => 1,
                'linkOptions' => [
                    'data-lazy-link' => true,
                    'data-lazy-container' => '#main-container',
                ],
            ],
            'main/transaction/product' => [
                'label' => Yii::t('app', 'Product'),
                'icon' => 'i8:shipping-container',
                'url' => ['/finance/admin/product/index'],
                'sort' => 1,
                'linkOptions' => [
                    'data-lazy-link' => true,
                    'data-lazy-container' => '#main-container',
                ],
            ],
            'setting/finance' => [
                'label' => Yii::t('app', 'Finance'),
                'url' => ['/core/admin/setting/index', 'section' => 'finance'],
                'icon' => 'i8:money-yours',
                'linkOptions' => [
                    'data-lazy-container' => '#main-container',
                    'data-lazy-link' => true,
                ],
            ],
        ]);

        if (Yii::$app->hasModule('quick_access')) {
            $view->menu->addItems([
                'quick_access/quick_add/invoice' => [
                    'label' => Yii::t('app', 'Invoice'),
                    'icon' => 'i8:cash',
                    'url' => ['/finance/admin/invoice/add'],
                    'linkOptions' => [
                        'data-lazy-modal' => 'invoice-form-modal',
                        'data-lazy-container' => '#main-container',
                        'data-lazy-link' => true,
                        'class' => 'nav-link side-panel-close',
                    ],
                ],
                'quick_access/quick_add/expense' => [
                    'label' => Yii::t('app', 'Expense'),
                    'icon' => 'i8:money-transfer',
                    'url' => ['/finance/admin/expense/add'],
                    'linkOptions' => [
                        'data-lazy-modal' => 'expense-form-modal',
                        'data-lazy-container' => '#main-container',
                        'data-lazy-link' => true,
                        'class' => 'nav-link side-panel-close',
                    ],
                ],
            ]);
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
                    'label' => Icon::show('i8:cash', ['class' => 'icon icons8-size mr-2']) . Yii::t('app', 'Add {object}', [
                            'object' => Yii::t('app', 'Invoice'),
                        ]),
                    'url' => ['/finance/admin/invoice/add', 'customer_id' => $model->id],
                    'linkOptions' => [
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal' => 'invoice-form-modal',
                    ],
                ];

                $buttonDropdown->dropdown['items'][] = [
                    'label' => Icon::show('i8:receive-cash', ['class' => 'icon icons8-size mr-2']) . Yii::t('app', 'Add {object}', [
                            'object' => Yii::t('app', 'Payment'),
                        ]),
                    'url' => ['/finance/admin/invoice-payment/add', 'customer_id' => $model->id],
                    'linkOptions' => [
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal' => 'invoice-payment-form-modal',
                    ],
                ];

                $buttonDropdown->dropdown['items'][] = [
                    'label' => Icon::show('i8:money-transfer', ['class' => 'icon icons8-size mr-2']) . Yii::t('app', 'Add {object}', [
                            'object' => Yii::t('app', 'Expense'),
                        ]),
                    'url' => ['/finance/admin/expense/add', 'customer_id' => $model->id],
                    'linkOptions' => [
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal' => 'expense-form-modal',
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
     */
    public function registerWidgetForCustomer($event)
    {
        /**
         * @var Customer $customer
         * @var View     $view
         */

        $customer = $event->viewParams['model'];
        $view = $event->sender;

        $invoiceSearchModel = new InvoiceSearch([
            'params' => [
                'customer_id' => $customer->id,
                'addUrl' => ['/finance/admin/invoice/add', 'customer_id' => $customer->id],
            ],
        ]);

        ob_start();
        ob_implicit_flush(false);

        $invoiceCard = Card::begin([
            'title' => Yii::t('app', 'Invoice Overview'),
            'icon' => 'i8:cash',
            'bodyOptions' => false,
            'options' => [
                'class' => 'card border mb-3 border-bottom-0 rounded shadow-sm overflow-hidden',
            ],
            'headerOptions' => [
                'class' => 'card-header border-bottom',
            ],
        ]);

        echo $view->render('@modules/finance/views/admin/invoice/components/data-payment-statistic', [
            'searchModel' => $invoiceSearchModel,
            'searchAction' => ['/finance/admin/invoice/index', 'customer_id' => $customer->id, 'view' => 'customer'],
        ]);

        $invoiceCard->addToHeader(Html::a([
            'url' => ['/finance/admin/invoice/index', 'customer_id' => $customer->id, 'view' => 'customer'],
            'label' => Yii::t('app', 'See More'),
            'icon' => 'i8:double-right',
            'class' => 'btn btn-light btn-sm',
        ]));

        Card::end();

        $invoiceCard = ob_get_clean();

        $invoiceSection = Html::tag('div', $invoiceCard, [
            'class' => 'col-md-12',
        ]);

        $expenseSearchModel = new ExpenseSearch([
            'params' => [
                'customer_id' => $customer->id,
                'addUrl' => ['/finance/admin/expense/add', 'customer_id' => $customer->id],
            ],
        ]);

        ob_start();
        ob_implicit_flush(false);

        $expenseCard = Card::begin([
            'title' => Yii::t('app', 'Expense Overview'),
            'icon' => 'i8:money-transfer',
            'bodyOptions' => false,
            'options' => [
                'class' => 'card border mb-3 border-bottom-0 rounded shadow-sm overflow-hidden',
            ],
            'headerOptions' => [
                'class' => 'card-header border-bottom',
            ],
        ]);

        echo $view->render('@modules/finance/views/admin/expense/components/data-bill-statistic', [
            'searchModel' => $expenseSearchModel,
            'withTotal' => true,
            'searchAction' => ['/finance/admin/expense/index', 'customer_id' => $customer->id, 'view' => 'customer'],
        ]);

        $expenseCard->addToHeader(Html::a([
            'url' => ['/finance/admin/expense/index', 'customer_id' => $customer->id, 'view' => 'customer'],
            'label' => Yii::t('app', 'See More'),
            'icon' => 'i8:double-right',
            'class' => 'btn btn-light btn-sm',
        ]));

        Card::end();

        $expenseCard = ob_get_clean();

        $expenseSection = Html::tag('div', $expenseCard, [
            'class' => 'col-md-12',
        ]);

        echo $invoiceSection . $expenseSection;
    }
}
