<?php namespace modules\calendar\controllers\admin;

// "Keep the essence of your id, id isn't just a id, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\web\admin\Controller;
use modules\calendar\components\EventRelation;
use modules\calendar\models\Event;
use modules\calendar\models\forms\event\EventSearch;
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
class EventController extends Controller
{

    /**
     * @param string $view
     *
     * @return array|string|Response
     *
     * @throws InvalidConfigException
     */
    public function actionIndex($view = 'default')
    {
        $params = Yii::$app->request->queryParams;

        $searchModel = new EventSearch([
            'params' => [
                'view' => $view,
            ],
        ]);

        if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $searchModel->load($params);

            return Form::validate($searchModel);
        }

        if ($view === 'calendar' && Yii::$app->request->get('query')) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return $searchModel->fullCalendar($params);
        }

        $searchModel->apply($params);

        return $this->render('index', compact('searchModel'));
    }

    /**
     * @param string $id
     *
     * @param string $scenario
     *
     * @return array|string|Response
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function actionUpdate($id, $scenario = 'default')
    {
        $model = $this->getModel($id, Event::class);

        if (!($model instanceof Event)) {
            return $model;
        }

        if ($scenario === 'default') {
            $model->scenario = 'admin/update';
        } else {
            $model->scenario = 'admin/update/date';
        }

        $model->member_ids = $model->getMemberRelationships()->select('members_of_event.staff_id')->createCommand()->queryColumn();

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param string       $id
     * @param string|Event $modelClass
     * @param null|Closure $queryFilter
     *
     * @return string|Response|Event
     * @throws InvalidConfigException
     */
    public function getModel($id, $modelClass = Event::class, $queryFilter = null)
    {
        $query = $modelClass::find()->andWhere(['id' => $id]);

        if ($queryFilter instanceof Closure) {
            call_user_func($queryFilter, $query, $id, $modelClass);
        }

        $model = $query->one();

        if (!$model) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Event'),
            ]));
        }

        return $model;
    }

    /**
     * @param $id
     *
     * @return Event|string|Response
     *
     * @throws InvalidConfigException
     */
    public function actionView($id)
    {
        $model = $this->getModel($id, Event::class);

        if (!($model instanceof Event)) {
            return $model;
        }

        return $this->render('view', compact('model'));
    }

    /**
     * @param Event        $model
     * @param              $data
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

            $success = false;

            if ($model->save()) {
                $success = true;

                Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully saved', [
                    'object' => Yii::t('app', 'Event'),
                    'object_name' => $model->name,
                ]));

                if (Lazy::isLazyModalRequest() || Lazy::isLazyInsideModalRequest()) {
                    Lazy::close();

                    return;
                }

                if (!Yii::$app->request->isAjax) {
                    return $this->redirect(['update', 'id' => $model->id]);
                }
            } elseif ($model->hasErrors()) {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Some of the information you entered is invalid'));
            } else {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to save {object}', [
                    'object' => Yii::t('app', 'event'),
                ]));
            }

            if (Yii::$app->request->isAjax && !Lazy::isLazyRequest()) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                return [
                    'success' => $success,
                    'messages' => Yii::$app->session->getAllFlashes(),
                ];
            }
        }

        return $this->render('modify', compact('model'));
    }

    /**
     * @param mixed $model
     * @param mixed $model_id
     *
     * @return array|string|Response
     */
    public function actionAdd($model = null, $model_id = null)
    {
        $model = new Event([
            'scenario' => 'admin/add',
            'model' => $model,
            'model_id' => $model_id,
        ]);

        if (($startDate = Yii::$app->request->get('start_date', null))) {
            $model->start_date = $startDate;
        }

        if (($endDate = Yii::$app->request->get('end_date', null))) {
            $model->end_date = $endDate;
        }

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param string $id
     *
     * @return array|string|Response
     * @throws InvalidConfigException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Event)) {
            return $model;
        }

        if ($model->delete()) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully deleted', [
                'object' => Yii::t('app', 'Event'),
                'object_name' => $model->name,
            ]));

            if (Lazy::isLazyModalRequest()) {
                Lazy::close();

                return '';
            }
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to delete {object}', [
                'object' => Yii::t('app', 'Event'),
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

        $searchModel = new EventSearch();

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
        $event = new Event();
        $relation = EventRelation::get($model);

        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'input' => $this->view->renderAjaxContent($relation->pickerInput($event, 'model_id')),
        ];
    }
}