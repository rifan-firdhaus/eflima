<?php namespace modules\crm\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\models\forms\history\HistorySearch;
use modules\account\models\queries\HistoryQuery;
use modules\account\web\admin\Controller;
use modules\account\widgets\lazy\LazyResponse;
use modules\calendar\models\forms\event\EventSearch;
use modules\crm\models\Customer;
use modules\crm\models\CustomerContact;
use modules\crm\models\CustomerContactAccount;
use modules\crm\models\forms\customer\CustomerBulkSetGroup;
use modules\crm\models\forms\customer\CustomerSearch;
use modules\crm\models\forms\customer_contact\CustomerContactSearch;
use modules\crm\models\Lead;
use modules\file_manager\web\UploadedFile;
use modules\task\models\forms\task\TaskSearch;
use modules\task\models\forms\task_timer\TaskTimerSearch;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\inputs\Select2Data;
use modules\ui\widgets\lazy\Lazy;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Expression;
use yii\db\StaleObjectException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\Response;
use function compact;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class CustomerController extends Controller
{
    public $viewMenu = [
        'detail' => [
            'route' => ['/crm/admin/customer/detail'],
            'role' => 'admin.customer.view.detail',
        ],
        'contact' => [
            'route' => ['/crm/admin/customer/contact'],
            'role' => 'admin.customer.view.contact',
        ],
        'task' => [
            'route' => ['/crm/admin/customer/task'],
            'role' => 'admin.customer.view.contact',
        ],
        'event' => [
            'route' => ['/crm/admin/customer/event'],
            'role' => 'admin.customer.view.event',
        ],
        'history' => [
            'route' => ['/crm/admin/customer/history'],
            'role' => 'admin.customer.view.history',
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
                'roles' => ['admin.customer.list'],
            ],
            [
                'allow' => true,
                'actions' => ['add'],
                'verbs' => ['GET', 'POST'],
                'roles' => ['admin.customer.add'],
            ],
            [
                'allow' => true,
                'actions' => ['update', 'bulk-set-group'],
                'verbs' => ['GET', 'POST', 'PATCH'],
                'roles' => ['admin.customer.update'],
            ],
            [
                'allow' => true,
                'actions' => ['delete', 'bulk-delete'],
                'verbs' => ['DELETE', 'POST'],
                'roles' => ['admin.customer.delete'],
            ],
            [
                'allow' => true,
                'actions' => ['detail'],
                'verbs' => ['GET'],
                'roles' => ['admin.customer.view.detail'],
            ],
            [
                'allow' => true,
                'actions' => ['contact'],
                'verbs' => ['GET'],
                'roles' => ['admin.customer.view.contact'],
            ],
            [
                'allow' => true,
                'actions' => ['task'],
                'verbs' => ['GET'],
                'roles' => ['admin.customer.view.task'],
            ],
            [
                'allow' => true,
                'actions' => ['event'],
                'verbs' => ['GET'],
                'roles' => ['admin.customer.view.event'],
            ],
            [
                'allow' => true,
                'actions' => ['history'],
                'verbs' => ['GET'],
                'roles' => ['admin.customer.view.history'],
            ],
            [
                'allow' => true,
                'actions' => ['auto-complete', 'view'],
                'roles' => ['@'],
                'verbs' => ['GET'],
            ],
        ];

        return $behaviors;
    }

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
     *
     * @return string|Response
     *
     * @throws InvalidConfigException
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
     * @return Customer|string|Response
     * @throws InvalidConfigException
     */
    public function actionDetail($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Customer)) {
            return $model;
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
     * @param string|int $id
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function actionHistory($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Customer)) {
            return $model;
        }

        $searchModel = new HistorySearch([
            'params' => [
                'model' => Customer::class,
                'model_id' => $model->id,
                'models' => [
                    function ($query) use ($model) {
                        /** @var HistoryQuery $query */

                        $query->leftJoin(CustomerContact::tableName(), [
                            'customer_contact.id' => new Expression('[[history.model_id]]'),
                            'history.model' => CustomerContact::class,
                            'customer_contact.customer_id' => $model->id,
                        ]);

                        return ['IS NOT', 'customer_contact.id', null];
                    },
                ],
            ],
        ]);
        return $this->render('history', compact('model', 'searchModel'));
    }

    /**
     * @param string|int $id
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function actionContact($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Customer)) {
            return $model;
        }

        $contactSearchModel = new CustomerContactSearch([
            'params' => [
                'customer_id' => $model->id,
            ],
        ]);

        $searchParams = Yii::$app->request->queryParams;

        $contactSearchModel->getQuery()->andWhere(['customer_contact.customer_id' => $model->id]);

        if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $contactSearchModel->load($searchParams);

            return Form::validate($contactSearchModel);
        }

        $contactSearchModel->apply($searchParams);

        return $this->render('contact', compact('contactSearchModel', 'model'));
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

        if (!($model instanceof Customer)) {
            return $model;
        }

        $taskSearchModel = new TaskSearch([
            'params' => [
                'model' => 'customer',
                'model_id' => $model->id,
            ],
        ]);

        $taskSearchModel->apply(Yii::$app->request->get());

        return $this->render('task', compact('taskSearchModel', 'model'));
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

        if (!($model instanceof Customer)) {
            return $model;
        }

        $view = Yii::$app->request->get('view', 'default');
        $eventSearchModel = new EventSearch([
            'params' => [
                'view' => $view,
                'model' => 'customer',
                'model_id' => $model->id,
                'fetchUrl' => [
                    '/crm/admin/customer/event',
                    'id' => $model->id,
                    'view' => 'calendar',
                    'query' => 1,
                ],
                'addUrl' => ['/calendar/admin/event/add', 'model' => 'customer', 'model_id' => $model->id],
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
     * @param null|string|int $lead_id
     *
     * @return array|string|Response
     *
     * @throws InvalidConfigException
     */
    public function actionAdd($lead_id = null)
    {
        $lead = null;

        if ($lead_id) {
            $lead = Lead::find()->andWhere(['id' => $lead_id])->one();

            if (!$lead) {
                return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                    'object' => Yii::t('app', 'Lead'),
                ]));
            }
        }

        $model = new Customer([
            'scenario' => 'admin/add',
            'fromLead' => $lead,
        ]);
        $model->primaryContactModel = new CustomerContact([
            'is_primary' => true,
            'scenario' => 'admin/add',
        ]);
        $model->primaryContactModel->accountModel = new CustomerContactAccount([
            'scenario' => 'admin/add',
            'customerContactModel' => $model->primaryContactModel,
        ]);

        if ($lead) {
            $model->type = Customer::TYPE_PERSONAL;
            $model->primaryContactModel->first_name = $lead->first_name;
            $model->primaryContactModel->last_name = $lead->last_name;
            $model->primaryContactModel->phone = $lead->phone;
            $model->primaryContactModel->email = $lead->email;
            $model->primaryContactModel->city = $lead->city;
            $model->primaryContactModel->country_code = $lead->country_code;
            $model->primaryContactModel->province = $lead->province;
            $model->primaryContactModel->address = $lead->address;
            $model->primaryContactModel->postal_code = $lead->postal_code;
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
                'model' => Customer::class,
            ],
        ]);

        return $this->render('all-history', compact('searchModel'));
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
     * @return array|string|Response
     *
     * @throws InvalidConfigException
     * @throws Throwable
     *
     */
    public function actionBulkDelete()
    {
        $ids = (array) Yii::$app->request->post('id', []);

        $total = Customer::find()->andWhere(['id' => $ids])->count();

        if (count($ids) < $total) {
            return $this->notFound(Yii::t('app', 'Some {object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Customer'),
            ]));
        }

        if (Customer::bulkDelete($ids)) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{number} {object} successfully deleted', [
                'number' => count($ids),
                'object' => Yii::t('app', 'Customers'),
            ]));
        }

        return $this->goBack(['index']);
    }

    /**
     * @return array|string|void|Response
     * @throws Throwable
     */
    public function actionBulkSetGroup()
    {
        $ids = (array) Yii::$app->request->post('id', []);
        $model = new CustomerBulkSetGroup([
            'id' => $ids,
        ]);
        $data = Yii::$app->request->post();

        if ($model->load($data)) {
            if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                return Form::validate($model);
            }

            if ($model->save()) {
                Yii::$app->session->addFlash('success', Yii::t('app', '{number} {object} successfully updated', [
                    'number' => count($model->id),
                    'object' => Yii::t('app', 'Customers'),
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
                    'object' => Yii::t('app', 'Customer'),
                ]));
            }
        }

        return $this->render('bulk-set-group', compact('model'));
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
