<?php namespace modules\finance\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\finance\models\Invoice;
use modules\finance\models\InvoicePayment;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
trait InvoicePaymentRelatedTrait
{

    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return Yii::t('app', 'Payment');
    }

    /**
     * @inheritDoc
     *
     * @return InvoicePayment
     *
     * @throws InvalidConfigException
     */
    public function getModel($id)
    {
        return InvoicePayment::find()->andWhere(['id' => $id])->one();
    }

    /**
     * @param InvoicePayment $model
     *
     * @inheritDoc
     */
    public function getName($model)
    {
        return $model->number;
    }

    /**
     * @param InvoicePayment $model
     *
     * @inheritDoc
     */
    public function validate($model, $invoice)
    {
        if (!$model) {
            $invoice->addError('model_id', Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Payment'),
            ]));
        }
    }

    /**
     * @inheritDoc
     *
     * @param InvoicePayment $model
     */
    public function getUrl($model)
    {
        return Url::to(['/finance/admin/invoice-payment/view', 'id' => $model->id]);
    }

    /**
     * @inheritDoc
     *
     * @param InvoicePayment $model
     */
    public function getLink($model)
    {
        return Html::a(Html::encode($model->number), $this->getUrl($model), [
            'data-lazy-modal' => 'invoice-payment-view-modal',
            'data-lazy-modal-size' => 'modal-lg',
            'data-lazy-container' => '#main-container'
        ]);
    }
}