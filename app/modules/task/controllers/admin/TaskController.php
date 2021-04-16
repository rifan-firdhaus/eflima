<?php namespace modules\task\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\models\forms\history\HistorySearch;
use modules\account\models\forms\staff\StaffSearch;
use modules\account\models\Staff;
use modules\account\models\StaffAccount;
use modules\account\web\admin\Controller;
use modules\account\widgets\lazy\LazyResponse;
use modules\file_manager\web\UploadedFile;
use modules\task\components\TaskRelation;
use modules\task\models\forms\task\TaskBulkReassign;
use modules\task\models\forms\task\TaskBulkSetPriority;
use modules\task\models\forms\task\TaskBulkSetStatus;
use modules\task\models\forms\task\TaskSearch;
use modules\task\models\forms\task_interaction\TaskInteractionSearch;
use modules\task\models\forms\task_timer\TaskTimerSearch;
use modules\task\models\query\TaskQuery;
use modules\task\models\Task;
use modules\task\models\TaskChecklist;
use modules\task\models\TaskFollower;
use modules\task\models\TaskInteraction;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\lazy\Lazy;
use Throwable;
use Yii;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\Response;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class TaskController extends Controller
{
    public $viewMenu = [
        'detail' => [
            'route' => ['/task/admin/task/detail'],
            'role' => 'admin.task.view.detail',
        ],
        'timer' => [
            'route' => ['/task/admin/task/timer'],
            'role' => 'admin.task.view.timer',
        ],
        'history' => [
            'route' => ['/task/admin/task/history'],
            'role' => 'admin.task.view.history',
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
                'roles' => ['admin.task.list'],
            ],
            [
                'allow' => true,
                'actions' => ['add'],
                'verbs' => ['GET', 'POST'],
                'roles' => ['admin.task.add'],
            ],
            [
                'allow' => true,
                'actions' => ['update'],
                'verbs' => ['GET', 'POST', 'PATCH'],
                'roles' => ['admin.task.update'],
            ],
            [
                'allow' => true,
                'actions' => ['all-history'],
                'verbs' => ['GET', 'POST', 'PATCH'],
                'roles' => ['admin.task.history'],
            ],
            [
                'allow' => true,
                'actions' => ['model-input'],
                'verbs' => ['GET'],
                'roles' => ['admin.task.update', 'admin.task.add'],
            ],
            [
                'allow' => true,
                'actions' => ['detail'],
                'verbs' => ['GET'],
                'roles' => ['admin.task.view.detail'],
            ],
            [
                'allow' => true,
                'actions' => ['timer'],
                'verbs' => ['GET'],
                'roles' => ['admin.task.view.timer'],
            ],
            [
                'allow' => true,
                'actions' => ['history'],
                'verbs' => ['GET'],
                'roles' => ['admin.task.view.history'],
            ],
            [
                'allow' => true,
                'actions' => ['delete', 'bulk-delete'],
                'verbs' => ['DELETE', 'POST'],
                'roles' => ['admin.task.delete'],
            ],
            [
                'allow' => true,
                'actions' => ['update-progress'],
                'verbs' => ['POST'],
                'roles' => ['admin.task.view.detail'],
            ],
            [
                'allow' => true,
                'actions' => ['change-status', 'bulk-set-status'],
                'verbs' => ['PATCH', 'POST'],
                'roles' => ['admin.task.status'],
            ],
            [
                'allow' => true,
                'actions' => ['change-priority', 'bulk-set-priority'],
                'verbs' => ['PATCH', 'POST'],
                'roles' => ['admin.task.priority'],
            ],
            [
                'allow' => true,
                'actions' => ['assign', 'unassign', 'bulk-reassign'],
                'verbs' => ['PATCH', 'POST'],
                'roles' => ['admin.task.assignee'],
            ],
            [
                'allow' => true,
                'actions' => ['toggle-timer'],
                'verbs' => ['PATCH', 'POST'],
                'roles' => ['admin.task.timer.toggle'],
            ],
            [
                'allow' => true,
                'actions' => [
                    'auto-complete',
                    'staff-assignable-auto-complete',
                    'active-timers',
                    'model-input',
                    'view',
                ],
                'roles' => ['@'],
                'verbs' => ['GET'],
            ],
        ];

        return $behaviors;
    }

    /**
     * @return array|string|Response
     * @throws InvalidConfigException
     */
    public function actionIndex()
    {
        $params = Yii::$app->request->queryParams;

        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;

        $searchModel = new TaskSearch([
            'scenario' => 'admin/search',
            'currentStaff' => $account->profile,
        ]);

        $searchModel->getQuery()->with(['assignees', 'status', 'priority']);

        if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $searchModel->load($params);

            return Form::validate($searchModel);
        }

        $searchModel->apply($params);

        return $this->render('index', compact('searchModel'));
    }

    /**
     * @return string
     */
    public function actionAllHistory()
    {
        $searchModel = new HistorySearch([
            'params' => [
                'model' => Task::class,
            ],
        ]);

        return $this->render('all-history', compact('searchModel'));
    }


    /**
     * @param Task          $model
     * @param               $data
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
                    'object' => Yii::t('app', 'Task'),
                    'object_name' => $model->title,
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
                    'object' => Yii::t('app', 'Task'),
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
        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;
        $model = $this->getModel($id, Task::class);

        if (!($model instanceof Task)) {
            return $model;
        }

        $model->scenario = 'admin/update';
        $model->assignee_ids = $model->getAssigneesRelationship()->select('assignees_of_task.assignee_id')->createCommand()->queryColumn();
        $model->assignor_id = $account->profile->id;

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param null|string|int $duplicate_id
     * @param null|string     $model
     * @param mixed           $model_id
     *
     * @return array|string|Response
     *
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function actionAdd($duplicate_id = null, $model = null, $model_id = null)
    {
        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;

        $model = new Task([
            'scenario' => 'admin/add',
            'creator_id' => $account->profile->id,
            'assignor_id' => $account->profile->id,
            'staff' => $account->profile,
            'model' => $model,
            'model_id' => $model_id,
        ]);

        if ($duplicate_id) {
            $duplicate = $this->getModel($duplicate_id);

            if (!$duplicate instanceof Task) {
                return $model;
            }

            $model->setAttributes($duplicate->getAttributes());
            $model->assignee_ids = $duplicate->getAssigneesRelationship()->select('assignees_of_task.assignee_id')->createCommand()->queryColumn();
            $model->checklists = TaskChecklist::find()->andWhere(['task_id' => $duplicate->id])->select(['label', 'is_checked'])->createCommand()->queryAll();
        }

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param integer      $id
     * @param string|Task  $modelClass
     * @param null|Closure $queryFilter
     *
     * @return string|Response|Task
     * @throws InvalidConfigException
     */
    public function getModel($id, $modelClass = Task::class, $queryFilter = null)
    {
        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;

        /** @var TaskQuery $query */
        $query = $modelClass::find();

        $query->andWhere(['id' => $id])->visibleToStaff($account->profile);

        if ($queryFilter instanceof Closure) {
            call_user_func($queryFilter, $query, $id, $modelClass);
        }

        $model = $query->one();
        $model->staff = $account->profile;

        if (!$model) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Task'),
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

        if (!($model instanceof Task)) {
            return $model;
        }

        if ($model->delete()) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully deleted', [
                'object' => Yii::t('app', 'Task'),
                'object_name' => $model->title,
            ]));

            if (Lazy::isLazyModalRequest()) {
                Lazy::close();

                return '';
            }
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to delete {object}', [
                'object' => Yii::t('app', 'Task'),
            ]));
        }

        return $this->redirect(['index']);
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

        $total = Task::find()->andWhere(['id' => $ids])->count();

        if (count($ids) < $total) {
            return $this->notFound(Yii::t('app', 'Some {object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Task'),
            ]));
        }

        if (Task::bulkDelete($ids)) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{number} {object} successfully deleted', [
                'number' => count($ids),
                'object' => Yii::t('app', 'Tasks'),
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
        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;
        $ids = (array) Yii::$app->request->post('id', []);
        $model = new TaskBulkSetStatus([
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
                    'object' => Yii::t('app', 'Tasks'),
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
                    'object' => Yii::t('app', 'Task'),
                ]));
            }
        }

        return $this->render('bulk-set-status', compact('model'));
    }

    /**
     * @return array|string|void|Response
     * @throws Throwable
     */
    public function actionBulkSetPriority()
    {
        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;
        $ids = (array) Yii::$app->request->post('id', []);
        $model = new TaskBulkSetPriority([
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
                    'object' => Yii::t('app', 'Tasks'),
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
                    'object' => Yii::t('app', 'Task'),
                ]));
            }
        }

        return $this->render('bulk-set-priority', compact('model'));
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
        $model = new TaskBulkReassign([
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
                    'object' => Yii::t('app', 'Tasks'),
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
                    'object' => Yii::t('app', 'Task'),
                ]));
            }
        }

        return $this->render('bulk-reassign', compact('model'));
    }

    /**
     * @param int|string $id
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
     * @return Task|string|Response
     * @throws InvalidConfigException
     */
    public function actionDetail($id)
    {
        $model = $this->getModel($id, Task::class, function ($query) {
            /** @var TaskQuery $query */
            return $query->with(['assignees', 'status', 'priority']);
        });

        if (!($model instanceof Task)) {
            return $model;
        }

        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;

        $interactionModel = new TaskInteraction([
            'task_id' => $model->id,
            'staff_id' => $account->profile->id,
            'scenario' => 'admin/add',
        ]);
        $interactionModel->loadDefaultValues();

        $interactionSearchModel = new TaskInteractionSearch();

        $interactionSearchModel->getQuery()->with([
            'status',
            'attachments',
        ]);

        $interactionSearchModel->getQuery()->andWhere(['task_interaction.task_id' => $model->id]);

        $interactionSearchModel->apply(Yii::$app->request->queryParams);

        return $this->render('view', compact(
            'model', 'interactionModel', 'interactionSearchModel'
        ));
    }

    /**
     * @param string|int $id
     *
     * @return string|Response
     *
     * @throws InvalidConfigException
     */
    public function actionHistory($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Task)) {
            return $model;
        }

        $historySearchModel = new HistorySearch([
            'params' => [
                'model' => Task::class,
                'model_id' => $model->id,
            ],
        ]);

        return $this->render('history', compact('model', 'historySearchModel'));
    }

    /**
     * @param string|int $id
     *
     * @return array|string|Response
     *
     * @throws InvalidConfigException
     */
    public function actionTimer($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Task)) {
            return $model;
        }

        $params = Yii::$app->request->queryParams;
        $timerSearchModel = new TaskTimerSearch([
            'currentTask' => $model,
            'params' => [
                'task_id' => $model->id,
            ],
        ]);

        if (!$model->is_timer_enabled) {
            $timerSearchModel->params['addUrl'] = false;
        }

        if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $timerSearchModel->load($params);

            return Form::validate($timerSearchModel);
        }

        $timerSearchModel->apply($params);

        return $this->render('timer', compact('model', 'timerSearchModel'));
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

        if (!($model instanceof Task)) {
            return $model;
        }

        if ($model->changeStatus($status)) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{field} of {object_name} successfully changed', [
                'field' => Yii::t('app', 'Status'),
                'object_name' => $model->title,
            ]));
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to change {field} of {object}', [
                'object' => Yii::t('app', 'Task'),
                'field' => Yii::t('app', 'status'),
            ]));
        }

        return $this->goBack(['index']);
    }

    /**
     * @param int|string $id
     * @param int        $priority
     *
     * @return array|string|Response
     * @throws InvalidConfigException
     */
    public function actionChangePriority($id, $priority)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Task)) {
            return $model;
        }

        if ($model->changePriority($priority)) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{field} of {object_name} successfully changed', [
                'field' => Yii::t('app', 'Priority'),
                'object_name' => $model->title,
            ]));
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to change {field} of {object}', [
                'object' => Yii::t('app', 'Task'),
                'action' => Yii::t('app', 'priority'),
            ]));
        }

        return $this->goBack(['index']);
    }

    /**
     * @param int|string $id
     * @param int        $start
     *
     * @return string|Response
     * @throws InvalidConfigException
     */
    public function actionToggleTimer($id, $start = 0)
    {
        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;

        $model = $this->getModel($id);

        if (!($model instanceof Task)) {
            return $model;
        }

        $method = intval($start) ? 'startTimer' : 'stopTimer';

        if ($model->{$method}($account->profile->id)) {
            Yii::$app->session->addFlash('success', Yii::t('app', 'Timer of {task} successfully {action}', [
                'task' => $model->title,
                'action' => intval($start) ? Yii::t('app', 'started') : Yii::t('app', 'stopped'),
            ]));

            $activeTimers = Task::find()->runningTimer($account->profile->id)->count();

            LazyResponse::$lazyData['timerCount'] = $activeTimers;
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to {action} {object}', [
                'action' => intval($start) ? Yii::t('app', 'start') : Yii::t('app', 'stop'),
                'object' => Yii::t('app', 'Timer'),
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

        if (!($model instanceof Task)) {
            return $model;
        }

        if ($model->visibility === Task::VISIBILITY_PRIVATE) {
            throw new Exception('You can\'t assign other staff on private task');
        }

        $staff = Staff::find()->andWhere(['id' => $staff_id])->one();

        if (!$staff) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Staff'),
            ]));
        }

        if ($model->assign($staff_id, $account->profile)) {
            Yii::$app->session->addFlash('success', Yii::t('app', 'Task assigned to {staff}', [
                'staff' => $staff->name,
            ]));
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to assign task'));
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

        if (!($model instanceof Task)) {
            return $model;
        }

        if ($model->visibility === Task::VISIBILITY_PRIVATE) {
            throw new Exception('You can\'t unassign staff on private task');
        }

        $staff = Staff::find()->andWhere(['id' => $staff_id])->one();

        if (!$staff) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Staff'),
            ]));
        }

        if ($model->unassign($staff_id)) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{staff} unassigned from task', [
                'staff' => $staff->name,
            ]));
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to unassigned staff from task'));
        }

        return $this->goBack(['index']);
    }

    /**
     * @param $id
     *
     * @return array|Response
     * @throws InvalidConfigException
     */
    public function actionUpdateProgress($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->getModel($id);

        if (!($model instanceof Task)) {
            return $model;
        }

        $progress = Yii::$app->request->post('progress');

        $isValid = DynamicModel::validateData([
            'progress' => $progress,
        ], [
            ['progress', 'required'],
            ['progress', 'integer', 'min' => 0, 'max' => 100],
        ]);

        if (!$isValid || !$model->updateProgress($progress / 100)) {
            return [
                'success' => false,
                'messages' => [
                    'danger' => [
                        Yii::t('app', 'Failed to {action} {object}', [
                            'action' => Yii::t('app', 'Set'),
                            'object' => Yii::t('app', 'Task\'s Progress'),
                        ]),
                    ],
                ],
            ];
        }

        return [
            'success' => true,
            'messages' => [
                'success' => [
                    Yii::t('app', 'Task\'s Progress "{object_name}" seccessfully set', [
                        'action' => Yii::t('app', 'Set'),
                        'object_name' => Yii::t('app', 'Task\'s Progress'),
                    ]),
                ],
            ],
        ];
    }

    /**
     * @param $id
     *
     * @return string|Response
     * @throws InvalidConfigException
     */
    public function actionFollow($id)
    {
        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;

        $model = $this->getModel($id);

        if (!($model instanceof Task)) {
            return $model;
        }

        $followerModel = new TaskFollower([
            'task_id' => $model->id,
            'follower_id' => $account->profile->id,
        ]);
        $data = Yii::$app->request->post();

        if ($followerModel->load($data)) {
            if (($success = $model->save())) {
                Yii::$app->session->addFlash('success', Yii::t('app', 'You are following {object_name}', [
                    'object_name' => $model->title,
                ]));
            } else {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to {action} {object}', [
                    'object' => Yii::t('app', 'Task'),
                    'action' => Yii::t('app', 'follow'),
                ]));
            }

            if (Lazy::isLazyRequest()) {
                LazyResponse::$lazyData['success'] = true;

                return null;
            }
        }

        return $this->redirect(['/task/admin/task/view', 'id' => $id]);
    }

    /**
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function actionActiveTimers()
    {
        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;
        $searchModel = new TaskSearch();

        $searchModel->getQuery()->runningTimer($account->profile->id);
        $searchModel->apply(Yii::$app->request->queryParams);

        return $this->renderAjax('active-timers', [
            'dataProvider' => $searchModel->dataProvider,
        ]);
    }

    /**
     * @param $id
     *
     * @return array|Task|string|Response
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

        if (!$model instanceof Task) {
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
     * @param $id
     *
     * @return array|Task|string|Response|null
     * @throws InvalidConfigException
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function unfollow($id)
    {
        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;

        $model = $this->getModel($id);

        if (!($model instanceof Task)) {
            return $model;
        }

        $followerModel = TaskFollower::find()->andWhere(['task_id' => $id, 'follower_id' => $account->profile->id])->one();

        if (!$followerModel) {
            return $this->notFound(Yii::t('app', 'You are not following {object_name} yet', [
                'task' => $model->title,
            ]));
        }

        if (($success = $followerModel->delete())) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{object_name} unfollowed', [
                'object_name' => $model->title,
            ]));
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to {action} {object}', [
                'action' => Yii::t('app', 'unfollow'),
                'object' => Yii::t('app', 'Task'),
            ]));
        }

        if (Lazy::isLazyModalRequest()) {
            LazyResponse::$lazyData['success'] = $success;

            return null;
        }

        return $this->redirect(['index']);
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
        $task = new Task();
        $relation = TaskRelation::get($model);

        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'input' => $this->view->renderAjaxContent($relation->pickerInput($task, 'model_id')),
        ];
    }
}
