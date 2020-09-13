<?php namespace modules\support\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\finance\models\Invoice;
use modules\support\models\KnowledgeBase;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
trait KnowledgeBaseRelatedTrait
{

    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return Yii::t('app', 'Knowledge Base');
    }

    /**
     * @inheritDoc
     */
    public function getModel($id)
    {
        return KnowledgeBase::find()->andWhere(['id' => $id])->one();
    }

    /**
     * @param KnowledgeBase $model
     *
     * @inheritDoc
     */
    public function getName($model)
    {
        return $model->title;
    }

    /**
     * @param KnowledgeBase $model
     *
     * @inheritDoc
     */
    public function validate($model, $relation)
    {
        if (!$model) {
            $relation->addError('model_id', Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Knowledge Base'),
            ]));
        }
    }
    /**
     * @inheritDoc
     *
     * @param KnowledgeBase $model
     */
    public function getUrl($model)
    {
        return Url::to(['/support/admin/knowledgeBase/view', 'id' => $model->id]);
    }

    /**
     * @inheritDoc
     *
     * @param KnowledgeBase $model
     */
    public function getLink($model)
    {
        return Html::a(Html::encode($model->subject), $this->getUrl($model), [
            'data-lazy-modal' => 'knowledge-base-view-modal',
            'data-lazy-container' => '#main-container'
        ]);
    }
}