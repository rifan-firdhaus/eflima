<?php namespace modules\finance\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\models\forms\history\HistorySearch;
use modules\account\models\forms\staff\StaffSearch;
use modules\account\models\queries\HistoryQuery;
use modules\account\models\Staff;
use modules\account\models\StaffAccount;
use modules\account\web\admin\Controller;
use modules\calendar\models\Event;
use modules\core\helpers\Common;
use modules\crm\models\Customer;
use modules\crm\models\Lead;
use modules\finance\components\ProposalRelation;
use modules\finance\models\forms\proposal\ProposalBulkSetStatus;
use modules\finance\models\forms\proposal\ProposalSearch;
use modules\finance\models\Proposal;
use modules\finance\models\ProposalItem;
use modules\task\models\forms\task\TaskSearch;
use modules\task\models\Task;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\lazy\Lazy;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\StaleObjectException;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ProposalController extends Controller
{
    public $viewMenu = [
        'detail' => [
            'route' => ['/finance/admin/proposal/detail'],
            'role' => 'admin.proposal.view.detail',
        ],
        'task' => [
            'route' => ['/finance/admin/proposal/task'],
            'role' => 'admin.proposal.view.task',
        ],
        'history' => [
            'route' => ['/finance/admin/proposal/history'],
            'role' => 'admin.proposal.view.history',
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
                'roles' => ['admin.proposal.list'],
                'matchCallback' => function () {
                    return Yii::$app->request->get('view', 'default') === 'default';
                },
            ],
            [
                'allow' => true,
                'actions' => ['index'],
                'verbs' => ['GET'],
                'roles' => ['admin.customer.view.proposal'],
                'matchCallback' => function () {
                    return Yii::$app->request->get('view', 'default') === 'customer';
                },
            ],
            [
                'allow' => true,
                'actions' => ['index'],
                'verbs' => ['GET'],
                'roles' => ['admin.lead.view.proposal'],
                'matchCallback' => function () {
                    return Yii::$app->request->get('view', 'default') === 'lead';
                },
            ],
            [
                'allow' => true,
                'actions' => ['add'],
                'verbs' => ['GET', 'POST'],
                'roles' => ['admin.proposal.add'],
            ],
            [
                'allow' => true,
                'actions' => ['update', 'save-content'],
                'verbs' => ['GET', 'POST', 'PATCH'],
                'roles' => ['admin.proposal.update'],
            ],
            [
                'allow' => true,
                'actions' => ['detail'],
                'verbs' => ['GET'],
                'roles' => ['admin.proposal.view.detail'],
            ],
            [
                'allow' => true,
                'actions' => ['task'],
                'verbs' => ['GET'],
                'roles' => ['admin.proposal.view.task'],
            ],
            [
                'allow' => true,
                'actions' => ['event'],
                'verbs' => ['GET'],
                'roles' => ['admin.proposal.view.event'],
            ],
            [
                'allow' => true,
                'actions' => ['history'],
                'verbs' => ['GET'],
                'roles' => ['admin.proposal.view.history'],
            ],
            [
                'allow' => true,
                'actions' => ['delete', 'bulk-delete'],
                'verbs' => ['DELETE', 'POST'],
                'roles' => ['admin.proposal.delete'],
            ],
            [
                'allow' => true,
                'actions' => ['change-status','bulk-set-status'],
                'verbs' => ['POST'],
                'roles' => ['admin.proposal.status'],
            ],
            [
                'allow' => true,
                'actions' => ['unassign', 'assign'],
                'verbs' => ['POST'],
                'roles' => ['admin.proposal.assignee'],
            ],
            [
                'allow' => true,
                'actions' => ['model-input'],
                'verbs' => ['GET'],
                'roles' => ['admin.proposal.update', 'admin.proposal.add'],
            ],
            [
                'allow' => true,
                'actions' => [
                    'view',
                    'auto-complete',
                    'staff-assignable-auto-complete',
                ],
                'verbs' => ['GET'],
                'roles' => ['@'],
            ],
            [
                'allow' => true,
                'actions' => ['all-history'],
                'verbs' => ['GET'],
                'roles' => ['admin.proposal.history'],
            ],
            [
                'allow' => true,
                'actions' => ['generate'],
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

        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;

        $searchModel = new ProposalSearch([
            'currentStaff' => $account->profile,
        ]);

        if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $searchModel->load($params);

            return Form::validate($searchModel);
        }

        switch ($view) {
            case 'customer':
                $customerId = Yii::$app->request->get('customer_id');

                if (!$customerId) {
                    throw new BadRequestHttpException('Missing required parameter: customer_id');
                }

                return $this->indexOfCustomer($customerId, $searchModel);
            case 'lead':
                $leadId = Yii::$app->request->get('lead_id');

                if (!$leadId) {
                    throw new BadRequestHttpException('Missing required parameter: lead_id');
                }

                return $this->indexOfLead($leadId, $searchModel);
        }

        $searchModel->getQuery()->with(['status']);
        $searchModel->apply($params);

        return $this->render('index', compact('searchModel'));
    }

    /**
     * @param int|string     $customerId
     * @param ProposalSearch $searchModel
     *
     * @return array|string|Response
     *
     * @throws InvalidConfigException
     */
    public function indexOfCustomer($customerId, $searchModel)
    {
        $params = Yii::$app->request->queryParams;
        $customer = Customer::find()->andWhere(['id' => $customerId])->one();

        if (!$customer) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Customer'),
            ]));
        }

        $searchModel->params['model_id'] = $customerId;
        $searchModel->params['model'] = 'customer';

        $searchModel->getQuery()->with(['status']);
        $searchModel->apply($params);

        return $this->render('index-customer', compact('searchModel', 'customer'));
    }

    /**
     * @param int|string     $leadId
     * @param ProposalSearch $searchModel
     *
     * @return array|string|Response
     *
     * @throws InvalidConfigException
     */
    public function indexOfLead($leadId, $searchModel)
    {
        $params = Yii::$app->request->queryParams;
        $lead = Lead::find()->andWhere(['id' => $leadId])->one();

        if (!$lead) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Lead'),
            ]));
        }

        $searchModel->params['model_id'] = $leadId;
        $searchModel->params['model'] = 'lead';

        $searchModel->getQuery()->with(['status']);
        $searchModel->apply($params);

        return $this->render('index-lead', compact('searchModel', 'lead'));
    }

    /**
     * @param Proposal        $model
     * @param                 $data
     *
     * @return string|array
     *
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    protected function modify($model, $data)
    {
        $model->loadDefaultValues();

        if ($model->load($data)) {
            if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                return Form::validate($model);
            }


            $itemData = Json::decode(Yii::$app->request->post('items'));
            $model->itemModels = $this->getItemModels($itemData, $model);
            if (!$model->validate() || !Model::validateMultiple($model->itemModels)) {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Some of the information you entered is invalid'));
            } elseif ($model->save()) {
                Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully saved', [
                    'object' => Yii::t('app', 'Proposal'),
                    'object_name' => $model->title,
                ]));

                if (Lazy::isLazyModalRequest() || Lazy::isLazyInsideModalRequest()) {
                    Lazy::close();

                    return;
                }

                return $this->redirect(['update', 'id' => $model->id]);
            } else {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to save {object}', [
                    'object' => Yii::t('app', 'Proposal'),
                ]));
            }
        }

        return $this->render('modify', compact('model'));
    }

    /**
     * @param array    $data
     * @param Proposal $proposal
     *
     * @return array
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function getItemModels($data, $proposal)
    {
        $models = [];

        foreach ($data AS $item) {
            if (!empty($item['id']) && !$proposal->isNewRecord) {
                $model = ProposalItem::find()->andWhere(['id' => $item['id'], 'proposal_id' => $proposal->id])->one();

                if (!$model) {
                    throw new NotFoundHttpException('Can\'t find proposal item');
                }

                $model->scenario = 'admin/update';
            } else {
                $model = new ProposalItem([
                    'scenario' => 'admin/add',
                ]);
            }

            $model->loadDefaultValues();
            $model->setAttributes($item);

            $models[] = $model;
        }

        return $models;
    }

    public function actionSaveContent($id)
    {
        $model = $this->getModel($id);

        if (!$model instanceof Proposal) {
            return $model;
        }

        $model->content = Yii::$app->request->post('content');

        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$model->save(false)) {
            return [
                'success' => false,
                'messages' => [
                    'danger' => [
                        Yii::t('app', 'Failed to save {object}', [
                            'object' => Yii::t('app', 'Content'),
                        ]),
                    ],
                ],
            ];
        }

        return [
            'success' => true,
            'messages' => [
                'success' => [
                    Yii::t('app', '{object} successfully deleted', ['object' => Yii::t('app', 'Checklist')]),
                ],
            ],
        ];
    }

    /**
     * @param int|string $id
     *
     * @return array|string|Response
     *
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function actionUpdate($id)
    {
        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;
        $model = $this->getModel($id, Proposal::class);

        if (!($model instanceof Proposal)) {
            return $model;
        }

        $model->scenario = 'admin/update';
        $model->assignee_ids = $model->getAssigneesRelationship()->select('assignees_of_proposal.assignee_id')->createCommand()->queryColumn();
        $model->assignor_id = $account->profile->id;

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param null|int|string $status_id
     *
     * @return array|string|Response
     */
    public function actionAdd($status_id = null)
    {
        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;
        $model = new Proposal([
            'scenario' => 'admin/add',
            'status_id' => $status_id,
            'assignor_id' => $account->profile->id,
        ]);

        if (!Common::isEmpty($status_id) && !$model->getStatus()->exists()) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Status'),
            ]));
        }

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @return string
     */
    public function actionAllHistory()
    {
        $searchModel = new HistorySearch([
            'params' => [
                'model' => Proposal::class,
            ],
        ]);

        return $this->render('all-history', compact('searchModel'));
    }

    /**
     * @param string|int $id
     *
     * @return Response
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
     * @return string|Response
     *
     * @throws InvalidConfigException
     */
    public function actionDetail($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Proposal)) {
            return $model;
        }

        return $this->render('view', compact('model'));
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

        if (!($model instanceof Proposal)) {
            return $model;
        }

        $historySearchParams = [
            'model' => Proposal::class,
            'model_id' => $model->id,
        ];

        if (Yii::$app->hasModule('task')) {
            $historySearchParams['models'][] = function ($query) use ($model) {
                /** @var HistoryQuery $query */

                $query->leftJoin(Task::tableName(), [
                    'task.id' => new Expression('[[history.model_id]]'),
                    'history.model' => Task::class,
                    'task.model_id' => $model->id,
                    'task.model' => 'proposal',
                ]);

                return ['IS NOT', 'task.id', null];
            };
        }

        if (Yii::$app->hasModule('calendar')) {
            $historySearchParams['models'][] = function ($query) use ($model) {
                /** @var HistoryQuery $query */

                $query->leftJoin(Event::tableName(), [
                    'history.model' => Event::class,
                    'event.id' => new Expression('[[history.model_id]]'),
                    'event.model' => 'proposal',
                    'event.model_id' => $model->id,
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
    public function actionTask($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Proposal)) {
            return $model;
        }

        $taskSearchModel = new TaskSearch([
            'params' => [
                'model' => 'proposal',
                'model_id' => $model->id,
            ],
        ]);

        $taskSearchModel->apply(Yii::$app->request->get());

        return $this->render('task', compact('model', 'taskSearchModel'));
    }

    /**
     * @param integer         $id
     * @param string|Proposal $modelClass
     * @param null|Closure    $queryFilter
     *
     * @return string|Response|Proposal
     * @throws InvalidConfigException
     */
    public function getModel($id, $modelClass = Proposal::class, $queryFilter = null)
    {
        $query = $modelClass::find()->andWhere(['id' => $id]);

        if ($queryFilter instanceof Closure) {
            call_user_func($queryFilter, $query, $id, $modelClass);
        }

        $model = $query->one();

        if (!$model) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Proposal'),
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

        if (!($model instanceof Proposal)) {
            return $model;
        }

        if ($model->delete()) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully deleted', [
                'object' => Yii::t('app', 'Proposal'),
                'object_name' => $model->title,
            ]));

            if (Lazy::isLazyModalRequest()) {
                Lazy::close();

                return '';
            }
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to delete {object}', [
                'object' => Yii::t('app', 'Proposal'),
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

        $total = Proposal::find()->andWhere(['id' => $ids])->count();

        if (count($ids) < $total) {
            return $this->notFound(Yii::t('app', 'Some {object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Project'),
            ]));
        }

        if (Proposal::bulkDelete($ids)) {
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
        $model = new ProposalBulkSetStatus([
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
                    'object' => Yii::t('app', 'Proposal'),
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
                    'object' => Yii::t('app', 'Proposal'),
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

        if (!($model instanceof Proposal)) {
            return $model;
        }

        if ($model->changeStatus($status)) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{field} of {object_name} successfully changed', [
                'field' => Yii::t('app', 'Status'),
                'object_name' => $model->title,
            ]));
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to change {field} of {object}', [
                'object' => Yii::t('app', 'Proposal'),
                'field' => Yii::t('app', 'status'),
            ]));
        }

        return $this->goBack(['index']);
    }


    /**
     * @param $id
     * @param $staff_id
     *
     * @return array|Task|string|Response
     *
     * @throws InvalidConfigException
     *
     * @throws Exception
     */
    public function actionAssign($id, $staff_id)
    {
        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;
        $model = $this->getModel($id);

        if (!($model instanceof Proposal)) {
            return $model;
        }

        $staff = Staff::find()->andWhere(['id' => $staff_id])->one();

        if (!$staff) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Staff'),
            ]));
        }

        if ($model->assign($staff_id, $account->profile->id)) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{object} assigned to {staff}', [
                'staff' => $staff->name,
                'object' => Yii::t('app', 'Proposal'),
            ]));
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to assign {object}', [
                'object' => Yii::t('app', 'Proposal'),
            ]));
        }

        return $this->goBack(['index']);
    }


    /**
     * @param $id
     * @param $staff_id
     *
     * @return array|Task|string|Response
     *
     * @throws InvalidConfigException
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionUnassign($id, $staff_id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Proposal)) {
            return $model;
        }

        $staff = Staff::find()->andWhere(['id' => $staff_id])->one();

        if (!$staff) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Proposal'),
            ]));
        }

        if ($model->unassign($staff_id)) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{staff} unassigned from {object}', [
                'staff' => $staff->name,
                'object' => Yii::t('app', 'Proposal'),
            ]));
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to unassigned staff from {object}', [
                'object' => Yii::t('app', 'Proposal'),
            ]));
        }

        return $this->goBack(['index']);
    }

    /**
     * @param $id
     *
     * @return array|Proposal|string|Response
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws MethodNotAllowedHttpException
     */
    public function actionStaffAssignableAutoComplete($id)
    {

        if (!Yii::$app->request->isAjax) {
            throw new MethodNotAllowedHttpException('This URL only serve ajax request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $searchModel = new StaffSearch();

        $model = $this->getModel($id);

        if (!$model instanceof Proposal) {
            return $model;
        }

        $assigned = $model->getAssigneesRelationship()
            ->select('assignee_id')
            ->createCommand()
            ->queryColumn();

        $searchModel->getQuery()
            ->andWhere(['NOT IN', 'staff.id', $assigned]);

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

        $searchModel = new ProposalSearch();

        return $searchModel->autoComplete(Yii::$app->request->queryParams);
    }

    /**
     * @param $model
     *
     * @return array
     *
     * @throws InvalidConfigException
     */
    public function actionModelInput($model)
    {
        $proposal = new Proposal();
        $relation = ProposalRelation::get($model);

        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'input' => $this->view->renderAjaxContent($relation->pickerInput($proposal, 'model_id')),
        ];
    }
}
