<?php namespace modules\task\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\project\models\Project;
use modules\task\models\Task;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
trait TaskRelatedTrait
{
    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return Yii::t('app', 'Task');
    }

    /**
     * @inheritDoc
     */
    public function getModel($id)
    {
        return Task::find()->andWhere(['id' => $id])->one();

    }

    /**
     * @param Task $model
     *
     * @inheritDoc
     */
    public function getName($model)
    {
        return $model->title;
    }

    /**
     * @param Task $model
     *
     * @inheritDoc
     */
    public function validate($model, $note)
    {
        if (!$model) {
            $note->addError('model_id', Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Task'),
            ]));
        }
    }

    /**
     * @inheritDoc
     *
     * @param Task $model
     */
    public function getUrl($model)
    {
        return Url::to(['/task/admin/task/view', 'id' => $model->id]);
    }

    /**
     * @inheritDoc
     *
     * @param Task $model
     */
    public function getLink($model)
    {
        return Html::a(Html::encode($model->title), $this->getUrl($model), [
            'data-lazy-modal' => 'task-view-modal',
            'data-lazy-container' => '#main-container',
        ]);
    }
}