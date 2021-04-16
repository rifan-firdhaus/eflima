<?php namespace modules\finance;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\components\CommentRelation;
use modules\core\base\Module;
use modules\finance\components\CustomerProposalRelation;
use modules\finance\components\LeadProposalRelation;
use modules\finance\components\BankTransferPayment;
use modules\finance\components\CashPayment;
use modules\finance\components\ExpenseCommentRelation;
use modules\finance\components\ExpenseNoteRelation;
use modules\finance\components\ExpenseQuickSearch;
use modules\finance\components\ExpenseTaskRelation;
use modules\finance\components\Hook;
use modules\finance\components\InvoiceCommentRelation;
use modules\finance\components\InvoiceNoteRelation;
use modules\finance\components\InvoicePaymentCommentRelation;
use modules\finance\components\InvoicePaymentNoteRelation;
use modules\finance\components\InvoicePaymentQuickSearch;
use modules\finance\components\InvoiceQuickSearch;
use modules\finance\components\InvoiceTaskRelation;
use modules\finance\components\Payment;
use modules\finance\components\ProposalCommentRelation;
use modules\finance\components\ProposalNoteRelation;
use modules\finance\components\ProposalQuickSearch;
use modules\finance\components\ProposalRelation;
use modules\finance\components\ProposalTaskRelation;
use modules\finance\models\Proposal;
use modules\note\components\NoteRelation;
use modules\quick_access\components\QuickSearch;
use modules\task\components\TaskRelation;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Finance extends Module
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        Yii::$app->formatter->currencyCode = Yii::$app->setting->get('finance/base_currency');

        Hook::instance();

        Payment::register('cash', CashPayment::class);
        Payment::register('bank_transfer', BankTransferPayment::class);

        ProposalRelation::register('customer', CustomerProposalRelation::class);
        ProposalRelation::register('lead', LeadProposalRelation::class);


        if (Yii::$app->hasModule('quick_access')) {
            QuickSearch::register(ExpenseQuickSearch::class);
            QuickSearch::register(InvoiceQuickSearch::class);
            QuickSearch::register(InvoicePaymentQuickSearch::class);
            QuickSearch::register(ProposalQuickSearch::class);
        }

        if (Yii::$app->hasModule('task')) {
            TaskRelation::register('invoice', InvoiceTaskRelation::class);
            TaskRelation::register('expense', ExpenseTaskRelation::class);

            TaskRelation::register('proposal', ProposalTaskRelation::class);
        }

        NoteRelation::register('invoice', InvoiceNoteRelation::class);
        NoteRelation::register('expense', ExpenseNoteRelation::class);
        NoteRelation::register('invoice_payment', InvoicePaymentNoteRelation::class);
        NoteRelation::register('proposal', ProposalNoteRelation::class);

        CommentRelation::register('invoice', InvoiceCommentRelation::class);
        CommentRelation::register('expense', ExpenseCommentRelation::class);
        CommentRelation::register('invoice_payment', InvoicePaymentCommentRelation::class);
        CommentRelation::register('proposal', ProposalCommentRelation::class);
    }

}
