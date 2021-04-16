<?php namespace modules\project\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\components\CommentRelation;
use modules\project\models\ProjectDiscussionTopic;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ProjectDiscussionTopicCommentRelation extends CommentRelation
{
    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return Yii::t('app', 'Topic');
    }

    /**
     * @inheritDoc
     */
    public function getModel($id)
    {
        return ProjectDiscussionTopic::find()->andWhere(['id' => $id])->one();
    }

    /**
     * @inheritDoc
     */
    public function isActive($modelId = null)
    {
        return Yii::$app->user->can('admin.project.view.discussion');
    }

    /**
     * @param ProjectDiscussionTopic $model
     *
     * @inheritDoc
     */
    public function getName($model)
    {
        return $model->subject;
    }

    /**
     * @param ProjectDiscussionTopic $model
     *
     * @inheritDoc
     */
    public function validate($model, $project)
    {
        if (!$model) {
            $project->addError('model_id', Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Topic'),
            ]));
        }
    }

    /**
     * @inheritDoc
     *
     * @param ProjectDiscussionTopic $model
     */
    public function getUrl($model)
    {
        return Url::to(['/project/admin/project-/view', 'id' => $model->id]);
    }

    /**
     * @inheritDoc
     *
     * @param ProjectDiscussionTopic $model
     */
    public function getLink($model)
    {
        return Html::a(Html::encode($model->subject), $this->getUrl($model), [
            'data-lazy-modal' => 'project-view-modal',
            'data-lazy-container' => '#main-container',
        ]);
    }
}
