<?php namespace modules\address\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\web\admin\Controller;
use modules\address\models\forms\province\ProvinceSearch;
use modules\address\models\Province;
use modules\core\components\SettingRenderer;
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
class ProvinceController extends Controller
{

    /**
     * @return array|string|Response
     */
    public function actionIndex()
    {
        $params = Yii::$app->request->queryParams;

        $searchModel = new ProvinceSearch();

        if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $searchModel->load($params);

            return Form::validate($searchModel);
        }

        $searchModel->apply($params);

        /** @var SettingRenderer $renderer */
        $renderer = Yii::createObject([
            'class' => SettingRenderer::class,
            'section' => 'crm',
            'view' => $this->view,
        ]);

        return $this->render('index', compact('searchModel', 'renderer'));
    }

    /**
     * @param string $code
     *
     * @return array|string|Response
     * @throws InvalidConfigException
     */
    public function actionUpdate($code)
    {
        $model = $this->getModel($code, Province::class);

        if (!($model instanceof Province)) {
            return $model;
        }

        $model->scenario = 'admin/update';

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param string          $code
     * @param string|Province $modelClass
     * @param null|Closure    $queryFilter
     *
     * @return string|Response|Province
     * @throws InvalidConfigException
     */
    public function getModel($code, $modelClass = Province::class, $queryFilter = null)
    {
        $query = $modelClass::find()->andWhere(['code' => $code]);

        if ($queryFilter instanceof Closure) {
            call_user_func($queryFilter, $query, $code, $modelClass);
        }

        $model = $query->one();

        if (!$model) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Province'),
            ]));
        }

        return $model;
    }

    /**
     * @param Province     $model
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

            if ($model->save()) {
                Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully saved', [
                    'object' => Yii::t('app', 'Province'),
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
                    'object' => Yii::t('app', 'province'),
                ]));
            }
        }

        return $this->render('modify', compact('model'));
    }

    /**
     * @return array|string|Response
     */
    public function actionAdd()
    {
        $model = new Province([
            'scenario' => 'admin/add',
        ]);

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param string $code
     *
     * @return array|string|Response
     * @throws InvalidConfigException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete($code)
    {
        $model = $this->getModel($code);

        if (!($model instanceof Province)) {
            return $model;
        }

        if ($model->delete()) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully deleted', [
                'object' => Yii::t('app', 'Province'),
                'object_name' => $model->name,
            ]));

            if (Lazy::isLazyModalRequest()) {
                Lazy::close();

                return '';
            }
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to delete {object}', [
                'object' => Yii::t('app', 'Province'),
            ]));
        }

        return $this->goBack(['index']);
    }

    /**
     * @param string             $code
     * @param int|string|boolean $enable
     *
     * @return array|string|Response
     * @throws InvalidConfigException
     */
    public function actionEnable($code, $enable = 1)
    {
        $model = $this->getModel($code);

        if (!($model instanceof Province)) {
            return $model;
        }

        $enable = intval($enable);

        if ($model->enable($enable)) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully {action}', [
                'object' => Yii::t('app', 'Province'),
                'object_name' => $model->name,
                'action' => $enable ? Yii::t('app', 'enabled') : Yii::t('app', 'disabled'),
            ]));
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to {action} {object} ({object_name})', [
                'object' => Yii::t('app', 'Province'),
                'object_name' => $model->name,
                'action' => $enable ? Yii::t('app', 'enabled') : Yii::t('app', 'disabled'),
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

        $searchModel = new ProvinceSearch();

        return $searchModel->autoComplete(Yii::$app->request->queryParams);
    }
}
