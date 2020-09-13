<?php namespace modules\task\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\components\BaseSettingObject;
use modules\task\models\TaskPriority;
use modules\task\models\TaskStatus;
use modules\task\widgets\inputs\TaskPriorityInput;
use modules\task\widgets\inputs\TaskStatusInput;
use modules\ui\widgets\form\fields\ActiveField;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class SettingObject extends BaseSettingObject
{
    /**
     * @inheritdoc
     */
    public function render()
    {
        switch ($this->renderer->section) {
            case 'task':
                $this->renderTaskSection();
                break;
        }
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        switch ($this->renderer->section) {
            case 'task':
                $this->initTaskSection();
                break;
        }
    }

    protected function initTaskSection()
    {
        $this->renderer->view->on('block:core/admin/setting/index:begin', function () {
            echo $this->renderer->view->render('@modules/task/views/admin/setting/menu', ['active' => 'task-setting']);
        });

        $this->renderer->addFields([
            'task/default_status' => [
                'label' => Yii::t('app', 'Default Status'),
                'rules' => [
                    'required',
                ],
            ],
            'task/completed_status' => [
                'label' => Yii::t('app', 'Completed Status'),
                'rules' => [
                    'required',
                ],
            ],
            'task/closed_status' => [
                'label' => Yii::t('app', 'Closed Status'),
                'rules' => [
                    'required',
                ],
            ],
            'task/default_priority' => [
                'label' => Yii::t('app', 'Default Priority'),
                'rules' => [
                    'required',
                ],
            ],
            'task/is_subtask_allowed' => [
                'label' => Yii::t('app', 'Allow task inside a task (Sub task capability)'),
                'rules' => [
                    'boolean',
                ],
            ],
            'task/is_checklist_allowed' => [
                'label' => Yii::t('app', 'Allow add checklist inside task'),
                'rules' => [
                    'boolean',
                ],
            ],
            'task/notify_before_deadline_period' => [
                'label' => Yii::t('app', 'Notify assignee when'),
                'rules' => [
                    ['default', 'value' => null],
                ],
            ],
        ]);

        $this->renderer->addSubSection('general', [
            'label' => false,
        ]);
    }

    protected function renderTaskSection()
    {
        $taskStatusQuery = TaskStatus::find()->enabled();
        $taskPriorityQuery = TaskPriority::find()->enabled();

        $this->renderer->getSubSection('general')->addFields([
            [
                'type' => ActiveField::TYPE_WIDGET,
                'attribute' => 'value',
                'model' => $this->renderer->getModel('task/default_status'),
                'widget' => [
                    'class' => TaskStatusInput::class,
                    'query' => $taskStatusQuery,
                ],
            ],
            [
                'type' => ActiveField::TYPE_WIDGET,
                'attribute' => 'value',
                'model' => $this->renderer->getModel('task/completed_status'),
                'hint' => Yii::t('app', 'Status of the task will be changed to this status automatically once the progress of the task reach 100%'),
                'widget' => [
                    'class' => TaskStatusInput::class,
                    'query' => $taskStatusQuery,
                ],
            ],
            [
                'type' => ActiveField::TYPE_WIDGET,
                'attribute' => 'value',
                'model' => $this->renderer->getModel('task/closed_status'),
                'hint' => Yii::t('app', 'Closed status means that the task is fully completed and closed, no interaction will be allowed for the task with closed status'),
                'widget' => [
                    'class' => TaskStatusInput::class,
                    'query' => $taskStatusQuery,
                ],
            ],
            [
                'type' => ActiveField::TYPE_WIDGET,
                'attribute' => 'value',
                'model' => $this->renderer->getModel('task/default_priority'),
                'widget' => [
                    'class' => TaskPriorityInput::class,
                    'query' => $taskPriorityQuery,
                ],
            ],
            [
                'type' => ActiveField::TYPE_CHECKBOX,
                'attribute' => 'value',
                'label' => '',
                'model' => $this->renderer->getModel('task/is_subtask_allowed'),
                'inputOptions' => [
                    'custom' => true,
                ],
            ],
            [
                'type' => ActiveField::TYPE_CHECKBOX,
                'attribute' => 'value',
                'label' => '',
                'model' => $this->renderer->getModel('task/is_checklist_allowed'),
                'inputOptions' => [
                    'custom' => true,
                ],
            ],
        ]);
    }

}