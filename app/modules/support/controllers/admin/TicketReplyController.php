<?php namespace modules\support\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\models\StaffAccount;
use modules\account\web\admin\Controller;
use modules\account\widgets\lazy\LazyResponse;
use modules\file_manager\web\UploadedFile;
use modules\support\models\TicketReply;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\lazy\Lazy;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\Response;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class TicketReplyController extends Controller
{
    /**
     * @param TicketReply     $model
     * @param                 $data
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
                Yii::$app->session->addFlash('success', Yii::t('app', '{object} successfully sent', [
                    'object' => Yii::t('app', 'Reply'),
                ]));

                if (Lazy::isLazyRequest()) {
                    LazyResponse::$lazyData['item'] = $this->item($model);

                    return $this->form($model->ticket_id);
                }

                return $this->redirect(['update', 'id' => $model->id]);
            } elseif ($model->hasErrors()) {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Some of the information you entered is invalid'));
            } else {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to send {object}', [
                    'object' => Yii::t('app', 'Reply'),
                ]));
            }
        }

        return $this->render('modify', compact('model'));
    }

    /**
     * @param TicketReply $model
     *
     * @return string
     */
    public function item($model)
    {
        return $this->renderAjax('components/data-list-item', compact('model'));
    }

    /**
     * @param int|string $ticketId
     *
     * @return string
     */
    public function form($ticketId)
    {
        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;

        $model = new TicketReply([
            'scenario' => 'admin/reply',
            'ticket_id' => $ticketId,
            'staff_id' => $account->profile->id,
        ]);

        $model->loadDefaultValues();

        return $this->renderAjax('components/form', compact('model'));
    }

    /**
     * @param int|string $ticket_id
     *
     * @return array|string|Response
     */
    public function actionAdd($ticket_id)
    {
        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;

        $model = new TicketReply([
            'scenario' => 'admin/reply',
            'ticket_id' => $ticket_id,
            'staff_id' => $account->profile->id,
        ]);

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param integer            $id
     * @param string|TicketReply $modelClass
     * @param null|Closure       $queryFilter
     *
     * @return string|Response|TicketReply
     * @throws InvalidConfigException
     */
    public function getModel($id, $modelClass = TicketReply::class, $queryFilter = null)
    {
        $query = $modelClass::find()->andWhere(['id' => $id]);

        if ($queryFilter instanceof Closure) {
            call_user_func($queryFilter, $query, $id, $modelClass);
        }

        $model = $query->one();

        if (!$model) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Reply'),
            ]));
        }

        return $model;
    }
}