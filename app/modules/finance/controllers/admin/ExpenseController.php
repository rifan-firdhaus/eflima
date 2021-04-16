<?php namespace modules\finance\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\models\forms\history\HistorySearch;
use modules\account\models\queries\HistoryQuery;
use modules\account\web\admin\Controller;
use modules\account\widgets\lazy\LazyResponse;
use modules\core\helpers\Common;
use modules\crm\models\Customer;
use modules\file_manager\web\UploadedFile;
use modules\finance\models\Expense;
use modules\finance\models\forms\expense\ExpenseInvoiceItem;
use modules\finance\models\forms\expense\ExpenseSearch;
use modules\finance\models\Invoice;
use modules\finance\models\InvoiceItem;
use modules\finance\models\queries\InvoiceQuery;
use modules\task\models\forms\task\TaskSearch;
use modules\task\models\Task;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\lazy\Lazy;
use Throwable;
use Yii;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\base\InvalidRouteException;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\Response;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ExpenseController extends Controller
{
    public $viewMenu = [
        'detail' => [
            'route' => ['/finance/admin/expense/detail'],
            'role' => 'admin.expense.view.detail',
        ],
        'task' => [
            'route' => ['/finance/admin/expense/task'],
            'role' => 'admin.expense.view.task',
        ],
        'history' => [
            'route' => ['/finance/admin/expense/history'],
            'role' => 'admin.expense.view.history',
        ],
    ];

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access']['rules'] = [
            [
                'allow' => true,
                'actions' => ['index'],
                'verbs' => ['GET'],
                'roles' => ['admin.expense.list'],
            ],
            [
                'allow' => true,
                'actions' => ['add'],
                'verbs' => ['GET', 'POST'],
                'roles' => ['admin.expense.add'],
            ],
            [
                'allow' => true,
                'actions' => ['update'],
                'verbs' => ['GET', 'POST', 'PATCH'],
                'roles' => ['admin.expense.update'],
            ],
            [
                'allow' => true,
                'actions' => ['detail'],
                'verbs' => ['GET'],
                'roles' => ['admin.expense.view.detail'],
            ],
            [
                'allow' => true,
                'actions' => ['task'],
                'verbs' => ['GET'],
                'roles' => ['admin.expense.view.task'],
            ],
            [
                'allow' => true,
                'actions' => ['history'],
                'verbs' => ['GET'],
                'roles' => ['admin.expense.view.history'],
            ],
            [
                'allow' => true,
                'actions' => ['delete','bulk-delete'],
                'verbs' => ['DELETE', 'POST'],
                'roles' => ['admin.expense.delete'],
            ],
            [
                'allow' => true,
                'actions' => ['add-to-invoice','billable-picker'],
                'verbs' => ['GET', 'POST'],
                'roles' => ['admin.expense.bill'],
            ],
            [
                'allow' => true,
                'actions' => ['update-invoice-item'],
                'verbs' => ['GET', 'POST'],
                'roles' => ['admin.invoice.item.update'],
            ],
            [
                'allow' => true,
                'actions' => [
                    'auto-complete',
                    'view',
                ],
                'roles' => ['@'],
                'verbs' => ['GET'],
            ],
        ];

        return $behaviors;
    }

    /**
     * @param string $view
     *
     * @return array|string|Response
     *
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    public function actionIndex($view = 'default')
    {
        $params = Yii::$app->request->queryParams;

        $searchModel = new ExpenseSearch();

        if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $searchModel->load($params);

            return Form::validate($searchModel);
        }

        $searchModel->apply($params);

        switch ($view) {
            case 'customer':
                $customerId = Yii::$app->request->get('customer_id');

                if (!$customerId) {
                    throw new BadRequestHttpException('Missing required parameter: customer_id');
                }

                return $this->indexOfCustomer($customerId, $searchModel);
        }

        return $this->render('index', compact('searchModel'));
    }

    /**
     * @param int|string    $customerId
     * @param ExpenseSearch $searchModel
     *
     * @return array|string|Response
     *
     * @throws InvalidConfigException
     */
    public function indexOfCustomer($customerId, $searchModel)
    {
        $customer = Customer::find()->andWhere(['id' => $customerId])->one();

        if (!$customer) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Customer'),
            ]));
        }

        $searchModel->getQuery()->andWhere(['expense.customer_id' => $customerId]);

        $searchModel->params['customer_id'] = $customerId;

        return $this->render('index-customer', compact('searchModel', 'customer'));
    }

    /**
     * @param Expense    $model
     * @param            $data
     *
     * @return string|array
     */
    protected function modify($model, $data)
    {
        $model->loadDefaultValues();

        if ($model->load($data)) {
            $model->uploaded_attachments = UploadedFile::getInstances($model, 'uploaded_attachments');

            if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                return Form::validate($model);
            }

            if ($model->save()) {
                Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully saved', [
                    'object' => Yii::t('app', 'Expense'),
                    'object_name' => $model->name,
                ]));

                if (Lazy::isLazyModalRequest() || Lazy::isLazyInsideModalRequest()) {
                    Lazy::close();

                    return;
                }

                return $this->redirect(['update', 'id' => $model->id]);
            } elseif ($model->hasErrors()) {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Some of the information you entered is invalid'));
            } else {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to save {object}', [
                    'object' => Yii::t('app', 'Expense'),
                ]));
            }
        }

        return $this->render('modify', compact('model'));
    }

    /**
     * @param string|int $id
     *
     * @param string     $action
     *
     * @return Expense|string|Response
     *
     * @throws InvalidConfigException
     *                               |
     * @throws InvalidRouteException
     */
    public function actionView($id, $action = 'default')
    {
        foreach ($this->viewMenu AS $item) {
            if (!Yii::$app->user->can($item['role'])) {
                continue;
            }

            $route = $item['route'];

            if (is_callable($route)) {
                call_user_func($route, $id);
            } else {
                $route['id'] = $id;
            }


            return $this->redirect($route);
        }

        return $this->redirect(['/']);

    }

    public function actionDetail($id){
        $model = $this->getModel($id);

        if (!($model instanceof Expense)) {
            return $model;
        }

        return $this->render('view', compact('model'));
    }

    /**
     * @param int|string $id
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function actionTask($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Expense)) {
            return $model;
        }

        $taskSearchModel = new TaskSearch([
            'params' => [
                'model' => 'expense',
                'model_id' => $model->id,
            ],
        ]);

        $taskSearchModel->apply(Yii::$app->request->get());

        return $this->render('task', compact('model', 'taskSearchModel'));
    }

    /**
     * @param int|string $id
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function actionHistory($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Expense)) {
            return $model;
        }

        $historySearchParams = [
            'model' => Expense::class,
            'model_id' => $model->id,
            'models' => [],
        ];

        if (Yii::$app->hasModule('task')) {
            $historySearchParams['params']['models'][] = function ($query) use ($model) {
                /** @var HistoryQuery $query */

                $query->leftJoin(Task::tableName(), [
                    'history.model' => Task::class,
                    'task.id' => new Expression('[[history.model_id]]'),
                    'task.model_id' => $model->id,
                    'task.model' => 'expense',
                ]);

                return ['IS NOT', 'task_id', null];
            };
        }

        $historySearchModel = new HistorySearch([
            'params' => $historySearchParams,
        ]);

        return $this->render('history', compact('historySearchModel', 'model'));
    }

    /**
     * @param int|string $id
     *
     * @return array|string|Response
     * @throws InvalidConfigException
     */
    public function actionUpdate($id)
    {
        $model = $this->getModel($id, Expense::class);

        if (!($model instanceof Expense)) {
            return $model;
        }

        $model->scenario = 'admin/update';

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param null|string|int $customer_id
     *
     * @return array|string|Response
     */
    public function actionAdd($customer_id = null)
    {
        $model = new Expense([
            'scenario' => 'admin/add',
            'customer_id' => $customer_id,
        ]);

        if (!Common::isEmpty($customer_id) && !$model->getCustomer()->exists()) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Customer'),
            ]));
        }

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param integer        $id
     * @param string|Expense $modelClass
     * @param null|Closure   $queryFilter
     *
     * @return string|Response|Expense
     * @throws InvalidConfigException
     */
    public function getModel($id, $modelClass = Expense::class, $queryFilter = null)
    {
        $query = $modelClass::find()->andWhere(['id' => $id]);

        if ($queryFilter instanceof Closure) {
            call_user_func($queryFilter, $query, $id, $modelClass);
        }

        $model = $query->one();

        if (!$model) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Expense'),
            ]));
        }

        return $model;
    }


    /**
     * @param int|string $id
     *
     * @return array|string|Response
     *
     * @throws InvalidConfigException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Expense)) {
            return $model;
        }

        if ($model->delete()) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully deleted', [
                'object' => Yii::t('app', 'Expense'),
                'object_name' => $model->name,
            ]));

            if (Lazy::isLazyModalRequest()) {
                Lazy::close();

                return '';
            }
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to delete {object}', [
                'object' => Yii::t('app', 'Expense'),
            ]));
        }

        return $this->goBack(['index']);
    }


    /**
     * @return array|string|Response
     *
     * @throws InvalidConfigException
     * @throws Throwable
     *
     */
    public function actionBulkDelete()
    {
        $ids = (array) Yii::$app->request->post('id', []);

        $total = Expense::find()->andWhere(['id' => $ids])->count();

        if (count($ids) < $total) {
            return $this->notFound(Yii::t('app', 'Some {object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Expense'),
            ]));
        }

        if (Expense::bulkDelete($ids)) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{number} {object} successfully deleted', [
                'number' => count($ids),
                'object' => Yii::t('app', 'Expenses'),
            ]));
        }

        return $this->goBack(['index']);
    }

    /**
     * @param Expense[] $expenses
     * @param null      $invoice_id
     *
     * @throws InvalidConfigException
     */
    public function temporaryAddToInvoice($expenses, $invoice_id = null)
    {
        $itemsData = Json::decode(Yii::$app->request->post('models'));

        if ($invoice_id) {
            $invoice = Invoice::find()->andWhere(['invoice_id' => $invoice_id])->one();
        } else {
            $invoice = new Invoice([
                'scenario' => 'admin/temp',
            ]);
        }

        $invoiceData = Json::decode(Yii::$app->request->post('invoice'));
        $invoice->load($invoiceData, '');

        $invoiceItems = [];

        foreach ($expenses AS $expense) {
            $invoiceItem = new InvoiceItem([
                'type' => 'expense',
                'scenario' => 'admin/temp/add',
                'params' => [
                    'expense_id' => $expense->id,
                ],
                'price' => $expense->total,
                'amount' => 1,
                'name' => $expense->name,
            ]);

            $invoiceItem->invoice = $invoice;

            $invoiceItem->tax_inputs = [];

            foreach ($expense->taxes AS $tax) {
                $invoiceItem->tax_inputs[] = [
                    'tax_id' => $tax->tax_id,
                ];
            }

            InvoiceItemController::getDummyModel($invoiceItem);

            $id = '__' . rand(1, 999999);

            $invoiceItem->normalizeAttributes();

            $invoiceItems[$id] = [
                'model' => ArrayHelper::toArray($invoiceItem),
                'row' => $this->renderPartial('@modules/finance/views/admin/invoice-item/components/item-row', [
                    'model' => $invoiceItem,
                ]),
            ];
            $itemsData[$id] = ArrayHelper::toArray($invoiceItem);
        }

        InvoiceItemController::getInvoiceDummyModel($invoice, $itemsData);

        Lazy::close();
        LazyResponse::$lazyData['temp'] = Yii::$app->request->get('temp');
        LazyResponse::$lazyData['rows'] = ArrayHelper::toArray($invoiceItems);
        LazyResponse::$lazyData['footer'] = $this->renderPartial('@modules/finance/views/admin/invoice-item/components/item-summary', [
            'model' => $invoice,
        ]);

        return;
    }

    /**
     * @param Expense[] $expenses
     * @param Invoice   $invoice
     *
     * @return bool|void
     * @throws Throwable
     * @throws Exception
     */
    public function addToInvoice($expenses, $invoice)
    {
        if (!Expense::addAllToInvoice($expenses, $invoice)) {
            return false;
        }

        Lazy::close();

        $invoice->refresh();

        LazyResponse::$lazyData['footer'] = $this->renderPartial('@modules/finance/views/admin/invoice-item/components/item-summary', [
            'model' => $invoice,
        ]);

        LazyResponse::$lazyData['rows'] = [];

        foreach ($expenses AS $expense) {
            $expense->refresh();

            LazyResponse::$lazyData['rows'][] = $this->renderPartial('@modules/finance/views/admin/invoice-item/components/item-row', [
                'model' => $expense->invoiceItem,
            ]);
        }

        return;
    }

    /**
     * @param null|int|string $invoice_id
     * @param null|int|string $temp
     *
     * @return bool|string|void
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function actionBillablePicker($invoice_id = null, $temp = null)
    {
        $invoice = !$temp ? Invoice::find()->andWhere(['id' => $invoice_id])->one() : null;
        $invoiceTemp = Json::decode(Yii::$app->request->post('invoice'));
        $customerId = !$temp ? $invoice->customer_id : $invoiceTemp['customer_id'];

        if (($expenseIds = Yii::$app->request->post('expenses'))) {
            $expenses = Expense::find()->readyToInvoiced()
                ->andWhere([
                    'expense.id' => $expenseIds,
                    'expense.customer_id' => $customerId,
                ])
                ->all();

            if ($temp) {
                return $this->temporaryAddToInvoice($expenses, $invoice_id);
            }

            return $this->addToInvoice($expenses, $invoice);
        }


        $searchModel = new ExpenseSearch();

        $searchModel->getQuery()->readyToInvoiced()->andWhere(['expense.customer_id' => $customerId]);

        $searchModel->apply(Yii::$app->request->get());

        return $this->render('billable-picker', compact('searchModel', 'invoice'));
    }

    public function temporaryUpdateInvoiceItem($invoice_id = null, $temp = false)
    {
        $model = new ExpenseInvoiceItem([
            'scenario' => intval($temp) ? 'admin/temp/update' : 'admin/update',
            'type' => 'expense',
            'invoice_id' => $invoice_id,
        ]);

        if ($invoice_id) {
            $model->invoice = Invoice::find()->andWhere(['invoice_id' => $invoice_id])->one();
        } else {
            $model->invoice = new Invoice([
                'scenario' => 'admin/temp',
            ]);
        }

        $data = Json::decode(Yii::$app->request->post('model'));
        $invoiceData = Json::decode(Yii::$app->request->post('invoice'));
        $model->invoice->load($invoiceData, '');
        $model->load($data, '');

        $model->amount = isset($data['amount']) ? $data['amount'] : 0;
        $model->sub_total = isset($data['sub_total']) ? $data['sub_total'] : 0;
        $model->grand_total = isset($data['grand_total']) ? $data['sub_total'] : 0;
        $model->tax_inputs = isset($data['tax_inputs']) ? $data['tax_inputs'] : [];
        $model->price = isset($data['price']) ? $data['price'] : 0;
        $model->params = isset($data['params']) ? $data['params'] : [];

        if ($invoice_id) {
            $invoice = Invoice::find()->andWhere(['invoice_id' => $invoice_id])->one();
        } else {
            $invoice = new Invoice([
                'scenario' => 'admin/temp',
            ]);
        }

        $model->invoice = $invoice;

        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post())) {
            if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                return Form::validate($model);
            }

            InvoiceItemController::getDummyModel($model);

            $model->normalizeAttributes();

            $itemsData = Json::decode(Yii::$app->request->post('models'));

            $itemsData[$temp] = ArrayHelper::toArray($model);

            InvoiceItemController::getInvoiceDummyModel($model->invoice, $itemsData);

            Lazy::close();

            LazyResponse::$lazyData['temp'] = $temp;

            LazyResponse::$lazyData['rows'] = [
                $temp => [
                    'model' => ArrayHelper::toArray($model),
                    'row' => $this->renderPartial('@modules/finance/views/admin/invoice-item/components/item-row', compact('model')),
                ],
            ];

            LazyResponse::$lazyData['footer'] = $this->renderPartial('@modules/finance/views/admin/invoice-item/components/item-summary', [
                'model' => $model->invoice,
            ]);

            return;
        }

        return $this->render('invoice-item-modify', compact('model'));
    }

    public function actionUpdateInvoiceItem($invoice_id = null, $temp = false)
    {
        if ($temp) {
            return $this->temporaryUpdateInvoiceItem($invoice_id, $temp);
        }

        $model = ExpenseInvoiceItem::find()->andWhere(['id' => Yii::$app->request->get('id')])->one();
        $model->scenario = 'admin/temp/update';

        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post())) {
            if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                return Form::validate($model);
            }

            if ($model->save()) {
                Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully saved', [
                    'object' => Yii::t('app', 'Item'),
                    'object_name' => $model->name,
                ]));
            } elseif ($model->hasErrors()) {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Some of the information you entered is invalid'));
            } else {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to save {object}', [
                    'object' => Yii::t('app', 'Item'),
                ]));
            }

            Lazy::close();
            LazyResponse::$lazyData['temp'] = $temp;
            LazyResponse::$lazyData['rows'] = [$this->renderPartial('@modules/finance/views/admin/invoice-item/components/item-row', compact('model'))];
            LazyResponse::$lazyData['footer'] = $this->renderPartial('@modules/finance/views/admin/invoice-item/components/item-summary', [
                'model' => $model->invoice,
            ]);

            return;
        }

        return $this->render('invoice-item-modify', compact('model'));
    }

    public function actionAddToInvoice($id)
    {
        $model = $this->getModel($id);
        $formModel = new DynamicModel([
            'invoice_id' => null,
        ]);

        $formModel->addRule('invoice_id', 'required');
        $formModel->addRule('invoice_id', 'exist', [
            'targetClass' => Invoice::class,
            'targetAttribute' => ['invoice_id' => 'id'],
            'filter' => function ($query) use ($model) {
                /** @var InvoiceQuery $query */

                return $query->andWhere([
                    'invoice.customer_id' => $model->customer_id,
                    'invoice.status' => Invoice::STATUS_DRAFT,
                ]);
            },
        ]);

        if ($formModel->load(Yii::$app->request->post())) {
            if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                return Form::validate($formModel);
            }

            if (!$formModel->validate()) {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Some of the information you entered is invalid'));
            } elseif ($model->addToInvoice($formModel->invoice_id)) {
                $invoice = Invoice::find()->andWhere(['id' => $formModel->invoice_id])->one();

                Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully added to invoice {invoice}', [
                    'object' => Yii::t('app', 'Expense'),
                    'object_name' => $model->name,
                    'invoice' => $invoice->number,
                ]));

                if (Lazy::isLazyModalRequest() || Lazy::isLazyInsideModalRequest()) {
                    Lazy::close();

                    return;
                }
            } else {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to add {object} to invoice', [
                    'object' => Yii::t('app', 'Expense'),
                ]));
            }
        }

        return $this->render('add-to-invoice', compact('model', 'formModel'));
    }

    /**
     * @return array
     *
     * @throws InvalidConfigException
     * @throws MethodNotAllowedHttpException
     */
    public function actionAutoComplete()
    {
        if (!Yii::$app->request->isAjax) {
            throw new MethodNotAllowedHttpException('This URL only serve ajax request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $searchModel = new ExpenseSearch();

        return $searchModel->autoComplete(Yii::$app->request->queryParams);
    }
}
