<?php namespace modules\support\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\finance\models\Invoice;
use modules\support\models\Ticket;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
trait TicketRelatedTrait
{

    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return Yii::t('app', 'Ticket');
    }

    /**
     * @inheritDoc
     */
    public function getModel($id)
    {
        return Ticket::find()->andWhere(['id' => $id])->one();

    }

    /**
     * @param Ticket $model
     *
     * @inheritDoc
     */
    public function getName($model)
    {
        return $model->subject;
    }

    /**
     * @param Ticket $model
     *
     * @inheritDoc
     */
    public function validate($model, $task)
    {
        if (!$model) {
            $task->addError('model_id', Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Ticket'),
            ]));
        }
    }
    /**
     * @inheritDoc
     *
     * @param Ticket $model
     */
    public function getUrl($model)
    {
        return Url::to(['/support/admin/ticket/view', 'id' => $model->id]);
    }

    /**
     * @inheritDoc
     *
     * @param Ticket $model
     */
    public function getLink($model)
    {
        return Html::a(Html::encode($model->subject), $this->getUrl($model), [
            'data-lazy-modal' => 'ticket-view-modal',
            'data-lazy-container' => '#main-container'
        ]);
    }
}