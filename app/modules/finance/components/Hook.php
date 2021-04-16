<?php namespace modules\finance\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use app\modules\account\web\admin\Application as AdminApplication;
use modules\account\models\Staff;
use modules\calendar\models\EventMember;
use modules\core\components\HookTrait;
use modules\core\components\SettingRenderer;
use modules\crm\models\Customer;
use modules\crm\models\Lead;
use modules\finance\models\Expense;
use modules\finance\models\Invoice;
use modules\finance\models\InvoiceAssignee;
use modules\finance\models\InvoiceItem;
use modules\finance\models\Proposal;
use modules\finance\models\ProposalAssignee;
use Yii;
use yii\base\Event;
use yii\base\InvalidConfigException;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Hook
{
    use HookTrait;

    protected function __construct()
    {
        Event::on(InvoiceItem::class, InvoiceItem::EVENT_AFTER_DELETE, [Expense::class, 'eventInvoiceItemDeleted']);
        Event::on(InvoiceItem::class, InvoiceItem::EVENT_AFTER_UPDATE, [Expense::class, 'eventInvoiceItemSaved']);
        Event::on(InvoiceItem::class, InvoiceItem::EVENT_AFTER_INSERT, [Expense::class, 'eventInvoiceItemSaved']);

        Event::on(SettingRenderer::class, SettingRenderer::EVENT_INIT, [$this, 'registerSetting']);

        if (Yii::$app instanceof AdminApplication) {
            AdminHook::instance();
        }

        Event::on(Customer::class, Customer::EVENT_BEFORE_DELETE, [Proposal::class, 'deleteAllProposalRelatedToDeletedCustomer']);
        Event::on(Customer::class, Customer::EVENT_BEFORE_DELETE, [Invoice::class, 'deleteAllInvoiceRelatedToDeletedCustomer']);
        Event::on(Customer::class, Customer::EVENT_BEFORE_DELETE, [Expense::class, 'deleteAllExpenseRelatedToDeletedCustomer']);

        Event::on(Lead::class, Lead::EVENT_BEFORE_DELETE, [Proposal::class, 'deleteAllProposalRelatedToDeletedLead']);

        Event::on(Staff::class, Staff::EVENT_BEFORE_DELETE, [ProposalAssignee::class, 'deleteAllRelatedToDeletedStaff']);
        Event::on(Staff::class, Staff::EVENT_BEFORE_DELETE, [InvoiceAssignee::class, 'deleteAllRelatedToDeletedStaff']);
    }

    /**
     * @param Event $event
     *
     * @throws InvalidConfigException
     */
    public function registerSetting($event)
    {
        /** @var SettingRenderer $renderer */
        $renderer = $event->sender;

        $renderer->addObject(SettingObject::class);
    }
}
