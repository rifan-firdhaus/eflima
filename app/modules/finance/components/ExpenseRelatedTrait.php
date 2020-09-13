<?php namespace modules\finance\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\finance\models\Expense;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
trait ExpenseRelatedTrait
{
    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return Yii::t('app', 'Expense');
    }

    /**
     * @inheritDoc
     *
     * @return Expense|null
     * @throws InvalidConfigException
     */
    public function getModel($id)
    {
        return Expense::find()->andWhere(['id' => $id])->one();
    }

    /**
     * @inheritDoc
     *
     * @param Expense $model
     */
    public function getName($model)
    {
        return $model->name;
    }

    /**
     * @inheritDoc
     */
    public function validate($model, $task)
    {
        if (!$model) {
            $task->addError('model_id', Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Task'),
            ]));
        }
    }

    /**
     * @inheritDoc
     *
     * @param Expense $model
     */
    public function getUrl($model)
    {
        return Url::to(['/finance/admin/expense/view', 'id' => $model->id]);
    }

    /**
     * @inheritDoc
     *
     * @param Expense $model
     */
    public function getLink($model)
    {
        return Html::a(Html::encode($model->name), $this->getUrl($model), [
            'data-lazy-modal' => 'expense-view-modal',
            'data-lazy-container' => '#main-container',
        ]);
    }
}