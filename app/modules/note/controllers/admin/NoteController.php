<?php namespace modules\note\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\web\admin\Controller;
use modules\account\widgets\lazy\LazyResponse;
use modules\file_manager\web\UploadedFile;
use modules\note\models\forms\note\NoteSearch;
use modules\note\models\Note;
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
class NoteController extends Controller
{

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

        $params[$searchModel->formName()]['model_id'] = $model_id;
        $params[$searchModel->formName()]['model'] = $model;

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
}