<?php namespace modules\project\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\project\models\ProjectMilestone;
use modules\ui\widgets\inputs\Select2Input;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ProjectMilestoneInput extends Select2Input
{
    public $project_id;
    public $projectInputSelector;
    public $url = ['/project/admin/project-milestone/auto-complete'];

    /**
     * @return array
     */
    public function buildUrl()
    {
        $url = $this->url;
        $queryableAttributes = ['project_id'];

        foreach ($queryableAttributes AS $attribute) {
            if (!is_null($this->{$attribute}) && $this->{$attribute} !== '') {
                $url[$attribute] = $this->{$attribute};
            }
        }

        return $url;
    }

    /**
     * @inheritdoc
     */
    public function normalize()
    {
        $params = [
            'q' => new JsExpression('params.term'),
            'page' => new JsExpression('params.page'),
            'project_id' => new JsExpression('params.project_id'),
        ];

        if (isset($this->projectInputSelector)) {
            $params['project_id'] = new JsExpression("$('{$this->projectInputSelector}').val()");
        }

        $params = Json::encode($params);

        $this->jsOptions['ajax'] = [
            'url' => Url::to($this->buildUrl()),
            'data' => new JsExpression("function (params) {return {$params};}"),
        ];

        if (!$this->selected) {
            $this->selected = function ($value, $select2) {
                if ($select2->multiple) {
                    is_array($value) || ($value = explode(',', $value));

                    return ProjectMilestone::find()->andWhere(['id' => $value])->map('id', 'name');
                }

                $model = ProjectMilestone::find()->andWhere(['id' => $value])->one();

                if ($model) {
                    return [$value => $model->name];
                }
            };
        }

        parent::normalize();
    }
}