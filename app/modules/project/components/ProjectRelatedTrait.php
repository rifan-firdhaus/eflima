<?php namespace modules\project\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\project\models\Project;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
trait ProjectRelatedTrait
{

    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return Yii::t('app', 'Project');
    }

    /**
     * @inheritDoc
     */
    public function getModel($id)
    {
        return Project::find()->andWhere(['id' => $id])->one();
    }

    /**
     * @param Project $model
     *
     * @inheritDoc
     */
    public function getName($model)
    {
        return $model->name;
    }

    /**
     * @param Project $model
     *
     * @inheritDoc
     */
    public function validate($model, $project)
    {
        if (!$model) {
            $project->addError('model_id', Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Project'),
            ]));
        }
    }

    /**
     * @inheritDoc
     *
     * @param Project $model
     */
    public function getUrl($model)
    {
        return Url::to(['/project/admin/project/view', 'id' => $model->id]);
    }

    /**
     * @inheritDoc
     *
     * @param Project $model
     */
    public function getLink($model)
    {
        return Html::a(Html::encode($model->name), $this->getUrl($model), [
            'data-lazy-modal' => 'project-view-modal',
            'data-lazy-container' => '#main-container',
        ]);
    }
}