<?php namespace modules\project\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\models\forms\history\HistorySearch;
use modules\account\models\forms\staff\StaffSearch;
use modules\account\models\queries\HistoryQuery;
use modules\account\web\admin\Controller;
use modules\calendar\models\Event;
use modules\calendar\models\forms\event\EventSearch;
use modules\crm\models\Customer;
use modules\file_manager\web\UploadedFile;
use modules\finance\models\Expense;
use modules\finance\models\forms\expense\ExpenseSearch;
use modules\finance\models\forms\invoice\InvoiceSearch;
use modules\finance\models\forms\invoice_payment\InvoicePaymentSearch;
use modules\finance\models\Invoice;
use modules\project\models\forms\project\ProjectBulkSetStatus;
use modules\project\models\forms\project\ProjectSearch;
use modules\project\models\forms\project_discussion_topic\ProjectDiscussionTopicSearch;
use modules\project\models\Project;
use modules\project\models\ProjectDiscussionTopic;
use modules\support\models\forms\ticket\TicketSearch;
use modules\support\models\Ticket;
use modules\task\models\forms\task\TaskSearch;
use modules\task\models\forms\task_timer\TaskTimerSearch;
use modules\task\models\query\TaskQuery;
use modules\task\models\Task;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\lazy\Lazy;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\Response;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ProjectController extends Controller
{
    public $viewMenu = [
        'detail' => [
            'route' => ['/project/admin/project/detail'],
            'role' => 'admin.project.view.detail',
        ],
        'task' => [
            'route' => ['/project/admin/project/task'],
            'role' => 'admin.project.view.task',
        ],
        'milestone' => [
            'route' => ['/project/admin/project/milestone'],
            'role' => 'admin.project.view.milestone.list',
        ],
        'timer' => [
            'route' => ['/project/admin/project/task-timer'],
            'role' => 'admin.project.view.task-timer',
        ],
        'invoice' => [
            'route' => ['/project/admin/project/invoice'],
            'role' => 'admin.project.view.invoice',
        ],
        'payment' => [
            'route' => ['/project/admin/project/payment'],
            'role' => 'admin.project.view.payment',
        ],
        'expense' => [
            'route' => ['/project/admin/project/expense'],
            'role' => 'admin.project.view.expense',
        ],
        'ticket' => [
            'route' => ['/project/admin/project/ticket'],
            'role' => 'admin.project.view.ticket',
        ],
        'event' => [
            'route' => ['/project/admin/project/event'],
            'role' => 'admin.project.view.event',
        ],
        'discussion' => [
            'route' => ['/project/admin/project/discussion'],
            'role' => 'admin.project.view.discussion',
        ],
        'history' => [
            'route' => ['/project/admin/project/history'],
            'role' => 'admin.project.view.history',
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
                'roles' => ['admin.project.list'],
            ],
            [

                'allow' => true,
                'actions' => ['add'],
                'verbs' => ['GET', 'POST'],
                'roles' => ['admin.project.add'],
            ],
            [

                'allow' => true,
                'actions' => ['update'],
                'verbs' => ['GET', 'POST', 'PATCH'],
                'roles' => ['admin.project.update'],
            ],
            [

                'allow' => true,
                'actions' => ['delete', 'bulk-delete'],
                'verbs' => ['DELETE', 'POST'],
                'roles' => ['admin.project.delete'],
            ],
            [

                'allow' => true,
                'actions' => ['change-status','bulk-set-status'],
                'verbs' => ['POST'],
                'roles' => ['admin.project.status'],
            ],
            [

                'allow' => true,
                'actions' => ['detail'],
                'verbs' => ['GET'],
                'roles' => ['admin.project.view.detail'],
            ],
            [

                'allow' => true,
                'actions' => ['invoice'],
                'verbs' => ['GET'],
                'roles' => ['admin.project.view.invoice'],
            ],
            [

                'allow' => true,
                'actions' => ['expense'],
                'verbs' => ['GET'],
                'roles' => ['admin.project.view.expense'],
            ],
            [

                'allow' => true,
                'actions' => ['ticket'],
                'verbs' => ['GET'],
                'roles' => ['admin.project.view.ticket'],
            ],
            [

                'allow' => true,
                'actions' => ['task-timer'],
                'verbs' => ['GET'],
                'roles' => ['admin.project.view.task-timer'],
            ],
            [

                'allow' => true,
                'actions' => ['discussion'],
                'verbs' => ['GET'],
                'roles' => ['admin.project.view.discussion'],
            ],
            [

                'allow' => true,
                'actions' => ['milestone'],
                'verbs' => ['GET'],
                'roles' => ['admin.project.view.milestone.list'],
            ],
            [

                'allow' => true,
                'actions' => ['event'],
                'verbs' => ['GET'],
                'roles' => ['admin.project.view.milestone.list'],
            ],
            [

                'allow' => true,
                'actions' => ['payment'],
                'verbs' => ['GET'],
                'roles' => ['admin.project.view.payment'],
            ],
            [

                'allow' => true,
                'actions' => ['task'],
                'verbs' => ['GET'],
                'roles' => ['admin.project.view.task'],
            ],
            [

                'allow' => true,
                'actions' => ['history'],
                'verbs' => ['GET'],
                'roles' => ['admin.project.view.history'],
            ],
            [
                'allow' => true,
                'actions' => [
                    'view',
                    'auto-complete',
                    'staff-invitable-auto-complete',
                ],
                'verbs' => ['GET'],
                'roles' => ['@'],
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

        $searchModel = new ProjectSearch();

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
     * @param ProjectSearch $searchModel
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

        $searchModel->getQuery()->andWhere(['project.customer_id' => $customerId]);

        $searchModel->params['customer_id'] = $customerId;

        return $this->render('index-customer', compact('searchModel', 'customer'));
    }

    /**
     * @param        $id
     *
     * @return Response
     *
     */
    public function actionView($id)
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

    /**
     * @param $id
     *
     * @return Project|string|Response
     *
     * @throws InvalidConfigException
     */
    public function actionDetail($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Project)) {
            return $model;
        }

        $taskSearchModel = $this->taskSearchModel($model);
        $taskQuery = clone $taskSearchModel->getQuery();

        $invoiceSearchModel = new InvoiceSearch([
            'params' => [
                'project_id' => $model->id,
                'customer_id' => $model->customer_id,
            ],
        ]);

        $taskTimerSearchModel = new TaskTimerSearch();
        $taskTimerSearchModel->getQuery()->andWhere(['IN', 'task_timer.task_id', $taskQuery->select('task.id')]);

        $ticketSearchModel = new TicketSearch([
            'params' => [
                'project_id' => $model->id,
                'customer_id' => $model->customer_id,
            ],
        ]);
        $ticketSearchModel->getQuery()->andWhere(['ticket.project_id' => $model->id]);

        return $this->render('view', compact('model', 'ticketSearchModel', 'taskSearchModel', 'invoiceSearchModel', 'taskTimerSearchModel'));
    }

    /**
     * @param string|int $id
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function actionMilestone($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Project)) {
            return $model;
        }

        return $this->render('milestone', compact('model'));
    }

    /**
     * @param string|int $id
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function actionHistory($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Project)) {
            return $model;
        }

        $historySearchParams = [
            'model' => Project::class,
            'model_id' => $model->id,
            'models' => [
                function ($query) use ($model) {
                    /** @var HistoryQuery $query */

                    $query->leftJoin(Task::tableName(), [
                        'task.id' => new Expression('[[history.model_id]]'),
                        'history.model' => Task::class,
                    ]);

                    return ['task.model_id' => $model->id, 'task.model' => 'project'];
                },
            ],
        ];

        if (Yii::$app->hasModule('finance')) {
            $historySearchParams['models'][] = function ($query) use ($model) {
                /** @var HistoryQuery $query */

                $query->leftJoin(Expense::tableName(), [
                    'expense.id' => new Expression('[[history.model_id]]'),
                    'history.model' => Expense::class,
                    'expense.project_id' => $model->id,
                ]);
                $query->leftJoin(Invoice::tableName(), [
                    'invoice.id' => new Expression('[[history.model_id]]'),
                    'history.model' => Invoice::class,
                    'invoice.project_id' => $model->id,
                ]);

                $query->leftJoin(['finance_task' => Task::tableName()], [
                    'finance_task.id' => new Expression('[[history.model_id]]'),
                    'history.model' => Task::class,
                ]);
                $query->leftJoin(['expense_of_task' => Expense::tableName()], [
                    'finance_task.model' => 'expense',
                    'expense_of_task.id' => new Expression('[[finance_task.model_id]]'),
                    'expense_of_task.project_id' => $model->id,
                ]);
                $query->leftJoin(['invoice_of_task' => Invoice::tableName()], [
                    'finance_task.model' => 'invoice',
                    'invoice_of_task.id' => new Expression('[[finance_task.model_id]]'),
                    'invoice_of_task.project_id' => $model->id,
                ]);

                return [
                    'OR',
                    ['IS NOT', 'invoice.id', null],
                    ['IS NOT', 'expense.id', null],
                    ['IS NOT', 'invoice_of_task.id', null],
                    ['IS NOT', 'expense_of_task.id', null],
                ];
            };
        }

        if (Yii::$app->hasModule('calendar')) {
            $historySearchParams['models'][] = function ($query) use ($model) {
                /** @var HistoryQuery $query */

                $query->leftJoin(Event::tableName(), [
                    'event.id' => new Expression('[[history.model_id]]'),
                    'event.model' => 'project',
                    'event.model_id' => $model->id,
                    'history.model' => Event::class,
                ]);

                return ['IS NOT', 'event.id', null];
            };
        }

        $historySearchModel = new HistorySearch([
            'params' => $historySearchParams,
        ]);


        return $this->render('history', compact('model', 'historySearchModel'));
    }

    /**
     * @param string|int $id
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function actionInvoice($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Project)) {
            return $model;
        }

        $searchModel = new InvoiceSearch([
            'params' => [
                'customer_id' => $model->customer_id,
                'project_id' => $model->id,
                'addUrl' => ['/finance/admin/invoice/add', 'customer_id' => $model->customer_id, 'project_id' => $model->id],
            ],
        ]);

        $searchModel->apply(Yii::$app->request->queryParams);

        return $this->render('invoice', compact('model', 'searchModel'));
    }

    /**
     * @param string|int $id
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function actionExpense($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Project)) {
            return $model;
        }

        $searchModel = new ExpenseSearch([
            'params' => [
                'customer_id' => $model->customer_id,
                'project_id' => $model->id,
                'addUrl' => ['/finance/admin/expense/add', 'customer_id' => $model->customer_id, 'project_id' => $model->id],
            ],
        ]);

        $searchModel->apply(Yii::$app->request->queryParams);

        return $this->render('expense', compact('model', 'searchModel'));
    }

    /**
     * @param string|int $id
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function actionDiscussion($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Project)) {
            return $model;
        }

        $searchModel = new ProjectDiscussionTopicSearch([
            'params' => [
                'project_id' => $model->id,
            ],
        ]);

        $currentTopicQuery = ProjectDiscussionTopic::find()->andWhere(['project_id' => $model->id]);

        if (($topicId = Yii::$app->request->get('topic_id', null))) {
            $currentTopicQuery->andWhere(['id' => $topicId]);
        }

        $currentTopic = $currentTopicQuery->one();

        return $this->render('discussion', compact('model', 'currentTopic', 'searchModel'));
    }

    /**
     * @param string|int $id
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function actionTicket($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Project)) {
            return $model;
        }

        $searchModel = new TicketSearch([
            'params' => [
                'customer_id' => $model->customer_id,
                'project_id' => $model->id,
                'addUrl' => ['/support/admin/ticket/add', 'customer_id' => $model->customer_id, 'project_id' => $model->id],
            ],
        ]);

        $searchModel->apply(Yii::$app->request->queryParams);

        return $this->render('ticket', compact('model', 'searchModel'));
    }

    /**
     * @param string|int $id
     *
     * @return string|array
     *
     * @throws InvalidConfigException
     */
    public function actionEvent($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Project)) {
            return $model;
        }

        $view = Yii::$app->request->get('view', 'default');
        $params = Yii::$app->request->queryParams;

        $searchModel = new EventSearch([
            'params' => [
                'view' => $view,
                'model' => 'project',
                'model_id' => $model->id,
                'addUrl' => [
                    '/calendar/admin/event/add',
                    'model' => 'project',
                    'model_id' => $model->id,
                ],
            ],
        ]);

        if ($view === 'calendar' && Yii::$app->request->get('query')) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return $searchModel->fullCalendar($params);
        }


        $searchModel->apply($params);

        return $this->render('event', compact('model', 'searchModel'));
    }

    /**
     * @param string|int $id
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function actionPayment($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Project)) {
            return $model;
        }

        $searchModel = new InvoicePaymentSearch([
            'params' => [
                'customer_id' => $model->customer_id,
                'project_id' => $model->id,
                'addUrl' => ['/finance/admin/invoice-payment/add', 'customer_id' => $model->customer_id, 'project_id' => $model->id],
            ],
        ]);

        $searchModel->apply(Yii::$app->request->queryParams);

        return $this->render('invoice-payment', compact('model', 'searchModel'));
    }

    /**
     * @param string|int $id
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function actionTask($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Project)) {
            return $model;
        }

        $searchModel = $this->taskSearchModel($model);

        $dataProvider = $searchModel->apply(Yii::$app->request->queryParams);

        return $this->render('task', compact(
            'searchModel', 'dataProvider', 'model'
        ));
    }

    /**
     * @param $model
     *
     * @return TaskSearch
     */
    public function taskSearchModel($model)
    {
        return new TaskSearch([
            'params' => [
                'model' => 'project',
                'model_id' => $model->id,
                'models' => [
                    function ($query) use ($model) {
                        /** @var TaskQuery $query */

                        $query->leftJoin(Ticket::tableName(), ['task.model_id' => new Expression('[[ticket.id]]'), 'task.model' => 'ticket']);

                        return ['ticket.project_id' => $model->id];
                    },
                    function ($query) use ($model) {
                        /** @var TaskQuery $query */

                        $query->leftJoin(Expense::tableName(), ['task.model_id' => new Expression('[[expense.id]]'), 'task.model' => 'expense']);

                        return ['expense.project_id' => $model->id];
                    },
                    function ($query) use ($model) {
                        /** @var TaskQuery $query */

                        $query->leftJoin(Invoice::tableName(), ['task.model_id' => new Expression('[[invoice.id]]'), 'task.model' => 'invoice']);

                        return ['invoice.project_id' => $model->id];
                    },
                ],
            ],
        ]);
    }

    /**
     * @param string|int $id
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function actionTaskTimer($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Project)) {
            return $model;
        }

        $searchModel = new TaskTimerSearch();

        $searchModel->getQuery()->andWhere(['IN', 'task_timer.task_id', $this->taskSearchModel($model)->getQuery()->select('task.id')]);

        $dataProvider = $searchModel->apply(Yii::$app->request->queryParams);

        return $this->render('task-timer', compact(
            'searchModel', 'dataProvider', 'model'
        ));
    }

    /**
     * @param Project    $model
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
                    'object' => Yii::t('app', 'Project'),
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
                    'object' => Yii::t('app', 'Project'),
                ]));
            }
        }

        return $this->render('modify', compact('model'));
    }

    /**
     * @param int|string $id
     *
     * @return array|string|Response
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function actionUpdate($id)
    {
        $model = $this->getModel($id, Project::class);

        if (!($model instanceof Project)) {
            return $model;
        }

        $model->scenario = 'admin/update';
        $model->member_ids = $model->getMembersRelationship()->select('members_of_project.staff_id')->createCommand()->queryColumn();

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param null|string|int $customer_id
     *
     * @return array|string|Response
     */
    public function actionAdd($customer_id = null)
    {
        $model = new Project([
            'scenario' => 'admin/add',
            'customer_id' => $customer_id,
        ]);

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param integer        $id
     * @param string|Project $modelClass
     * @param null|Closure   $queryFilter
     *
     * @return string|Response|Project
     * @throws InvalidConfigException
     */
    public function getModel($id, $modelClass = Project::class, $queryFilter = null)
    {
        $query = $modelClass::find()->andWhere(['id' => $id]);

        if ($queryFilter instanceof Closure) {
            call_user_func($queryFilter, $query, $id, $modelClass);
        }

        $model = $query->one();

        if (!$model) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Project'),
            ]));
        }

        return $model;
    }

    /**
     * @param int|string $id
     *
     * @return array|string|Response
     * @throws InvalidConfigException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Project)) {
            return $model;
        }

        if ($model->delete()) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully deleted', [
                'object' => Yii::t('app', 'Project'),
                'object_name' => $model->name,
            ]));

            if (Lazy::isLazyModalRequest()) {
                Lazy::close();

                return '';
            }
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to delete {object}', [
                'object' => Yii::t('app', 'Project'),
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

        $total = Project::find()->andWhere(['id' => $ids])->count();

        if (count($ids) < $total) {
            return $this->notFound(Yii::t('app', 'Some {object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Project'),
            ]));
        }

        if (Project::bulkDelete($ids)) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{number} {object} successfully deleted', [
                'number' => count($ids),
                'object' => Yii::t('app', 'Projects'),
            ]));
        }

        return $this->goBack(['index']);
    }


    /**
     * @return array|string|void|Response
     * @throws Throwable
     */
    public function actionBulkSetStatus()
    {
        $ids = (array) Yii::$app->request->post('id', []);
        $model = new ProjectBulkSetStatus([
            'ids' => $ids,
        ]);
        $data = Yii::$app->request->post();

        if ($model->load($data)) {
            if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                return Form::validate($model);
            }

            if ($model->save()) {
                Yii::$app->session->addFlash('success', Yii::t('app', '{number} {object} successfully updated', [
                    'number' => count($model->ids),
                    'object' => Yii::t('app', 'Projects'),
                ]));

                if (Lazy::isLazyModalRequest() || Lazy::isLazyInsideModalRequest()) {
                    Lazy::close();

                    return;
                }

                return $this->redirect(['index']);
            } elseif ($model->hasErrors()) {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Some of the information you entered is invalid'));
            } else {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to update {object}', [
                    'object' => Yii::t('app', 'Project'),
                ]));
            }
        }

        return $this->render('bulk-set-status', compact('model'));
    }

    /**
     * @param int|string $id
     * @param int        $status
     *
     * @return array|string|Response
     * @throws InvalidConfigException
     */
    public function actionChangeStatus($id, $status)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Project)) {
            return $model;
        }

        if ($model->changeStatus($status)) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{field} of {object_name} successfully changed', [
                'field' => Yii::t('app', 'Status'),
                'object_name' => $model->name,
            ]));
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to change {field} of {object}', [
                'object' => Yii::t('app', 'Project'),
                'field' => Yii::t('app', 'status'),
            ]));
        }

        return $this->goBack(['index']);
    }


    /**
     * @param $id
     *
     * @return array|Event|string|Response
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws MethodNotAllowedHttpException
     */
    public function actionStaffInvitableAutoComplete($id)
    {

        if (!Yii::$app->request->isAjax) {
            throw new MethodNotAllowedHttpException('This URL only serve ajax request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $searchModel = new StaffSearch();

        $model = $this->getModel($id);

        if (!$model instanceof Project) {
            return $model;
        }

        $invited = $model->getMembersRelationship()
            ->select('staff_id')
            ->createCommand()
            ->queryColumn();

        $searchModel->getQuery()
            ->andWhere(['NOT IN', 'staff.id', $invited]);

        return $searchModel->autoComplete(Yii::$app->request->queryParams);
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

        $searchModel = new ProjectSearch();

        return $searchModel->autoComplete(Yii::$app->request->queryParams);
    }
}
