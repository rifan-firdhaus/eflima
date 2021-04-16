<?php namespace modules\project\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\web\admin\Controller;
use modules\account\widgets\lazy\LazyResponse;
use modules\project\models\forms\project_milestone\ProjectMilestoneSearch;
use modules\project\models\Project;
use modules\project\models\ProjectMilestone;
use modules\task\models\forms\task\TaskSearch;
use modules\task\models\Task;
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
class ProjectMilestoneController extends Controller
{

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access']['rules'] = [
            [
                'allow' => true,
                'actions' => ['index', 'task-list'],
                'verbs' => ['GET'],
                'roles' => ['admin.project.view.milestone.list'],
            ],
            [
                'allow' => true,
                'actions' => ['add'],
                'verbs' => ['GET', 'POST'],
                'roles' => ['admin.project.view.milestone.add'],
            ],
            [
                'allow' => true,
                'actions' => ['update'],
                'verbs' => ['GET', 'POST', 'PATCH'],
                'roles' => ['admin.project.view.milestone.update'],
            ],
            [
                'allow' => true,
                'actions' => ['change-color', 'sort'],
                'verbs' => ['POST', 'PATCH'],
                'roles' => ['admin.project.view.milestone.update'],
            ],
            [
                'allow' => true,
                'actions' => ['delete'],
                'verbs' => ['DELETE', 'POST'],
                'roles' => ['admin.project.view.milestone.delete'],
            ],
            [
                'allow' => true,
                'actions' => ['sort-task', 'move-task'],
                'verbs' => ['PATCH', 'POST'],
                'roles' => ['admin.project.view.milestone.task'],
            ],
            [
                'allow' => true,
                'actions' => ['auto-complete'],
                'verbs' => ['GET'],
                'roles' => ['@']
            ]
        ];

        return $behaviors;
    }

    /**
     * @param ProjectMilestone $model
     * @param                  $data
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
                    'object' => Yii::t('app', 'Milestone'),
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
                    'object' => Yii::t('app', 'Milestone'),
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
     */
    public function actionUpdate($id)
    {
        $model = $this->getModel($id, ProjectMilestone::class);

        if (!($model instanceof ProjectMilestone)) {
            return $model;
        }

        $model->scenario = 'admin/update';

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param null|string|int $project_id
     *
     * @return array|string|Response
     */
    public function actionAdd($project_id = null)
    {
        $model = new ProjectMilestone([
            'scenario' => 'admin/add',
            'project_id' => $project_id,
        ]);

        if (!$model->getProject()->exists()) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Project'),
            ]));
        }

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param integer                 $id
     * @param string|ProjectMilestone $modelClass
     * @param null|Closure            $queryFilter
     *
     * @return string|Response|ProjectMilestone
     * @throws InvalidConfigException
     */
    public function getModel($id, $modelClass = ProjectMilestone::class, $queryFilter = null)
    {
        $query = $modelClass::find()->andWhere(['id' => $id]);

        if ($queryFilter instanceof Closure) {
            call_user_func($queryFilter, $query, $id, $modelClass);
        }

        $model = $query->one();

        if (!$model) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Milestone'),
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

        if (!($model instanceof ProjectMilestone)) {
            return $model;
        }

        if ($model->delete()) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully deleted', [
                'object' => Yii::t('app', 'Milestone'),
                'object_name' => $model->name,
            ]));

            if (Lazy::isLazyModalRequest()) {
                Lazy::close();

                return '';
            }
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to delete {object}', [
                'object' => Yii::t('app', 'Milestone'),
            ]));
        }

        return $this->goBack(['index']);
    }

    /**
     * @param string|int $id
     * @param string     $color
     *
     * @return string|Response
     * @throws InvalidConfigException
     * @throws MethodNotAllowedHttpException
     */
    public function actionChangeColor($id, $color)
    {
        if (!Yii::$app->request->isAjax) {
            throw new MethodNotAllowedHttpException('This URL only serve ajax request');
        }

        $model = $this->getModel($id);

        if (!$model instanceof ProjectMilestone) {
            return $model;
        }

        $model->changeColor($color);

        return $this->goBack(['index']);
    }

    /**
     * @param $project_id
     *
     * @return array|string|Response
     *
     * @throws InvalidConfigException
     * @throws MethodNotAllowedHttpException
     * @throws Throwable
     * @throws Exception
     */
    public function actionSort($project_id)
    {
        if (!Yii::$app->request->isAjax) {
            throw new MethodNotAllowedHttpException('This URL only serve ajax request');
        }

        if (!Project::find()->andWhere(['id' => $project_id])->exists()) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Project'),
            ]));
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        if (ProjectMilestone::sort($project_id, Yii::$app->request->post('sort'))) {
            return [
                'success' => true,
            ];
        }

        return [
            'success' => false,
            'messages' => [
                'danger' => [
                    Yii::t('app', 'Failed to sort {object}', [
                        'object' => Yii::t('app', 'Milestone'),
                    ]),
                ],
            ],
        ];
    }

    /**
     * @param $id
     *
     * @return array|ProjectMilestone|string|Response
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws MethodNotAllowedHttpException
     * @throws Throwable
     */
    public function actionSortTask($id)
    {
        if (!Yii::$app->request->isAjax) {
            throw new MethodNotAllowedHttpException('This URL only serve ajax request');
        }

        $model = $this->getModel($id);

        if (!$model instanceof ProjectMilestone) {
            return $model;
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $fakeModel = new DynamicModel([
            'sort' => Yii::$app->request->post('sort'),
        ]);

        $fakeModel->addRule('sort', 'required')->addRule('sort', 'exist', [
            'allowArray' => true,
            'targetAttribute' => 'id',
            'targetClass' => Task::class,
        ]);

        if ($fakeModel->validate() && $model->sortTask($fakeModel->sort)) {
            return [
                'success' => true,
            ];
        }

        return [
            'success' => false,
            'messages' => [
                'danger' => [
                    Yii::t('app', 'Failed to sort {object}', [
                        'object' => Yii::t('app', 'Task'),
                    ]),
                ],
            ],
        ];
    }

    /**
     * @param $id
     *
     * @return array|ProjectMilestone|string|Response
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws MethodNotAllowedHttpException
     * @throws Throwable
     */
    public function actionMoveTask($id)
    {
        if (!Yii::$app->request->isAjax) {
            throw new MethodNotAllowedHttpException('This URL only serve ajax request');
        }

        $model = $this->getModel($id);

        if (!$model instanceof ProjectMilestone) {
            return $model;
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $fakeModel = new DynamicModel([
            'sort' => Yii::$app->request->post('sort'),
            'milestone_id' => Yii::$app->request->post('milestone_id'),
            'task_id' => Yii::$app->request->post('task_id'),
        ]);

        $fakeModel->addRule(['sort', 'milestone_id', 'task_id'], 'required')
            ->addRule('sort', 'exist', [
                'allowArray' => true,
                'targetAttribute' => 'id',
                'targetClass' => Task::class,
            ])
            ->addRule('task_id', 'exist', [
                'targetAttribute' => ['task_id' => 'id'],
                'targetClass' => Task::class,
            ])
            ->addRule('milestone_id', 'exist', [
                'targetAttribute' => ['milestone_id' => 'id'],
                'targetClass' => ProjectMilestone::class,
            ]);

        if ($fakeModel->validate() && $model->moveTask($fakeModel->task_id, $fakeModel->milestone_id, $fakeModel->sort)) {
            return [
                'success' => true,
            ];
        }

        return [
            'success' => false,
            'messages' => [
                'danger' => [
                    Yii::t('app', 'Failed to move {object}', [
                        'object' => Yii::t('app', 'Task'),
                    ]),
                ],
            ],
        ];
    }

    /**
     * @param $id
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function actionTaskList($id)
    {
        $taskSearchModel = new TaskSearch();

        $taskSearchModel->getQuery()->andWhere(['task.milestone_id' => $id])
            ->orderBy(['task.milestone_order' => SORT_ASC]);

        $taskSearchModel->dataProvider->pagination->validatePage = false;
        $taskSearchModel->dataProvider->pagination->pageSize = 2;

        $taskSearchModel->apply(Yii::$app->request->queryParams);

        $taskSearchModel->dataProvider->getModels();

        LazyResponse::$lazyData['has_more_page'] = $taskSearchModel->dataProvider->pagination->page + 1 < $taskSearchModel->dataProvider->pagination->pageCount;
        LazyResponse::$lazyData['page'] = $taskSearchModel->dataProvider->pagination->page + 1;

        return $this->renderPartial('task-list', compact('taskSearchModel'));
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

        $searchModel = new ProjectMilestoneSearch();

        return $searchModel->autoComplete(Yii::$app->request->queryParams);
    }
}
