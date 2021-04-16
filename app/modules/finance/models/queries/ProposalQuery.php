<?php namespace modules\finance\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\db\ActiveQuery;
use modules\finance\models\ProposalStatus;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\crm\models\ProposalStatus]].
 *
 * @see    \modules\finance\models\Proposal
 */
class ProposalQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return ProposalStatus[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return ProposalStatus|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
