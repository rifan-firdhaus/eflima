<?php namespace modules\crm\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\models\forms\history\HistorySearch;
use modules\account\web\admin\Controller;
use modules\calendar\models\forms\event\EventSearch;
use modules\crm\models\Customer;
use modules\crm\models\forms\lead\LeadSearch;
use modules\crm\models\forms\lead_follow_up\LeadFollowUpSearch;
use modules\crm\models\Lead;
use modules\task\models\forms\task\TaskSearch;
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
                return $this->history(compact('model'));
            case 'task':
                return $this->task(compact('model'));
            case 'event':
                return $this->event(compact('model'));
        }

        $followUpSearchModel = new LeadFollowUpSearch([
            'currentLead' => $model,
        ]);

        $followUpSearchModel->apply();

        return $this->render('view', compact('model', 'followUpSearchModel'));
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function history($params)
    {
        /** @var Lead $model */
        $model = $params['model']->id;
        $params['searchModel'] = new HistorySearch([
            'params' => [
                'model' => Lead::class,
                'model_id' => $model,
            ],
        ]);

        return $this->render('history', $params);
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function task($params)
    {
        /** @var Customer $model */
        $model = $params['model'];
        $searchModel = $params['searchModel'] = new TaskSearch([
            'params' => [
                'model' => 'lead',
                'model_id' => $model->id,
            ],
        ]);

        $params['dataProvider'] = $searchModel->apply(Yii::$app->request->get());

        return $this->render('task', $params);
    }


    /**
     * @param array $params
     *
     * @return string|array
     *
     * @throws InvalidConfigException
     */
    public function event($params)
    {
        /** @var Customer $model */
        $view = Yii::$app->request->get('view', 'default');
        $model = $params['model'];
        $searchModel = new EventSearch([
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

            return $searchModel->fullCalendar(Yii::$app->request->queryParams);
        }

        $dataProvider = $searchModel->apply(Yii::$app->request->queryParams);

        return $this->render('event', compact('model', 'searchModel', 'dataProvider'));
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
