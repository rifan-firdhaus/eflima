<?php namespace modules\finance\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\db\ActiveQuery;
use modules\finance\models\InvoicePayment;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\finance\models\InvoicePayment]].
 *
 * @see    InvoicePayment
 */
class InvoicePaymentQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return InvoicePayment[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return InvoicePayment|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param $status
     *
     * @return $this
     */
    public function status($status)
    {
        return $this->andWhere(["{$this->getAlias()}.status" => $status]);
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
            ['>=', "{$this->getAlias()}.accepted_at", $timeStart],
            ['<=', "{$this->getAlias()}.accepted_at", $timeEnd],
        ]);
    }
}
