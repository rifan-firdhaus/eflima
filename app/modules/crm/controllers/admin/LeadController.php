<?php namespace modules\crm\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\models\forms\history\HistorySearch;
use modules\account\models\forms\staff\StaffSearch;
use modules\account\models\queries\HistoryQuery;
use modules\account\models\Staff;
use modules\account\web\admin\Controller;
use modules\calendar\models\Event;
use modules\calendar\models\forms\event\EventSearch;
use modules\crm\models\forms\lead\LeadSearch;
use modules\crm\models\forms\lead_follow_up\LeadFollowUpSearch;
use modules\crm\models\Lead;
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
    /**
     * @return array|string|Response
     */
    public function actionIndex()
    {
        $params = Yii::$app->request->queryParams;

        $searchModel = new LeadSearch();

        if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $searchModel->load($params);

            return Form::validate($searchModel);
        }

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
        $model = $this->getModel($id, Lead::class);

        if (!($model instanceof Lead)) {
            return $model;
        }

        $model->scenario = 'admin/update';
        $model->assignee_ids = $model->getAssigneesRelationship()->select('assignees_of_lead.assignee_id')->createCommand()->queryColumn();

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param null|int|string $customer_id
     *
     * @return array|string|Response
     */
    public function actionAdd($customer_id = null)
    {
        $model = new Lead([
            'scenario' => 'admin/add',
            'customer_id' => $customer_id,
        ]);

        return $this->modify($model, Yii::$app->request->post());
    }


    /**
     * @param string|int $id
     * @param string     $action
     *
     * @return string|Response
     * @throws InvalidConfigException
     */
    public function actionView($id, $action = 'view')
    {
        $model = $this->getModel($id);

        if (!($model instanceof Lead)) {
            return $model;
        }

        switch ($action) {
            case 'history':
                return $this->history($model);
            case 'task':
                return $this->task($model);
            case 'event':
                return $this->event($model);
        }

        $followUpSearchModel = new LeadFollowUpSearch([
            'currentLead' => $model,
        ]);

        $followUpSearchModel->apply();

        return $this->render('view', compact('model', 'followUpSearchModel'));
    }

    /**
     * @param Lead $model
     *
     * @return string
     */
    public function history($model)
    {
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
     * @param Lead $model
     *
     * @return string
     */
    public function task($model)
    {
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
     * @param Lead $model
     *
     * @return string|array
     *
     * @throws InvalidConfigException
     */
    public function event($model)
    {
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
     */
    public function actionAssign($id, $staff_id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Lead)) {
            return $model;
        }

        $staff = Staff::find()->andWhere(['id' => $staff_id])->one();

        if (!$staff) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Staff'),
            ]));
        }

        if ($model->assign($staff_id)) {
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
     *
     * @return array|Lead|string|Response
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

        if(!$model instanceof Lead){
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
}
