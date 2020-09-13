<?php namespace modules\finance\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class BankTransferPayment extends Payment
{
    /**
     * @inheritDoc
     */
    public function pay($model)
    {
        $model->status = Payment::STATUS_WAITING;

        return $model->save(false);
    }

    /**
     * @inheritDoc
     */
    public function paymentNotification($model)
    {
        $model->status = Payment::STATUS_ACCEPTED;

        return $model->save(false);
    }

    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return Yii::t('app','Bank Transfer');
    }
}