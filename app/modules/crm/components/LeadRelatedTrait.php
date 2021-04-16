<?php namespace modules\crm\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\db\ActiveRecord;
use modules\crm\models\Lead;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
trait LeadRelatedTrait
{

    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return Yii::t('app', 'Lead');
    }

    /**
     * @inheritDoc
     */
    public function getModel($id)
    {
        $model = Lead::find()->andWhere(['id' => $id])->one();


        return $model;
    }

    /**
     * @param Lead $model
     *
     * @inheritDoc
     */
    public function getName($model)
    {
        return $model->name;
    }

    /**
     * @param Lead         $model
     * @param ActiveRecord $relatedModel
     *
     * @inheritDoc
     */
    public function validate($model, $relatedModel)
    {
        if (!$model) {
            $relatedModel->addError('model_id', Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Lead'),
            ]));
        }
    }

    /**
     * @inheritDoc
     *
     * @param Lead $model
     */
    public function getUrl($model)
    {
        return Url::to(['/crm/admin/lead/view', 'id' => $model->id]);
    }

    /**
     * @inheritDoc
     *
     * @param Lead $model
     */
    public function getLink($model)
    {
        return Html::a(Html::encode($model->name), $this->getUrl($model), [
            'data-lazy-modal' => 'lead-view-modal',
            'data-lazy-container' => '#main-container',
        ]);
    }
}
