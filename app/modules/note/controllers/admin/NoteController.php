<?php namespace modules\note\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\web\admin\Controller;
use modules\account\widgets\lazy\LazyResponse;
use modules\file_manager\web\UploadedFile;
use modules\note\components\NoteRelation;
use modules\note\models\forms\note\NoteSearch;
use modules\note\models\Note;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\lazy\Lazy;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\StaleObjectException;
use yii\web\Response;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class NoteController extends Controller
{
    /**
     * @inheritDoc
     */
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
                        $noteRelation = NoteRelation::get($model);

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
                'actions' => ['add', 'update','toggle-pin'],
                'matchCallback' => function ($rule) {
                    $data = Yii::$app->request->post('Note');
                    $model = $data['model'];
                    $modelId = $data['model_id'];

                    $rule->allow = true;

                    if ($model) {
                        $noteRelation = NoteRelation::get($model);

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
                        $noteRelation = NoteRelation::get($model);

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
     * @param mixed $model
     * @param mixed $model_id
     *
     * @return array|string|Response
     *
     * @throws InvalidConfigException
     */
    public function actionIndex($model = null, $model_id = null)
    {
        $params = Yii::$app->request->queryParams;

        $searchModel = new NoteSearch([
            'params' => [
                'model' => $model,
                'model_id' => $model_id,
            ],
        ]);

        if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $searchModel->load($params);

            return Form::validate($searchModel);
        }

        $searchModel->apply($params);

        if (Yii::$app->request->isAjax) {
            return $this->indexAjax($searchModel->dataProvider);
        }

        return $this->render('index', compact('searchModel'));
    }

    /**
     * @param ActiveDataProvider $dataProvider
     *
     * @return array
     */
    public function indexAjax($dataProvider)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $result = [];

        foreach ($dataProvider->models AS $model) {
            $result[] = $this->renderAjax('components/note-item', compact('model'));
        }

        return $result;
    }

    /**
     * @param Note       $model
     * @param            $data
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
                    'object' => Yii::t('app', 'Note'),
                ]));

                if (Lazy::isLazyRequest()) {
                    LazyResponse::$lazyData['item'] = $this->renderAjax('components/note-item', compact('model'));

                    return;
                }

                return $this->redirect(['update', 'id' => $model->id]);
            } elseif ($model->hasErrors()) {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Some of the information you entered is invalid'));
            } else {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to save {object}', [
                    'object' => Yii::t('app', 'Note'),
                ]));
            }
        }

        return $this->renderAjax('components/form', compact('model'));
    }

    /**
     * @param int|string $id
     *
     * @return array|string|Response
     * @throws InvalidConfigException
     */
    public function actionUpdate($id)
    {
        $model = $this->getModel($id, Note::class);

        if (!($model instanceof Note)) {
            return $model;
        }

        $model->scenario = 'admin/update';

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param mixed $model
     * @param mixed $model_id
     *
     * @return array|string|Response
     */
    public function actionAdd($model = null, $model_id = null)
    {
        $model = new Note([
            'scenario' => 'admin/add',
            'creator_id' => Yii::$app->user->id,
            'model' => $model,
            'model_id' => $model_id,
        ]);

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param integer      $id
     * @param string|Note  $modelClass
     * @param null|Closure $queryFilter
     *
     * @return string|Response|Note
     * @throws InvalidConfigException
     */
    public function getModel($id, $modelClass = Note::class, $queryFilter = null)
    {
        $query = $modelClass::find()->andWhere(['id' => $id]);

        if ($queryFilter instanceof Closure) {
            call_user_func($queryFilter, $query, $id, $modelClass);
        }

        $model = $query->one();

        if (!$model) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Note'),
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

        if (!($model instanceof Note)) {
            return $model;
        }

        if ($model->delete()) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{object} successfully deleted', [
                'object' => Yii::t('app', 'Note'),
            ]));
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to delete {object}', [
                'object' => Yii::t('app', 'Note'),
            ]));
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'success' => true,
                'messages' => Yii::$app->session->getAllFlashes(),
            ];
        }

        return $this->goBack(['index']);
    }

    /**
     * @param string|int $id
     * @param bool       $is_pinned
     *
     * @return array
     * @throws InvalidConfigException
     */
    public function actionTogglePin($id)
    {
        $model = $this->getModel($id);

        Yii::$app->response->format = Response::FORMAT_JSON;

        $isPinned = !$model->is_pinned;

        if ($model->pin($isPinned)) {
            return [
                'success' => false,
                'messages' => [
                    'danger' => [
                        Yii::t('app', '{object} successfully {action}}', [
                            'action' => $isPinned ? Yii::t('app', 'pinned') : Yii::t('app', 'unpined'),
                            'object' => Yii::t('app', 'Note'),
                        ]),
                    ],
                ],
            ];
        }

        return [
            'success' => false,
            'messages' => [
                'danger' => [
                    Yii::t('app', 'Failed to {action} {object}', [
                        'action' => $is_pinned ? Yii::t('app', 'pin') : Yii::t('app', 'unpin'),
                        'object' => Yii::t('app', 'Note'),
                    ]),
                ],
            ],
        ];
    }
}
