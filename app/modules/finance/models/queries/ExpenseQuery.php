<?php

namespace modules\finance\models\queries;

use modules\core\db\ActiveQuery;
use modules\core\models\traits\VisibilityQuery;
use modules\finance\models\Expense;

/**
 * This is the ActiveQuery class for [[\modules\finance\models\Expense]].
 *
 * @see Expense
 */
class ExpenseQuery extends ActiveQuery
{
    use VisibilityQuery;

    /**
     * @inheritdoc
     *
     * @return Expense[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return Expense|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @return $this
     */
    public function nonBillable()
    {
        return $this->billable(false);
    }

    /**
     * @param bool $isBillable
     *
     * @return $this
     */
    public function billable($isBillable = true)
    {
        return $this->andWhere(["{$this->getAlias()}.is_billable" => $isBillable]);
    }

    /**
     * @return $this
     */
    public function notBilled()
    {
        return $this->billed(false);
    }

    /**
     * @param boolean $isBilled
     *
     * @return $this
     */
    public function billed($isBilled = true)
    {
        if (!$isBilled) {
            return $this->billable()->andWhere(["{$this->getAlias()}.invoice_item_id" => null]);
        }

        return $this->billable()->andWhere(['IS NOT', "{$this->getAlias()}.invoice_item_id", null]);
    }

    /**
     * @return $this
     */
    public function readyToInvoiced()
    {
        return $this->billable()->andWhere([
            "{$this->getAlias()}.invoice_item_id" => null,
        ]);
    }

    /**
     * @param int $timeStart
     * @param int $timeEnd
     *
     * @return $this
     */
    public function dateRange($timeStart, $timeEnd)
    {
        return $this->andWhere([
            'AND',
            ['>=', "{$this->getAlias()}.date", $timeStart],
            ['<=', "{$this->getAlias()}.date", $timeEnd],
        ]);
    }
}
