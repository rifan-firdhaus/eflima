<?php namespace modules\task\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use Faker\Factory;
use modules\account\models\forms\history\HistorySearch;
use modules\account\models\forms\staff\StaffSearch;
use modules\account\models\Staff;
use modules\account\models\StaffAccount;
use modules\account\web\admin\Controller;
use modules\account\widgets\lazy\LazyResponse;
use modules\file_manager\web\UploadedFile;
use modules\task\components\TaskRelation;
use modules\task\models\forms\task\TaskSearch;
use modules\task\models\forms\task_interaction\TaskInteractionSearch;
use modules\task\models\forms\task_timer\TaskTimerSearch;
use modules\task\models\query\TaskQuery;
use modules\task\models\Task;
use modules\task\models\TaskChecklist;
use modules\task\models\TaskFollower;
use modules\task\models\TaskInteraction;
use modules\task\models\TaskPriority;
use modules\task\models\TaskStatus;
use modules\task\models\TaskTimer;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\lazy\Lazy;
use Throwable;
use Yii;
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
    /**
     * @return array|string|Response
     * @throws InvalidConfigException
     */
    public function actionIndex()
    {
        $params = Yii::$app->request->queryParams;

        $searchModel = new TaskSearch([
            'scenario' => 'admin/search',
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
    public function actionHistory()
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
        $model = $this->getModel($id, Task::class);

        if (!($model instanceof Task)) {
            return $model;
        }

        $model->scenario = 'admin/update';
        $model->assignee_ids = $model->getAssigneesRelationship()->select('assignees_of_task.assignee_id')->createCommand()->queryColumn();

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
        $query = $modelClass::find()->andWhere(['id' => $id]);

        if ($queryFilter instanceof Closure) {
            call_user_func($queryFilter, $query, $id, $modelClass);
        }

        $model = $query->one();

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
     * @param int|string $id
     * @param string     $action
     *
     * @return array|string|Response
     * @throws InvalidConfigException
     */
    public function actionView($id, $action = 'view')
    {
        $model = $this->getModel($id, Task::class, function ($query) {
            /** @var TaskQuery $query */
            return $query->with(['assignees', 'status', 'priority']);
        });

        if (!($model instanceof Task)) {
            return $model;
        }

        switch ($action) {
            case 'history':
                return $this->history($model);
            case 'timer':
                return $this->timer($model);
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
            'attachments'
        ]);
    
        $interactionSearchModel->getQuery()->andWhere(['task_interaction.task_id' => $model->id]);

        $interactionSearchModel->apply(Yii::$app->request->queryParams);

        return $this->render('view', compact(
            'model', 'interactionModel', 'interactionSearchModel'
        ));
    }

    /**
     * @param Task $model
     *
     * @return string
     */
    public function history($model)
    {
        $historySearchModel = new HistorySearch([
            'params' => [
                'model' => Task::class,
                'model_id' => $model->id,
            ],
        ]);

        return $this->render('history', compact('model', 'historySearchModel'));
    }

    /**
     * @param Task $model
     *
     * @return string
     * @throws InvalidConfigException
     */
    public function timer($model)
    {
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
     */
    public function actionAssign($id, $staff_id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Task)) {
            return $model;
        }

        $staff = Staff::find()->andWhere(['id' => $staff_id])->one();

        if (!$staff) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Staff'),
            ]));
        }

        if ($model->assign($staff_id)) {
            Yii::$app->session->addFlash('success', Yii::t('app', 'Task assigned to {staff}', [
                'staff' => $staff->name,
            ]));
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to assign task'));
        }

        return $this->goBack(['index']);
    }

    public function actionUnassign($id, $staff_id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Task)) {
            return $model;
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
    public function actionStaffAssignableAutoComplete($id){

        if (!Yii::$app->request->isAjax) {
            throw new MethodNotAllowedHttpException('This URL only serve ajax request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $searchModel = new StaffSearch();

        $model = $this->getModel($id);

        if(!$model instanceof Task){
            return $model;
        }

        $assigned = $model->getAssigneesRelationship()
            ->select('assignee_id')
            ->createCommand()
            ->queryColumn();

        $searchModel->getQuery()
            ->andWhere(['NOT IN','staff.id',$assigned]);

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

    // TODO: Delete for producttion
    public function actionGenerate($number = 1)
    {
        $faker = Factory::create();

        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;
        $transaction = Yii::$app->db->beginTransaction();
        $now = time();
        $dateInputFormat = Yii::$app->setting->get('date_input_format') . ' ' . substr(Yii::$app->setting->get('time_input_format'), 4);

        while ($number > 0) {
            $startDate = $faker->boolean ? $now + rand(-20, 5) * (60 * 60 * 24) : $now + rand(-30, 30) * (60 * 60 * 24);
            $deadlineDate = $startDate + (rand(1, 60) * (60 * 60 * 24));
            $model = new Task([
                'scenario' => 'admin/add',
                'creator_id' => $account->profile->id,
            ]);
            $model->assignee_ids = Staff::find()->orderBy('RAND()')->limit(rand(1, 8))->select('id')->createCommand()->queryColumn();
            $checklistNum = rand(0, 17);
            $model->loadDefaultValues();
            $model->load([
                'title' => $faker->sentence(rand(2, 8)),
                'description' => $faker->paragraph(rand(5, 15)),
                'priority_id' => TaskPriority::find()->orderBy('RAND()')->one()->id,
                'status_id' => TaskStatus::find()->orderBy('RAND()')->one()->id,
                'started_date' => Yii::$app->formatter->asDate($startDate, $dateInputFormat),
                'deadline_date' => Yii::$app->formatter->asDate($deadlineDate, $dateInputFormat),
                'progress_calculation' => $faker->randomElement([Task::PROGRESS_CALCULATION_CHECKLIST, Task::PROGRESS_CALCULATION_OWN, Task::PROGRESS_CALCULATION_OWN]),

            ], '');

            for ($i = 0; $i <= $checklistNum; $i++) {
                $model->checklists['__' . rand(1000, 20000)] = [
                    'label' => $faker->sentence(rand(2, 8)),
                    'is_checked' => $faker->boolean,
                ];
            }

            if (!$model->save()) {
                $transaction->rollBack();

                return false;
            }

            if ($model->started_date <= time()) {
                $rand = rand(0, 5);

                while ($rand > 0) {
                    $time = $model->started_date + rand(0, ($model->deadline_date < $now ? $model->deadline_date : $now) - $model->started_date);
                    $timer = new TaskTimer([
                        'scenario' => 'admin/add',
                        'started_at' => $time,
                        'task_id' => $model->id,
                        'stopped_at' => $time + rand(10 * 60, 43200),
                        'stopper_id' => Staff::find()->orderBy('RAND()')->select('id')->createCommand()->queryScalar(),
                        'starter_id' => Staff::find()->orderBy('RAND()')->select('id')->createCommand()->queryScalar(),
                    ]);
                    $timer->loadDefaultValues();

                    if (!$timer->save()) {
                        $transaction->rollBack();

                        return false;
                    }

                    $rand--;
                }

                $rand = rand(0, 20);

                $taskInteractionStartDate = $model->started_date > $now ? ($now - rand(0, 432000)) : $model->started_date;

                while ($rand > 0) {
                    $progress = $faker->boolean && $faker->boolean && $model->progress_calculation == Task::PROGRESS_CALCULATION_OWN ? rand(20, 100) : null;
                    $taskInteractionStartDate = $taskInteractionStartDate + rand(0, $now - $taskInteractionStartDate);

                    $taskInteraction = new TaskInteraction([
                        'scenario' => 'admin/add',
                        'task_id' => $model->id,
                        'staff_id' => Staff::find()->orderBy('RAND()')->select('id')->createCommand()->queryScalar(),
                        'comment' => $faker->boolean ? $faker->sentence : $faker->paragraph(rand(1, 4)),
                        'progress' => $progress,
                        'status_id' => $faker->boolean ? TaskStatus::find()->orderBy("RAND()")->select('id')->createCommand()->queryScalar() : null,
                        'at' => $taskInteractionStartDate,
                    ]);

                    $taskInteraction->comment = "<p>{$taskInteraction->comment}</p>";

                    if (!$taskInteraction->save()) {
                        $transaction->rollBack();

                        return false;
                    }

                    $rand--;
                }
            }

            $number--;
        }

        $transaction->commit();

        return true;
    }
}
