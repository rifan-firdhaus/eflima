<?php namespace modules\finance\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\db\ActiveRecord;
use modules\finance\models\Invoice;
use modules\finance\models\Proposal;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
trait ProposalRelatedTrait
{

    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return Yii::t('app', 'Proposal');
    }

    /**
     * @inheritDoc
     *
     * @return Proposal
     *
     * @throws InvalidConfigException
     */
    public function getModel($id)
    {
        return Proposal::find()->andWhere(['id' => $id])->one();
    }

    /**
     * @param Proposal $model
     *
     * @inheritDoc
     */
    public function getName($model)
    {
        return $model->title;
    }

    /**
     * @param Proposal     $model
     * @param ActiveRecord $relatedModel
     *
     * @inheritDoc
     */
    public function validate($model, $relatedModel)
    {
        if (!$model) {
            $relatedModel->addError('model_id', Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Proposal'),
            ]));
        }
    }

    /**
     * @inheritDoc
     *
     * @param Proposal $model
     */
    public function getUrl($model)
    {
        return Url::to(['/finance/admin/proposal/view', 'id' => $model->id]);
    }

    /**
     * @inheritDoc
     *
     * @param Proposal $model
     */
    public function getLink($model)
    {
        return Html::a(Html::encode($model->title), $this->getUrl($model), [
            'data-lazy-modal' => 'proposal-view-modal',
            'data-lazy-container' => '#main-container',
        ]);
    }
}
