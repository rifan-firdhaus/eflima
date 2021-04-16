<?php namespace modules\task\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\StaffAccount;
use modules\account\web\admin\Controller;
use modules\account\web\admin\View;
use modules\account\widgets\history\HistoryWidget;
use modules\account\widgets\history\HistoryWidgetEvent;
use modules\core\components\HookTrait;
use modules\core\controllers\admin\SettingController;
use modules\note\controllers\admin\NoteController;
use modules\task\assets\admin\TaskAsset;
use modules\task\models\Task as TaskModel;
use modules\task\models\TaskPriority;
use modules\task\models\TaskStatus;
use Yii;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\helpers\Html;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class AdminHook
{
    use HookTrait;

    protected $historyShortDescription = [
        'task_timer.start' => 'Starting timer',
        'task_timer.stop' => 'Stopping timer "{duration}"',
        'task.update' => 'Updating task',
        'task.add' => 'Adding task',
        'task.status' => 'Changing status to "{status_label}"',
        'task.priority' => 'Changing priority to "{priority_label}"',
        'task.progress' => 'Set progress to {progress}%',
        'task_assignee.delete' => 'Removing assignment of "{assignee_name}"',
        'task_assignee.add' => 'Assigning "{assignee_name}"',
        'task_checklist.add' => 'Adding checklist "{label}"',
        'task_checklist.update' => 'Updating checklist "{label}"',
        'task_checklist.check' => 'Checking checklist "{label}"',
        'task_checklist.uncheck' => 'Unchecking checklist "{label}"',
    ];

    protected $historyOptions = [
        'task_checklist.check' => [
            'icon' => 'i8:checkmark',
            'iconOptions' => ['class' => 'icon bg-success'],
        ],
        'task_checklist.uncheck' => [
            'icon' => 'i8:multiply',
            'iconOptions' => ['class' => 'icon bg-warning'],
        ],
        'task.progress' => [
            'icon' => 'i8:double-tick',
            'iconOptions' => ['class' => 'icon bg-success'],
        ],
        'task.priority' => [
            'icon' => 'i8:sorting',
        ],
        'task.status' => [
            'icon' => 'i8:hammer',
        ],
        'task_assignee.add' => [
            'icon' => 'i8:link',
            'iconOptions' => ['class' => 'icon bg-info'],
        ],
        'task_assignee.delete' => [
            'icon' => 'i8:broken-link',
            'iconOptions' => ['class' => 'icon bg-warning'],
        ],
        'task_timer.start' => [
            'icon' => 'i8:play',
            'iconOptions' => ['class' => 'icon bg-success'],
        ],
        'task_timer.stop' => [
            'icon' => 'i8:stop',
            'description' => 'Stopping timer "{duration}" of task "{task_title}"',
            'iconOptions' => ['class' => 'icon bg-success'],
        ],
        'task_timer.add' => [
            'icon' => 'i8:stop',
            'description' => 'Adding time manually "{duration}" to task "{task_title}"',
            'iconOptions' => ['class' => 'icon bg-success'],
        ],
        'task_timer.update' => [
            'icon' => 'i8:stop',
            'description' => 'Updating time record "{duration}" of task "{task_title}"',
            'iconOptions' => ['class' => 'icon bg-success'],
        ],
    ];

    protected function __construct()
    {
        Event::on(NoteController::class, NoteController::EVENT_INIT, [$this, 'noteControllerBeforeAction']);
        Event::on(SettingController::class, SettingController::EVENT_INIT, [$this, 'registerSettingPermission']);
        Event::on(Controller::class, Controller::EVENT_BEFORE_ACTION, [$this, 'beforeAction']);
    }


    /**
     * @param Event $event
     *
     * @throws InvalidConfigException
     */
    public function registerSettingPermission($event)
    {
        /**
         * @var SettingController $settingController
         * @var AccessControl     $accessBehaviors
         */
        $settingController = $event->sender;
        $accessBehaviors = $settingController->getBehavior('access');

        $accessBehaviors->rules[] = Yii::createObject(array_merge([
            'allow' => true,
            'actions' => ['index'],
            'verbs' => ['GET', 'POST'],
            'roles' => ['admin.setting.task.general'],
            'matchCallback' => function () {
                return Yii::$app->request->get('section') === 'task';
            },
        ], $accessBehaviors->ruleConfig));
    }

    /**
     * @param Event $event
     */
    public function noteControllerBeforeAction($event)
    {
        /** @var NoteController $controller */
        $controller = $event->sender;
//
//        $controller->behaviors['access']->
    }

    /**
     * @param Event $event
     *
     * @throws InvalidConfigException
     */
    public function beforeAction($event)
    {
        /** @var Controller $controller */
        $controller = $event->sender;

        if (!Yii::$app->user->isGuest) {
            $this->registerMenu($controller->view);

            $controller->view->on(View::EVENT_BEGIN_PAGE, [$this, 'beginPage']);
            Event::on(HistoryWidget::class, HistoryWidget::EVEMT_RENDER_ITEM, [$this, 'renderHistoryWidgetItem']);
        }
    }

    /**
     * @param HistoryWidgetEvent $event
     *
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function renderHistoryWidgetItem($event)
    {
        /** @var HistoryWidget $widget */
        $widget = $event->sender;
        $model = $event->model;

        if (in_array($model->key, [
            'task_assignee.add',
            'task_assignee.delete',
            'task_timer.start',
            'task_timer.stop',
            'task_timer.update',
            'task_timer.add',
            'task_checklist.check',
            'task_checklist.uncheck',
            'task_checklist.update',
            'task_checklist.add',
        ])
        ) {
            $event->params['task_title'] = Html::a([
                'label' => Html::encode($model->params['task_title']),
                'url' => ['/task/admin/task/view', 'id' => $model->params['task_id']],
                'class' => 'important',
                'data-lazy-container' => '#main-container',
                'data-lazy-modal' => 'task-view-modal',
            ]);

            if (in_array($model->key, ['task_assignee.add', 'task_assignee.delete'])) {
                $event->params['assignee_name'] = Html::a([
                    'label' => Html::encode($model->params['assignee_name']),
                    'url' => ['/account/admin/staff/profile', 'id' => $model->params['assignee_id']],
                    'class' => 'important',
                ]);
            } elseif (in_array($model->key, ['task_timer.stop', 'task_timer.update', 'task_timer.add'])) {
                $event->params['duration'] = Html::tag('span', Yii::$app->formatter->asDuration($model->params['stopped_at'] - $model->params['started_at']), ['class' => 'important']);
            } elseif (in_array($model->key, ['task_checklist.update', 'task_checklist.add', 'task_checklist.uncheck'])) {
                $event->params['label'] = Html::tag('span', Html::encode($model->params['label']), [
                    'class' => 'important',
                ]);
            } elseif ($model->key === 'task_checklist.check') {
                $event->params['label'] = Html::tag('span', Html::encode($model->params['label']), [
                    'style' => ['text-decoration' => 'line-through'],
                    'class' => 'important',
                ]);
            }
        } elseif (in_array($model->key, [
            'task.add',
            'task.delete',
            'task.progress',
            'task.update',
            'task.status',
            'task.priority',
        ])) {
            $event->params = [
                'title' => Html::a([
                    'label' => Html::encode($model->params['title']),
                    'url' => ['/task/admin/task/view', 'id' => $model->params['id']],
                    'class' => 'important',
                    'data-lazy-container' => '#main-container',
                    'data-lazy-modal' => 'task-view-modal',
                ]),
            ];

            if ($model->key == 'task.status') {
                $statusColor = TaskStatus::find()
                    ->andWhere(['id' => $model->params['status_id']])
                    ->select('color_label')
                    ->createCommand()
                    ->queryScalar();
                $event->params['status_label'] = Html::tag('span', $model->params['status_label'], [
                    'style' => ($statusColor ? "color:{$statusColor}" : false),
                    'class' => 'important',
                ]);
            } elseif ($model->key == 'task.priority') {
                $priorityColor = TaskPriority::find()->andWhere(['id' => $model->params['priority_id']])
                    ->select('color_label')
                    ->createCommand()
                    ->queryScalar();
                $event->params['priority_label'] = Html::tag('span', $model->params['priority_label'], [
                    'style' => ($priorityColor ? "color:{$priorityColor}" : false),
                    'class' => 'important',
                ]);
            } elseif ($model->key == 'task.progress') {
                $event->params['progress'] = Html::tag('span', Yii::$app->formatter->asDecimal($model->params['progress']), ['class' => 'text-success important']);
            }
        }

        if (isset($this->historyOptions[$model->key])) {
            foreach ($this->historyOptions[$model->key] AS $attribute => $value) {
                $event->{$attribute} = $value;
            }
        }

        if ($widget->realId == 'task-history' && isset($this->historyShortDescription[$model->key])) {
            $event->description = $this->historyShortDescription[$model->key];
        }
    }

    /**
     * @param Event $event
     */
    public function beginPage($event)
    {
        /** @var View $view */
        $view = $event->sender;

        // render sidepanel for active timer
        $view->addBlock('account/layouts/admin/main:begin', Html::tag('div', '', [
            'id' => 'timer-panel',
            'class' => 'side-panel',
        ]));

        // Register task asset
        if ($view->getRequestedViewFile() === Yii::getAlias('@modules/account/views/layouts/admin/main.php')) {
            TaskAsset::register($view);
        }
    }

    /**
     * @param View $view
     *
     * @throws InvalidConfigException
     */
    protected function registerMenu($view)
    {
        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;
        $activeTimer = TaskModel::find()->runningTimer($account->profile->id)->count();
        $view->menu->addItems([
            'sidenav/top/task_timer' => [
                'label' => Yii::t('app', 'Task Timer'),
                'content' => Html::tag('span', $activeTimer, [
                    'class' => 'badge timer-count-badge badge-danger rounded',
                    'data-count' => $activeTimer,
                ]),
                'icon' => 'i8:timer',
                'url' => ['/task/admin/task/active-timers'],
                'linkOptions' => [
                    'data-lazy-container' => '#timer-panel',
                    'data-lazy-link' => true,
                ],
            ],
            'main/task' => [
                'label' => Yii::t('app', 'Task'),
                'icon' => 'i8:checked',
                'url' => ['/task/admin/task/index'],
                'sort' => 1,
                'linkOptions' => [
                    'data-lazy-link' => true,
                    'data-lazy-container' => '#main-container',
                ],
            ],
            'setting/task' => [
                'label' => Yii::t('app', 'Task'),
                'url' => ['/task/admin/setting/index'],
                'icon' => 'i8:checked',
                'linkOptions' => [
                    'data-lazy-container' => '#main-container',
                    'data-lazy-link' => true,
                ],
            ],
        ]);

        if (Yii::$app->hasModule('quick_access')) {
            $view->menu->addItems([
                'quick_access/quick_add/task' => [
                    'label' => Yii::t('app', 'Task'),
                    'icon' => 'i8:list',
                    'url' => ['/task/admin/task/add'],
                    'linkOptions' => [
                        'data-lazy-modal' => 'task-form-modal',
                        'data-lazy-container' => '#main-container',
                        'data-lazy-link' => true,
                        'class' => 'nav-link side-panel-close',
                    ],
                ],
            ]);
        }
    }
}
