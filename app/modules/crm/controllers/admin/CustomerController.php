<?php namespace modules\crm\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\models\forms\history\HistorySearch;
use modules\account\web\admin\Controller;
use modules\account\widgets\lazy\LazyResponse;
use modules\calendar\models\forms\event\EventSearch;
use modules\crm\models\Customer;
use modules\crm\models\CustomerContact;
use modules\crm\models\CustomerContactAccount;
use modules\crm\models\forms\customer\CustomerSearch;
use modules\crm\models\forms\customer_contact\CustomerContactSearch;
use modules\file_manager\web\UploadedFile;
use modules\task\models\forms\task\TaskSearch;
use modules\task\models\forms\task_timer\TaskTimerSearch;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\inputs\Select2Data;
use modules\ui\widgets\lazy\Lazy;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\Response;
use function compact;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class CustomerController extends Controller
{
    /**
     * @return array|string|Response
     */
    public function actionIndex()
    {
        $params = Yii::$app->request->queryParams;

        $searchModel = new CustomerSearch();

        if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $searchModel->load($params);

            return Form::validate($searchModel);
        }

        $searchModel->apply($params);

        return $this->render('index', compact('searchModel'));
    }

    /**
     * @param Customer   $model
     * @param            $data
     *
     * @return string|array
     */
    protected function modify($model, $data)
    {
        $model->loadDefaultValues();
        $model->primaryContactModel->loadDefaultValues();
        $model->primaryContactModel->accountModel->loadDefaultValues();

        if (
            $model->load($data) &&
            $model->primaryContactModel->load($data) &&
            $model->primaryContactModel->accountModel->load($data)
        ) {
            $model->uploaded_company_logo = UploadedFile::getInstance($model, 'uploaded_company_logo');
            $model->primaryContactModel->accountModel->uploaded_avatar = UploadedFile::getInstance($model->primaryContactModel->accountModel, 'uploaded_avatar');

            if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                return Form::validate(
                    $model,
                    $model->primaryContactModel,
                    $model->primaryContactModel->accountModel
                );
            }

            if ($model->save()) {
                Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully saved', [
                    'object' => Yii::t('app', 'Customer'),
                    'object_name' => $model->name,
                ]));

                if (Lazy::isLazyRequest()) {
                    LazyResponse::$lazyData['id'] = $model->id;
                }

                if (Lazy::isLazyModalRequest() || Lazy::isLazyInsideModalRequest()) {
                    Lazy::close();

                    return;
                }

                return $this->redirect(['update', 'id' => $model->id]);
            } elseif ($model->hasErrors()) {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Some of the information you entered is invalid'));
            } else {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to save {object}', [
                    'object' => Yii::t('app', 'Customer'),
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
        $model = $this->getModel($id, Customer::class);

        if (!($model instanceof Customer)) {
            return $model;
        }

        $model->scenario = 'admin/update';

        $model->primaryContactModel = $model->primaryContact;
        $model->primaryContactModel->scenario = 'admin/update';

        if ($model->primaryContact->has_customer_area_access) {
            $model->primaryContactModel->accountModel = $model->primaryContactModel->account;
            $model->primaryContactModel->accountModel->scenario = 'admin/update';
        } else {
            $model->primaryContactModel->accountModel = new CustomerContactAccount([
                'scenario' => 'admin/add',
            ]);
        }

        $model->primaryContactModel->accountModel->customerContactModel = $model->primaryContactModel;

        if (!($model instanceof Customer)) {
            return $model;
        }

        $model->scenario = 'admin/update';

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

        if (!($model instanceof Customer)) {
            return $model;
        }

        switch ($action) {
            case 'history':
                return $this->history(compact('model'));
            case 'contact':
                return $this->contact(compact('model'));
            case 'task':
                return $this->task(compact('model'));
            case 'event':
                return $this->event(compact('model'));
        }

        $taskSearchModel = new TaskSearch([
            'params' => [
                'model' => 'customer',
                'model_id' => $model->id,
            ],
        ]);

        $taskTimerSearchModel = new TaskTimerSearch();

        $taskQuery = clone $taskSearchModel->getQuery();

        $taskTimerSearchModel->getQuery()->andWhere(['IN', 'task_timer.task_id', $taskQuery->select('task.id')]);

        return $this->render('view', compact('model', 'taskSearchModel', 'taskTimerSearchModel'));
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function history($params)
    {
        /** @var Customer $model */
        $model = $params['model']->id;
        $params['searchModel'] = new HistorySearch([
            'params' => [
                'model' => Customer::class,
                'model_id' => $model,
            ],
        ]);
        return $this->render('history', $params);
    }

    /**
     * @param array $params
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function contact($params)
    {
        /** @var Customer $customer */
        $customer = $params['model'];
        $searchModel = $params['searchModel'] = new CustomerContactSearch([
            'currentCustomer' => $customer,
            'params' => [
                'customer_id' => $customer->id,
            ],
        ]);

        $searchParams = Yii::$app->request->queryParams;

        $searchModel->getQuery()->andWhere(['customer_contact.customer_id' => $customer->id]);

        if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $searchModel->load($searchParams);

            return Form::validate($searchModel);
        }

        $searchModel->apply($searchParams);

        return $this->render('contact', $params);
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
                'model' => 'customer',
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
                'model' => 'customer',
                'model_id' => $model->id,
                'fetchUrl' => [
                    '/crm/admin/customer/view',
                    'id' => $model->id,
                    'action' => 'event',
                    'view' => 'calendar',
                    'query' => 1,
                ],
                'addUrl' => ['/calendar/admin/event/add', 'model' => 'customer', 'model_id' => $model->id],
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
     * @return array|string|Response
     */
    public function actionAdd()
    {
        $model = new Customer([
            'scenario' => 'admin/add',
        ]);
        $model->primaryContactModel = new CustomerContact([
            'is_primary' => true,
            'scenario' => 'admin/add',
        ]);
        $model->primaryContactModel->accountModel = new CustomerContactAccount([
            'scenario' => 'admin/add',
            'customerContactModel' => $model->primaryContactModel,
        ]);

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param integer         $id
     * @param string|Customer $modelClass
     * @param null|Closure    $queryFilter
     *
     * @return string|Response|Customer
     * @throws InvalidConfigException
     */
    public function getModel($id, $modelClass = Customer::class, $queryFilter = null)
    {
        $query = $modelClass::find()->andWhere(['id' => $id]);

        if ($queryFilter instanceof Closure) {
            call_user_func($queryFilter, $query, $id, $modelClass);
        }

        $model = $query->one();

        if (!$model) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Customer'),
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

        if (!($model instanceof Customer)) {
            return $model;
        }

        if ($model->delete()) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully deleted', [
                'object' => Yii::t('app', 'Customer'),
                'object_name' => $model->name,
            ]));

            if (Lazy::isLazyModalRequest()) {
                Lazy::close();

                return '';
            }
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to delete {object}', [
                'object' => Yii::t('app', 'Customer'),
            ]));
        }

        return $this->goBack(['index']);
    }

    /**
     * @param int|null|string $id
     *
     * @return array
     *
     * @throws InvalidConfigException
     * @throws MethodNotAllowedHttpException
     */
    public function actionAutoComplete($id = null)
    {
        if (!Yii::$app->request->isAjax) {
            throw new MethodNotAllowedHttpException('This URL only serve ajax request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!empty($id)) {
            $model = $this->getModel($id);
            $attributes = CustomerSearch::autoCompleteAttributes();
            $attributes['id'] = 'id';
            $attributes['text'] = 'company_name';

            return Select2Data::serializeModel($model, $attributes);
        }


        $searchModel = new CustomerSearch();

        return $searchModel->autoComplete(Yii::$app->request->queryParams);
    }

}
