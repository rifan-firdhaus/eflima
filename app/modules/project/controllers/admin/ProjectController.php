<?php namespace modules\project\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\models\forms\history\HistorySearch;
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
     * @param string $action
     *
     * @return Project|string|Response
     * @throws InvalidConfigException
     */
    public function actionView($id, $action = 'detail')
    {
        $model = $this->getModel($id);

        if (!($model instanceof Project)) {
            return $model;
        }

        switch ($action) {
            case 'task':
                return $this->task($model);
            case 'task-timer':
                return $this->taskTimer($model);
            case 'invoice':
                return $this->invoice($model);
            case 'payment':
                return $this->payment($model);
            case 'expense':
                return $this->expense($model);
            case 'ticket':
                return $this->ticket($model);
            case 'event':
                return $this->event($model);
            case 'milestone':
                return $this->milestone($model);
            case 'history':
                return $this->history($model);
            case 'discussion':
                return $this->discussion($model);
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
     * @param Project $model
     *
     * @return string
     */
    public function milestone($model)
    {
        return $this->render('milestone', compact('model'));
    }

    /**
     * @param Project $model
     *
     * @return string
     */
    public function history($model)
    {
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
     * @param Project $model
     *
     * @return string
     */
    public function invoice($model)
    {
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
     * @param Project $model
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function expense($model)
    {
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
     * @param Project $model
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function discussion($model)
    {
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
     * @param Project $model
     *
     * @return string
     */
    public function ticket($model)
    {
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
     * @param Project $model
     *
     * @return string|array
     *
     * @throws InvalidConfigException
     */
    public function event($model)
    {
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
     * @param Project $model
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function payment($model)
    {
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
     * @param Project $model
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function task($model)
    {
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
     * @param Project $model
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function taskTimer($model)
    {

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
