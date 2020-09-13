<?php namespace modules\finance\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\finance\models\Invoice;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
trait InvoiceRelatedTrait
{

    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return Yii::t('app', 'Invoice');
    }

    /**
     * @inheritDoc
     */
    public function getModel($id)
    {
        return Invoice::find()->andWhere(['id' => $id])->one();
    }

    /**
     * @param Invoice $model
     *
     * @inheritDoc
     */
    public function getName($model)
    {
        return $model->number;
    }

    /**
     * @param Invoice $model
     *
     * @inheritDoc
     */
    public function validate($model, $invoice)
    {
        if (!$model) {
            $invoice->addError('model_id', Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Invoice'),
            ]));
        }
    }

    /**
     * @inheritDoc
     *
     * @param Invoice $model
     */
    public function getUrl($model)
    {
        return Url::to(['/finance/admin/invoice/view', 'id' => $model->id]);
    }

    /**
     * @inheritDoc
     *
     * @param Invoice $model
     */
    public function getLink($model)
    {
        return Html::a(Html::encode($model->number), $this->getUrl($model), [
            'data-lazy-modal' => 'invoice-view-modal',
            'data-lazy-container' => '#main-container'
        ]);
    }
}