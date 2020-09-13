<?php namespace modules\calendar\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\calendar\models\Event;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
trait EventRelatedTrait
{

    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return Yii::t('app', 'Event');
    }

    /**
     * @inheritDoc
     */
    public function getModel($id)
    {
        return Event::find()->andWhere(['id' => $id])->one();

    }

    /**
     * @param Event $model
     *
     * @inheritDoc
     */
    public function getName($model)
    {
        return $model->name;
    }

    /**
     * @param Event $model
     *
     * @inheritDoc
     */
    public function validate($model, $note)
    {
        if (!$model) {
            $note->addError('model_id', Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Event'),
            ]));
        }
    }

    /**
     * @inheritDoc
     *
     * @param Event $model
     */
    public function getUrl($model)
    {
        return Url::to(['/calendar/admin/event/view', 'id' => $model->id]);
    }

    /**
     * @inheritDoc
     *
     * @param Event $model
     */
    public function getLink($model)
    {
        return Html::a(Html::encode($model->name), $this->getUrl($model), [
            'data-lazy-modal' => 'event-view-modal',
            'data-lazy-container' => '#main-container',
        ]);
    }
}