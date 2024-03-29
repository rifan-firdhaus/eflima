<?php namespace modules\crm\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use Faker\Factory;
use modules\account\models\forms\history\HistorySearch;
use modules\account\models\forms\staff\StaffSearch;
use modules\account\models\queries\HistoryQuery;
use modules\account\models\Staff;
use modules\account\models\StaffAccount;
use modules\account\web\admin\Controller;
use modules\address\models\Country;
use modules\calendar\models\Event;
use modules\calendar\models\forms\event\EventSearch;
use modules\core\helpers\Common;
use modules\crm\models\forms\lead\LeadBulkReassign;
use modules\crm\models\forms\lead\LeadBulkSetStatus;
use modules\crm\models\forms\lead\LeadSearch;
use modules\crm\models\forms\lead_follow_up\LeadFollowUpSearch;
use modules\crm\models\Lead;
use modules\crm\models\LeadSource;
use modules\crm\models\LeadStatus;
use modules\task\models\forms\task\TaskSearch;
use modules\task\models\Task;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\lazy\Lazy;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\StaleObjectException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\Response;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class LeadController extends Controller
{
    public $viewMenu = [
        'detail' => [
            'route' => ['/crm/admin/lead/detail'],
            'role' => 'admin.lead.view.detail',
        ],
        'task' => [
            'route' => ['/crm/admin/lead/task'],
            'role' => 'admin.lead.view.task',
        ],
        'event' => [
            'route' => ['/crm/admin/lead/event'],
            'role' => 'admin.lead.view.event',
        ],
        'history' => [
            'route' => ['/task/admin/task/history'],
            'role' => 'admin.lead.view.history',
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
                'roles' => ['admin.lead.list'],
            ],
            [
                'allow' => true,
                'actions' => ['add'],
                'verbs' => ['GET', 'POST'],
                'roles' => ['admin.lead.add'],
            ],
            [
                'allow' => true,
                'actions' => ['update'],
                'verbs' => ['GET', 'POST', 'PATCH'],
                'roles' => ['admin.lead.update'],
            ],
            [
                'allow' => true,
                'actions' => ['detail'],
                'verbs' => ['GET'],
                'roles' => ['admin.lead.view.detail'],
            ],
            [
                'allow' => true,
                'actions' => ['task'],
                'verbs' => ['GET'],
                'roles' => ['admin.lead.view.task'],
            ],
            [
                'allow' => true,
                'actions' => ['event'],
                'verbs' => ['GET'],
                'roles' => ['admin.lead.view.event'],
            ],
            [
                'allow' => true,
                'actions' => ['history'],
                'verbs' => ['GET'],
                'roles' => ['admin.lead.view.history'],
            ],
            [
                'allow' => true,
                'actions' => ['delete', 'bulk-delete'],
                'verbs' => ['DELETE', 'POST'],
                'roles' => ['admin.lead.delete'],
            ],
            [
                'allow' => true,
                'actions' => ['change-status', 'bulk-set-status'],
                'verbs' => ['POST'],
                'roles' => ['admin.lead.status'],
            ],
            [
                'allow' => true,
                'actions' => ['unassign', 'assign', 'bulk-reassign'],
                'verbs' => ['POST'],
                'roles' => ['admin.lead.assignee'],
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
                'roles' => ['admin.lead.history'],
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
     * @return array|string|Response
     *
     * @throws InvalidConfigException
     */
    public function actionIndex()
    {
        $params = Yii::$app->request->queryParams;

        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;

        $searchModel = new LeadSearch([
            'currentStaff' => $account->profile,
        ]);

        if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $searchModel->load($params);

            return Form::validate($searchModel);
        }

        $searchModel->getQuery()->with(['status', 'country', 'assignees', 'source']);

        $searchModel->apply($params);

        return $this->render('index', compact('searchModel'));
    }

    /**
     * @param Lead            $model
     * @param                 $data
     *
     * @return string|array
     */
    protected function modify($model, $data)
    {
        $model->loadDefaultValues();

        if ($model->load($data)) {
            if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                return Form::validate($model);
            }

            if ($model->save()) {
                Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully saved', [
                    'object' => Yii::t('app', 'Lead'),
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
                    'object' => Yii::t('app', 'Lead'),
                ]));
            }
        }

        return $this->render('modify', compact('model'));
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

        $model = $this->getModel($id, Lead::class);

        if (!($model instanceof Lead)) {
            return $model;
        }

        $model->scenario = 'admin/update';
        $model->assignee_ids = $model->getAssigneesRelationship()->select('assignees_of_lead.assignee_id')->createCommand()->queryColumn();
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

        $model = new Lead([
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
                'model' => Lead::class,
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

    public function actionDetail($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Lead)) {
            return $model;
        }

        $followUpSearchModel = new LeadFollowUpSearch([
            'currentLead' => $model,
        ]);

        $followUpSearchModel->apply();

        return $this->render('view', compact('model', 'followUpSearchModel'));
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

        if (!($model instanceof Lead)) {
            return $model;
        }

        $historySearchParams = [
            'model' => Lead::class,
            'model_id' => $model->id,
        ];

        if (Yii::$app->hasModule('task')) {
            $historySearchParams['models'][] = function ($query) use ($model) {
                /** @var HistoryQuery $query */

                $query->leftJoin(Task::tableName(), [
                    'task.id' => new Expression('[[history.model_id]]'),
                    'history.model' => Task::class,
                    'task.model_id' => $model->id,
                    'task.model' => 'lead',
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
                    'event.model' => 'lead',
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

        if (!($model instanceof Lead)) {
            return $model;
        }

        $taskSearchModel = new TaskSearch([
            'params' => [
                'model' => 'lead',
                'model_id' => $model->id,
            ],
        ]);

        $taskSearchModel->apply(Yii::$app->request->get());

        return $this->render('task', compact('model', 'taskSearchModel'));
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

        if (!($model instanceof Lead)) {
            return $model;
        }

        $view = Yii::$app->request->get('view', 'default');
        $eventSearchModel = new EventSearch([
            'params' => [
                'view' => $view,
                'model' => 'lead',
                'model_id' => $model->id,
                'fetchUrl' => [
                    '/crm/admin/lead/view',
                    'id' => $model->id,
                    'action' => 'event',
                    'view' => 'calendar',
                    'query' => 1,
                ],
                'addUrl' => ['/calendar/admin/event/add', 'model' => 'lead', 'model_id' => $model->id],
            ],
        ]);

        if ($view === 'calendar' && Yii::$app->request->get('query')) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return $eventSearchModel->fullCalendar(Yii::$app->request->queryParams);
        }

        $eventSearchModel->apply(Yii::$app->request->queryParams);

        return $this->render('event', compact('model', 'eventSearchModel'));
    }

    /**
     * @param integer      $id
     * @param string|Lead  $modelClass
     * @param null|Closure $queryFilter
     *
     * @return string|Response|Lead
     * @throws InvalidConfigException
     */
    public function getModel($id, $modelClass = Lead::class, $queryFilter = null)
    {
        $query = $modelClass::find()->andWhere(['id' => $id]);

        if ($queryFilter instanceof Closure) {
            call_user_func($queryFilter, $query, $id, $modelClass);
        }

        $model = $query->one();

        if (!$model) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Lead'),
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

        if (!($model instanceof Lead)) {
            return $model;
        }

        if ($model->delete()) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully deleted', [
                'object' => Yii::t('app', 'Lead'),
                'object_name' => $model->name,
            ]));

            if (Lazy::isLazyModalRequest()) {
                Lazy::close();

                return '';
            }
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to delete {object}', [
                'object' => Yii::t('app', 'Lead'),
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

        $total = Lead::find()->andWhere(['id' => $ids])->count();

        if (count($ids) < $total) {
            return $this->notFound(Yii::t('app', 'Some {object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Lead'),
            ]));
        }

        if (Lead::bulkDelete($ids)) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{number} {object} successfully deleted', [
                'number' => count($ids),
                'object' => Yii::t('app', 'Leads'),
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
        $model = new LeadBulkSetStatus([
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
                    'object' => Yii::t('app', 'Lead'),
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
                    'object' => Yii::t('app', 'Lead'),
                ]));
            }
        }

        return $this->render('bulk-set-status', compact('model'));
    }


    /**
     * @return array|string|void|Response
     * @throws Throwable
     */
    public function actionBulkReassign()
    {
        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;
        $ids = (array) Yii::$app->request->post('id', []);
        $model = new LeadBulkReassign([
            'ids' => $ids,
            'staff' => $account->profile,
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
                    'object' => Yii::t('app', 'Leads'),
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
                    'object' => Yii::t('app', 'Lead'),
                ]));
            }
        }

        return $this->render('bulk-reassign', compact('model'));
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

        if (!($model instanceof Lead)) {
            return $model;
        }

        if ($model->changeStatus($status)) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{field} of {object_name} successfully changed', [
                'field' => Yii::t('app', 'Status'),
                'object_name' => $model->name,
            ]));
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to change {field} of {object}', [
                'object' => Yii::t('app', 'Lead'),
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
     * @throws Exception
     */
    public function actionAssign($id, $staff_id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Lead)) {
            return $model;
        }

        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;
        $staff = Staff::find()->andWhere(['id' => $staff_id])->one();

        if (!$staff) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Staff'),
            ]));
        }

        if ($model->assign($staff_id, $account->profile->id)) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{object} assigned to {staff}', [
                'staff' => $staff->name,
                'object' => Yii::t('app', 'Lead'),
            ]));
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to assign {object}', [
                'object' => Yii::t('app', 'Lead'),
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

        if (!($model instanceof Lead)) {
            return $model;
        }

        $staff = Staff::find()->andWhere(['id' => $staff_id])->one();

        if (!$staff) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Lead'),
            ]));
        }

        if ($model->unassign($staff_id)) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{staff} unassigned from {object}', [
                'staff' => $staff->name,
                'object' => Yii::t('app', 'Lead'),
            ]));
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to unassigned staff from {object}', [
                'object' => Yii::t('app', 'Lead'),
            ]));
        }

        return $this->goBack(['index']);
    }

    /**
     * @param $id
     *
     * @return array|Lead|string|Response
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

        if (!$model instanceof Lead) {
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

        $searchModel = new LeadSearch();

        return $searchModel->autoComplete(Yii::$app->request->queryParams);
    }

    /**
     * TODO: Remove on production
     */
    public function actionGenerate($amount = 1)
    {
        $faker = Factory::create();

        while ($amount > 0) {
            $model = new Lead([
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'phone' => $faker->phoneNumber,
                'mobile' => $faker->phoneNumber,
                'email' => $faker->email,
                'source_id' => LeadSource::find()->orderBy('RAND()')->select('id')->createCommand()->queryScalar(),
                'status_id' => LeadStatus::find()->orderBy('RAND()')->select('id')->createCommand()->queryScalar(),
                'country_code' => Country::find()->orderBy('RAND()')->select('code')->createCommand()->queryScalar(),
                'address' => $faker->streetAddress,
                'city' => $faker->city,
                'province' => $faker->city,
                'postal_code' => $faker->postcode,
                'assignee_ids' => Staff::find()->orderBy('RAND()')->select('id')->limit(rand(1, 3))->createCommand()->queryColumn(),
            ]);

            $model->loadDefaultValues();

            $model->save();

            $amount--;
        }
    }
}
