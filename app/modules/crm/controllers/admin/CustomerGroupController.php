<?php namespace modules\crm\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\web\admin\Controller;
use modules\core\components\SettingRenderer;
use modules\crm\models\CustomerGroup;
use modules\crm\models\forms\customer_group\CustomerGroupSearch;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\lazy\Lazy;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\web\Response;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class CustomerGroupController extends Controller
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
                'roles' => ['admin.setting.crm.customer-group.list'],
            ],
            [
                'allow' => true,
                'actions' => ['add'],
                'verbs' => ['GET', 'POST'],
                'roles' => ['admin.setting.crm.customer-group.add'],
            ],
            [
                'allow' => true,
                'actions' => ['update'],
                'verbs' => ['GET', 'POST', 'PATCH'],
                'roles' => ['admin.setting.crm.customer-group.update'],
            ],
            [
                'allow' => true,
                'actions' => ['delete'],
                'verbs' => ['POST', 'DELETE'],
                'roles' => ['admin.setting.crm.customer-group.delete'],
            ],
            [
                'allow' => true,
                'actions' => ['enable'],
                'verbs' => ['POST'],
                'roles' => ['admin.setting.crm.customer-group.visibility'],
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

        $searchModel = new CustomerGroupSearch();

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
     * @param CustomerGroup $model
     * @param               $data
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
                    'object' => Yii::t('app', 'Group'),
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
                    'object' => Yii::t('app', 'Group'),
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
        $model = $this->getModel($id, CustomerGroup::class);

        if (!($model instanceof CustomerGroup)) {
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
        $model = new CustomerGroup([
            'scenario' => 'admin/add',
        ]);

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param integer              $id
     * @param string|CustomerGroup $modelClass
     * @param null|Closure         $queryFilter
     *
     * @return string|Response|CustomerGroup
     * @throws InvalidConfigException
     */
    public function getModel($id, $modelClass = CustomerGroup::class, $queryFilter = null)
    {
        $query = $modelClass::find()->andWhere(['id' => $id]);

        if ($queryFilter instanceof Closure) {
            call_user_func($queryFilter, $query, $id, $modelClass);
        }

        $model = $query->one();

        if (!$model) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Source'),
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

        if (!($model instanceof CustomerGroup)) {
            return $model;
        }

        if ($model->delete()) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully deleted', [
                'object' => Yii::t('app', 'Group'),
                'object_name' => $model->name,
            ]));

            if (Lazy::isLazyModalRequest()) {
                Lazy::close();

                return '';
            }
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to delete {object}', [
                'object' => Yii::t('app', 'Group'),
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

        if (!($model instanceof CustomerGroup)) {
            return $model;
        }

        $enable = intval($enable);

        if ($model->enable($enable)) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{object} successfully {action}}', [
                'object' => Yii::t('app', 'Source'),
                'action' => $enable ? Yii::t('app', 'enabled') : Yii::t('app', 'disabled'),
            ]));
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to {action} {object}', [
                'object' => Yii::t('app', 'Source'),
                'action' => $enable ? Yii::t('app', 'enabled') : Yii::t('app', 'disabled'),
            ]));
        }

        return $this->goBack(['index']);
    }

}
