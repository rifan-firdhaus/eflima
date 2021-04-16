<?php namespace modules\crm\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\crm\models\Customer;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
trait CustomerRelatedTrait
{

    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return Yii::t('app', 'Customer');
    }

    /**
     * @inheritDoc
     */
    public function getModel($id)
    {
        return Customer::find()->andWhere(['id' => $id])->one();
    }

    /**
     * @param Customer $model
     *
     * @inheritDoc
     */
    public function getName($model)
    {
        return $model->name;
    }

    /**
     * @param mixed    $model
     * @param Customer $customer
     *
     * @inheritDoc
     */
    public function validate($model, $customer)
    {
        if (!$model) {
            $customer->addError('model_id', Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Customer'),
            ]));
        }
    }

    /**
     * @inheritDoc
     *
     * @param Customer $model
     */
    public function getUrl($model)
    {
        return Url::to(['/crm/admin/customer/view', 'id' => $model->id]);
    }

    /**
     * @inheritDoc
     *
     * @param Customer $model
     */
    public function getLink($model)
    {
        return Html::a(Html::encode($model->name), $this->getUrl($model), [
            'data-lazy-modal' => 'customer-view-modal',
            'data-lazy-container' => '#main-container',
        ]);
    }
}
