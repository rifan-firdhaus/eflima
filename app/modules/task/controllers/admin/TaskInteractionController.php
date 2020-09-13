<?php namespace modules\task\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\models\StaffAccount;
use modules\account\web\admin\Controller;
use modules\account\widgets\lazy\LazyResponse;
use modules\file_manager\web\UploadedFile;
use modules\task\models\TaskInteraction;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\lazy\Lazy;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\web\Response;
use function compact;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class TaskInteractionController extends Controller
{
    /**
     * @param TaskInteraction $model
     * @param                 $data
     *
     * @return string|array
     */
    protected function modify($model, $data)
    {
        $model->loadDefaultValues();

        if ($model->load($data)) {
            $model->uploaded_attachments = UploadedFile::getInstances($model,'uploaded_attachments');

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

                    return $this->form($model->task_id);
                }

                return $this->redirect(['update', 'id' => $model->id]);
            } elseif ($model->hasErrors()) {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Some of the information you entered is invalid'));
            } else {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to save {object}', [
                    'object' => Yii::t('app', 'Comment'),
                ]));
            }
        }

        return $this->render('modify', compact('model'));
    }

    /**
     * @param TaskInteraction $model
     *
     * @return string
     */
    public function item($model)
    {
        return $this->renderAjax('components/data-list-item', compact('model'));
    }

    /**
     * @param int|string $taskId
     *
     * @return string
     */
    public function form($taskId)
    {
        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;

        $model = new TaskInteraction([
            'scenario' => 'admin/add',
            'task_id' => $taskId,
            'staff_id' => $account->profile->id,
        ]);

        $model->loadDefaultValues();

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
        $model = $this->getModel($id, TaskInteraction::class);

        if (!($model instanceof TaskInteraction)) {
            return $model;
        }

        $model->scenario = 'admin/update';

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param int|string $task_id
     *
     * @return array|string|Response
     */
    public function actionAdd($task_id)
    {
        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;

        $model = new TaskInteraction([
            'scenario' => 'admin/add',
            'task_id' => $task_id,
            'staff_id' => $account->profile->id,
        ]);

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param integer                $id
     * @param string|TaskInteraction $modelClass
     * @param null|Closure           $queryFilter
     *
     * @return string|Response|TaskInteraction
     * @throws InvalidConfigException
     */
    public function getModel($id, $modelClass = TaskInteraction::class, $queryFilter = null)
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

        if (!($model instanceof TaskInteraction)) {
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