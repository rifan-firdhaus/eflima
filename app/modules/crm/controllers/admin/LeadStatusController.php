<?php namespace modules\crm\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\web\admin\Controller;
use modules\account\widgets\lazy\LazyResponse;
use modules\core\components\SettingRenderer;
use modules\crm\models\forms\lead\LeadSearch;
use modules\crm\models\forms\lead_status\LeadStatusSearch;
use modules\crm\models\Lead;
use modules\crm\models\LeadStatus;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\lazy\Lazy;
use Throwable;
use Yii;
use yii\base\DynamicModel;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\Response;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class LeadStatusController extends Controller
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
                'actions' => ['index'],
                'verbs' => ['GET'],
                'roles' => ['admin.setting.crm.lead-status.list'],
            ],
            [
                'allow' => true,
                'actions' => ['add'],
                'verbs' => ['GET', 'POST'],
                'roles' => ['admin.setting.crm.lead-status.add'],
            ],
            [
                'allow' => true,
                'actions' => ['update'],
                'verbs' => ['GET', 'POST', 'PATCH'],
                'roles' => ['admin.setting.crm.lead-status.update'],
            ],
            [
                'allow' => true,
                'actions' => ['delete'],
                'verbs' => ['POST', 'DELETE'],
                'roles' => ['admin.setting.crm.lead-status.delete'],
            ],
            [
                'allow' => true,
                'actions' => ['enable'],
                'verbs' => ['POST'],
                'roles' => ['admin.setting.crm.lead-status.visibility'],
            ],
            [
                'allow' => true,
                'actions' => ['kanban','lead-list'],
                'verbs' => ['GET'],
                'roles' => ['admin.lead.kanban'],
            ],
            [
                'allow' => true,
                'actions' => ['move-lead','sort-lead'],
                'verbs' => ['POST'],
                'roles' => ['admin.lead.update'],
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

        $searchModel = new LeadStatusSearch();

        if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $searchModel->load($params);

            return Form::validate($searchModel);
        }

        /** @var SettingRenderer $renderer */
        $renderer = Yii::createObject([
            'class' => SettingRenderer::class,
            'section' => 'crm',
            'view' => $this->view,
        ]);

        $searchModel->apply($params);

        return $this->render('index', compact('searchModel', 'renderer'));
    }


    /**
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function actionKanban()
    {
        $statuses = LeadStatus::find()->enabled()->orderBy(['order' => SORT_ASC])->all();

        return $this->render('kanban', compact('statuses'));
    }

    /**
     * @param $id
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function actionLeadList($id)
    {
        $searchModel = new LeadSearch([
            'params' => [
                'status_id' => $id,
            ],
        ]);

        $searchModel->getQuery()->andWhere(['lead.status_id' => $id])
            ->orderBy(['lead.order' => SORT_ASC]);

        $searchModel->dataProvider->pagination->validatePage = false;

        $searchModel->apply(Yii::$app->request->queryParams);

        $searchModel->dataProvider->getModels();

        LazyResponse::$lazyData['has_more_page'] = $searchModel->dataProvider->pagination->page + 1 < $searchModel->dataProvider->pagination->pageCount;
        LazyResponse::$lazyData['page'] = $searchModel->dataProvider->pagination->page + 1;

        return $this->renderPartial('lead-list', compact('searchModel'));
    }


    /**
     * @param LeadStatus $model
     * @param            $data
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
                    'object' => Yii::t('app', 'Status'),
                    'object_name' => $model->label,
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
                    'object' => Yii::t('app', 'Source'),
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
        $model = $this->getModel($id, LeadStatus::class);

        if (!($model instanceof LeadStatus)) {
            return $model;
        }

        $model->scenario = 'admin/update';

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @return array|string|Response
     */
    public function actionAdd()
    {
        $model = new LeadStatus([
            'scenario' => 'admin/add',
        ]);

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param integer           $id
     * @param string|LeadStatus $modelClass
     * @param null|Closure      $queryFilter
     *
     * @return string|Response|LeadStatus
     * @throws InvalidConfigException
     */
    public function getModel($id, $modelClass = LeadStatus::class, $queryFilter = null)
    {
        $query = $modelClass::find()->andWhere(['id' => $id]);

        if ($queryFilter instanceof Closure) {
            call_user_func($queryFilter, $query, $id, $modelClass);
        }

        $model = $query->one();

        if (!$model) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Status'),
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

        if (!($model instanceof LeadStatus)) {
            return $model;
        }

        if ($model->delete()) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully deleted', [
                'object' => Yii::t('app', 'Status'),
                'object_name' => $model->label,
            ]));

            if (Lazy::isLazyModalRequest()) {
                Lazy::close();

                return '';
            }
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to delete {object}', [
                'object' => Yii::t('app', 'Status'),
            ]));
        }

        return $this->goBack(['index']);
    }

    /**
     * @param int|string         $id
     * @param int|string|boolean $enable
     *
     * @return array|string|Response
     * @throws InvalidConfigException
     */
    public function actionEnable($id, $enable = 1)
    {
        $model = $this->getModel($id);

        if (!($model instanceof LeadStatus)) {
            return $model;
        }

        $enable = intval($enable);

        if ($model->enable($enable)) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{object} successfully {action}}', [
                'object' => Yii::t('app', 'Status'),
                'action' => $enable ? Yii::t('app', 'enabled') : Yii::t('app', 'disabled'),
            ]));
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to {action} {object}', [
                'object' => Yii::t('app', 'Status'),
                'action' => $enable ? Yii::t('app', 'enabled') : Yii::t('app', 'disabled'),
            ]));
        }

        return $this->goBack(['index']);
    }


    /**
     * @return array|string|Response
     *
     * @throws InvalidConfigException
     * @throws MethodNotAllowedHttpException
     * @throws Throwable
     */
    public function actionSort()
    {
        if (!Yii::$app->request->isAjax) {
            throw new MethodNotAllowedHttpException('This URL only serve ajax request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        if (LeadStatus::sort(Yii::$app->request->post('sort'))) {
            return [
                'success' => true,
            ];
        }

        return [
            'success' => false,
            'messages' => [
                'danger' => [
                    Yii::t('app', 'Failed to sort {object}', [
                        'object' => Yii::t('app', 'Status'),
                    ]),
                ],
            ],
        ];
    }


    /**
     * @param $id
     *
     * @return array|string|Response
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws MethodNotAllowedHttpException
     * @throws Throwable
     */
    public function actionSortLead($id)
    {
        if (!Yii::$app->request->isAjax) {
            throw new MethodNotAllowedHttpException('This URL only serve ajax request');
        }

        $model = $this->getModel($id);

        if (!$model instanceof LeadStatus) {
            return $model;
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $validator = new DynamicModel([
            'sort' => Yii::$app->request->post('sort'),
        ]);

        $validator->addRule('sort', 'required')->addRule('sort', 'exist', [
            'allowArray' => true,
            'targetAttribute' => 'id',
            'targetClass' => Lead::class,
        ]);

        if ($validator->validate() && $model->sortTask($validator->sort)) {
            return [
                'success' => true,
            ];
        }

        return [
            'success' => false,
            'messages' => [
                'danger' => [
                    Yii::t('app', 'Failed to sort {object}', [
                        'object' => Yii::t('app', 'Lead'),
                    ]),
                ],
            ],
        ];
    }

    /**
     * @param $id
     *
     * @return array|string|Response
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws MethodNotAllowedHttpException
     * @throws Throwable
     */
    public function actionMoveLead($id)
    {
        if (!Yii::$app->request->isAjax) {
            throw new MethodNotAllowedHttpException('This URL only serve ajax request');
        }

        $model = $this->getModel($id);

        if (!$model instanceof LeadStatus) {
            return $model;
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $validator = new DynamicModel([
            'sort' => Yii::$app->request->post('sort'),
            'status_id' => Yii::$app->request->post('status_id'),
            'lead_id' => Yii::$app->request->post('lead_id'),
        ]);

        $validator->addRule(['sort', 'status_id', 'lead_id'], 'required')
            ->addRule('sort', 'exist', [
                'allowArray' => true,
                'targetAttribute' => 'id',
                'targetClass' => Lead::class,
            ])
            ->addRule('lead_id', 'exist', [
                'targetAttribute' => ['lead_id' => 'id'],
                'targetClass' => Lead::class,
            ])
            ->addRule('status_id', 'exist', [
                'targetAttribute' => ['status_id' => 'id'],
                'targetClass' => LeadStatus::class,
            ]);

        if ($validator->validate() && $model->moveTask($validator->lead_id, $validator->status_id, $validator->sort)) {
            return [
                'success' => true,
            ];
        }

        return [
            'success' => false,
            'messages' => [
                'danger' => [
                    Yii::t('app', 'Failed to move {object}', [
                        'object' => Yii::t('app', 'Lead'),
                    ]),
                ],
            ],
        ];
    }

}
