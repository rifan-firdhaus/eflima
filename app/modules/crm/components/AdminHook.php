<?php namespace modules\crm\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\web\admin\Controller;
use modules\account\web\admin\View;
use modules\account\widgets\history\HistoryWidget;
use modules\account\widgets\history\HistoryWidgetEvent;
use modules\core\components\HookTrait;
use modules\core\controllers\admin\SettingController;
use modules\crm\models\LeadStatus;
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

    public $historyShortDescription = [
        'lead' => [
            'lead.add' => 'Creating lead',
            'lead.update' => 'Updating lead',
            'lead.status' => 'Changing status to "{status_label}"',
            'lead_assignee.delete' => 'Removing assignment of "{assignee_name}"',
            'lead_assignee.add' => 'Assigning "{assignee_name}"',
        ],
        'customer' => [
            'customer_contact.add' => 'Adding contact "{name}"',
            'customer_contact.update' => 'Updating contact "{name}"',
        ],
    ];

    public $historyOptions = [
        'lead.status' => [
            'icon' => 'i8:hammer',
        ],
        'lead_assignee.add' => [
            'icon' => 'i8:link',
            'iconOptions' => ['class' => 'icon bg-info'],
        ],
        'lead_assignee.delete' => [
            'icon' => 'i8:broken-link',
            'iconOptions' => ['class' => 'icon bg-warning'],
        ],
    ];

    protected function __construct()
    {
        Event::on(Controller::class, Controller::EVENT_BEFORE_ACTION, [$this, 'beforeAction']);

        Event::on(SettingController::class, SettingController::EVENT_INIT, [$this, 'registerSettingPermission']);
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
            'roles' => ['admin.setting.crm.general'],
            'matchCallback' => function () {
                return Yii::$app->request->get('section') === 'crm';
            },
        ], $accessBehaviors->ruleConfig));
    }
    /**
     * @param Event $event
     */
    public function beforeAction($event)
    {
        /** @var Controller $controller */
        $controller = $event->sender;

        if (!Yii::$app->user->isGuest) {
            $this->registerMenu($controller->view);

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
            'lead_assignee.add',
            'lead_assignee.delete',
        ])) {
            $event->params['lead_name'] = Html::a([
                'label' => Html::encode($model->params['lead_name']),
                'url' => ['/crm/admin/lead/view', 'id' => $model->params['lead_id']],
                'class' => 'important',
                'data-lazy-container' => '#main-container',
                'data-lazy-modal' => 'lead-view-modal',
            ]);

            if (in_array($model->key, ['lead_assignee.add', 'lead_assignee.delete'])) {
                $event->params['assignee_name'] = Html::a([
                    'label' => Html::encode($model->params['assignee_name']),
                    'url' => ['/account/admin/staff/profile', 'id' => $model->params['assignee_id']],
                    'class' => 'important',
                ]);
            }
        } elseif (in_array($model->key, [
            'lead.add',
            'lead.delete',
            'lead.update',
            'lead.status',
        ])) {
            $event->params['name'] = Html::a([
                'label' => Html::encode($model->params['name']),
                'url' => ['/crm/admin/lead/view', 'id' => $model->params['id']],
                'class' => 'important',
                'data-lazy-container' => '#main-container',
                'data-lazy-modal' => 'lead-view-modal',
            ]);

            if ($model->key == 'lead.status') {
                $statusColor = LeadStatus::find()
                    ->andWhere(['id' => $model->params['status_id']])
                    ->select('color_label')
                    ->createCommand()
                    ->queryScalar();

                $event->params['status_label'] = Html::tag('span', $model->params['status_label'], [
                    'style' => ($statusColor ? "color:{$statusColor}" : false),
                    'class' => 'important',
                ]);
            }
        } elseif (in_array($model->key, [
            'customer_contact.add',
            'customer_contact.delete',
            'customer_contact.update',
        ])) {
            $event->params['name'] = Html::a([
                'label' => Html::encode($model->params['name']),
                'url' => ['/crm/admin/customer-contact/update', 'id' => $model->params['id']],
                'class' => 'important',
                'data-lazy-container' => '#main-container',
                'data-lazy-modal' => 'customer-contact-form-modal',
            ]);
            $event->params['customer_name'] = Html::a([
                'label' => Html::encode($model->params['customer_name']),
                'url' => ['/crm/admin/customer/view', 'id' => $model->params['customer_id']],
                'class' => 'important',
                'data-lazy-container' => '#main-container',
                'data-lazy-modal' => 'customer-view-modal',
            ]);
        }


        if (isset($this->historyOptions[$model->key])) {
            foreach ($this->historyOptions[$model->key] AS $attribute => $value) {
                $event->{$attribute} = $value;
            }
        }

        if ($widget->realId == 'lead-history' && isset($this->historyShortDescription['lead'][$model->key])) {
            $event->description = $this->historyShortDescription['lead'][$model->key];
        }

        if ($widget->realId == 'customer-history' && isset($this->historyShortDescription['customer'][$model->key])) {
            $event->description = $this->historyShortDescription['customer'][$model->key];
        }
    }

    /**
     * @param View $view
     */
    protected function registerMenu($view)
    {
        $view->menu->addItems([
            'main/crm' => [
                'label' => Yii::t('app', 'Customer Relation'),
                'icon' => 'i8:user-menu-male',
                'sort' => 1,
                'options' => [
                    'class' => 'heading',
                ],
            ],
            'main/crm/customer' => [
                'label' => Yii::t('app', 'Customer'),
                'icon' => 'i8:contacts',
                'url' => ['/crm/admin/customer/index'],
                'sort' => 1,
                'visible' => Yii::$app->user->can('admin.customer.list'),
                'linkOptions' => [
                    'data-lazy-link' => true,
                    'data-lazy-container' => '#main-container',
                ],
            ],
            'main/crm/lead' => [
                'label' => Yii::t('app', 'Leads'),
                'icon' => 'i8:connect',
                'url' => ['/crm/admin/lead/index'],
                'sort' => 1,
                'visible' => Yii::$app->user->can('admin.lead.list'),
                'linkOptions' => [
                    'data-lazy-link' => true,
                    'data-lazy-container' => '#main-container',
                ],
            ],
            'main/crm/contracts' => [
                'label' => Yii::t('app', 'Contract'),
                'icon' => 'i8:sign-up',
                'url' => ['/crm/admin/contract/index'],
                'sort' => 1,
                'linkOptions' => [
                    'data-lazy-link' => true,
                    'data-lazy-container' => '#main-container',
                ],
            ],
            'setting/crm' => [
                'label' => Yii::t('app', 'Customer Relation'),
                'icon' => 'i8:address-book',
                'url' => ['/crm/admin/setting/index'],
                'linkOptions' => [
                    'data-lazy-container' => '#main-container',
                    'data-lazy-link' => true,
                ],
            ],
        ]);

        if (Yii::$app->hasModule('quick_access')) {
            $view->menu->addItems([
                'quick_access/quick_add/customer' => [
                    'label' => Yii::t('app', 'Customer'),
                    'sort' => 1,
                    'icon' => 'i8:contacts',
                    'url' => ['/crm/admin/customer/add'],
                    'linkOptions' => [
                        'data-lazy-modal' => 'customer-form-modal',
                        'data-lazy-container' => '#main-container',
                        'data-lazy-link' => true,
                        'class' => 'nav-link side-panel-close',
                    ],
                ],
                'quick_access/quick_add/contact' => [
                    'label' => Yii::t('app', 'Contact'),
                    'sort' => 2,
                    'icon' => 'i8:address-book',
                    'url' => ['/crm/admin/customer-contact/add'],
                    'linkOptions' => [
                        'data-lazy-modal' => 'customer-contact-form-modal',
                        'data-lazy-container' => '#main-container',
                        'data-lazy-link' => true,
                        'class' => 'nav-link side-panel-close',
                    ],
                ],
                'quick_access/quick_add/lead' => [
                    'label' => Yii::t('app', 'Lead'),
                    'sort' => 3,
                    'icon' => 'i8:connect',
                    'url' => ['/crm/admin/lead/add'],
                    'linkOptions' => [
                        'data-lazy-modal' => 'lead-form-modal',
                        'data-lazy-container' => '#main-container',
                        'data-lazy-link' => true,
                        'class' => 'nav-link side-panel-close',
                    ],
                ],
                'quick_access/quick_add/contract' => [
                    'label' => Yii::t('app', 'Contract'),
                    'icon' => 'i8:signature',
                    'url' => ['/crm/admin/contract/add'],
                    'linkOptions' => [
                        'data-lazy-modal' => 'lead-form-modal',
                        'data-lazy-container' => '#main-container',
                        'data-lazy-link' => true,
                        'class' => 'nav-link side-panel-close',
                    ],
                ],
            ]);
        }
    }
}
