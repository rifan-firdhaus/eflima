<?php namespace modules\content\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\web\admin\Controller;
use modules\content\models\forms\post\PostSearch;
use modules\content\models\Post;
use modules\file_manager\web\UploadedFile;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\lazy\Lazy;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\Response;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class PostController extends Controller
{

    /**
     * @param string $type
     *
     * @return array|string|Response
     */
    public function actionIndex($type)
    {
        $params = Yii::$app->request->queryParams;

        $searchModel = new PostSearch([
            'type_id' => $type,
        ]);

        $searchModel->getQuery()->andWhere(['post.type_id' => $type]);

        if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $searchModel->load($params);

            return Form::validate($searchModel);
        }

        $dataProvider = $searchModel->apply($params);

        return $this->render('index', compact('searchModel', 'dataProvider'));
    }

    /**
     * @param string|int $id
     *
     * @return array|string|Response
     * @throws InvalidConfigException
     */
    public function actionUpdate($id)
    {
        $model = $this->getModel($id, Post::class);

        if (!($model instanceof Post)) {
            return $model;
        }

        $model->scenario = 'admin/update';

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param string|int   $id
     * @param string|Post  $modelClass
     * @param null|Closure $queryFilter
     *
     * @return string|Response|Post
     * @throws InvalidConfigException
     */
    public function getModel($id, $modelClass = Post::class, $queryFilter = null)
    {
        $query = $modelClass::find()->andWhere(['id' => $id]);

        if ($queryFilter instanceof Closure) {
            call_user_func($queryFilter, $query, $id, $modelClass);
        }

        $model = $query->one();

        if (!$model) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Post'),
            ]));
        }

        return $model;
    }

    /**
     * @param Post $model
     * @param      $data
     *
     * @return string|array
     */
    protected function modify($model, $data)
    {
        $model->loadDefaultValues();

        if ($model->load($data)) {
            $model->uploaded_picture = UploadedFile::getInstance($model, 'uploaded_picture');

            if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                return Form::validate($model);
            }

            if ($model->save()) {
                Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully saved', [
                    'object' => Yii::t('app', 'Post'),
                    'object_name' => $model->title,
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
                    'object' => Yii::t('app', 'Post'),
                ]));
            }
        }

        return $this->render('modify', compact('model'));
    }

    /**
     * @param string $type
     *
     * @return array|string|Response
     */
    public function actionAdd($type)
    {
        $model = new Post([
            'scenario' => 'admin/add',
            'type_id' => $type,
        ]);

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param string|int $id
     *
     * @return array|string|Response
     * @throws InvalidConfigException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Post)) {
            return $model;
        }

        if ($model->delete()) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully deleted', [
                'object' => Yii::t('app', 'Post'),
                'object_name' => $model->title,
            ]));

            if (Lazy::isLazyModalRequest()) {
                Lazy::close();

                return '';
            }
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to delete {object}', [
                'object' => Yii::t('app', 'Post'),
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

        $searchModel = new PostSearch();

        return $searchModel->autoComplete(Yii::$app->request->queryParams);
    }
}