<?php namespace modules\account\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\components\CommentRelation;
use modules\account\models\AccountComment;
use modules\account\models\forms\account_comment\AccountCommentSearch;
use modules\account\models\StaffAccount;
use modules\account\web\admin\Controller;
use modules\account\widgets\lazy\LazyResponse;
use modules\file_manager\web\UploadedFile;
use modules\note\components\NoteRelation;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\lazy\Lazy;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\web\Response;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class StaffCommentController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access']['rules'] = [
            [
                'actions' => ['index'],
                'matchCallback' => function ($rule) {
                    $model = Yii::$app->request->get('model');
                    $modelId = Yii::$app->request->get('model_id');

                    $rule->allow = true;

                    if ($model) {
                        $noteRelation = CommentRelation::get($model);

                        if (!$noteRelation) {
                            $rule->allow = false;
                        }

                        if (!$noteRelation->isActive($modelId)) {
                            $rule->allow = false;
                        }
                    }

                    return true;
                },
            ],
            [
                'actions' => ['add', 'update'],
                'matchCallback' => function ($rule) {
                    $data = Yii::$app->request->post('AccountComment');
                    $model = $data['model'];
                    $modelId = $data['model_id'];

                    $rule->allow = true;

                    if ($model) {
                        $noteRelation = CommentRelation::get($model);

                        if (!$noteRelation) {
                            $rule->allow = false;
                        }

                        if (!$noteRelation->isActive($modelId)) {
                            $rule->allow = false;
                        }
                    }

                    return true;
                },
            ],
            [
                'actions' => ['delete'],
                'verbs' => ['DELETE', 'POST'],
                'matchCallback' => function ($rule) {
                    $id = Yii::$app->request->get('id');

                    $note = $this->getModel($id);

                    $model = $note->model;
                    $modelId = $note->model_id;

                    $rule->allow = true;

                    if ($model) {
                        $noteRelation = CommentRelation::get($model);

                        if (!$noteRelation) {
                            $rule->allow = false;
                        }

                        if (!$noteRelation->isActive($modelId)) {
                            $rule->allow = false;
                        }
                    }

                    return true;
                },
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

        $searchModel = new AccountCommentSearch();

        if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $searchModel->load($params);

            return Form::validate($searchModel);
        }

        $dataProvider = $searchModel->apply($params);

        return $this->render('index', compact('searchModel', 'dataProvider'));
    }

    /**
     * @param string $id
     *
     * @return array|string|Response
     *
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function actionUpdate($id)
    {
        $model = $this->getModel($id, AccountComment::class);

        if (!($model instanceof AccountComment)) {
            return $model;
        }

        $model->scenario = 'admin/update';

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param string                $id
     * @param string|AccountComment $modelClass
     * @param null|Closure          $queryFilter
     *
     * @return string|Response|AccountComment
     * @throws InvalidConfigException
     */
    public function getModel($id, $modelClass = AccountComment::class, $queryFilter = null)
    {
        $query = $modelClass::find()->andWhere(['id' => $id]);

        if ($queryFilter instanceof Closure) {
            call_user_func($queryFilter, $query, $id, $modelClass);
        }

        $model = $query->one();

        if (!$model) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Comment'),
            ]));
        }

        return $model;
    }

    /**
     * @param AccountComment $model
     * @param                $data
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
                Yii::$app->session->addFlash('success', Yii::t('app', '{object} successfully saved', [
                    'object' => Yii::t('app', 'Comment'),
                ]));

                if (Lazy::isLazyRequest()) {
                    LazyResponse::$lazyData['item'] = $this->item($model);

                    return $this->form($model->model, $model->model_id);
                }

                return $this->goBack(['index']);
            } elseif ($model->hasErrors()) {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Some of the information you entered is invalid'));
            } else {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to save {object}', [
                    'object' => Yii::t('app', 'comment'),
                ]));
            }
        }

        return $this->render('modify', compact('model'));
    }

    /**
     * @param AccountComment $model
     *
     * @return string
     */
    public function item($model)
    {
        return $this->renderAjax('components/data-list-item', compact('model'));
    }

    /**
     *
     * @param null $model
     * @param null $model_id
     *
     * @return string
     */
    public function form($model = null, $model_id = null)
    {
        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;

        $model = new AccountComment([
            'scenario' => 'admin/add',
            'model' => $model,
            'model_id' => $model_id,
            'account_id' => $account->id,
        ]);

        $model->loadDefaultValues();

        return $this->renderAjax('components/form', compact('model'));
    }

    /**
     * @param mixed $model
     * @param mixed $model_id
     *
     * @return array|string|Response
     */
    public function actionAdd($model = null, $model_id = null)
    {
        $model = new AccountComment([
            'scenario' => 'admin/add',
            'model' => $model,
            'model_id' => $model_id,
            'account_id' => Yii::$app->user->id,
        ]);

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

        if (!($model instanceof AccountComment)) {
            return $model;
        }

        if ($model->delete()) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{object} successfully deleted', [
                'object' => Yii::t('app', 'Comment'),
            ]));

            if (Lazy::isLazyModalRequest()) {
                Lazy::close();

                return '';
            }
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to delete {object}', [
                'object' => Yii::t('app', 'Comment'),
            ]));
        }

        return $this->goBack(['index']);
    }
}
