<?php namespace modules\finance\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\db\ActiveQuery;
use modules\finance\models\Invoice;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\finance\models\Invoice]].
 *
 * @see    Invoice
 */
class InvoiceQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return Invoice[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Invoice|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
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

    /**
     * @param bool $isPastDue
     *
     * @return $this
     */
    public function pastDue($isPastDue = true)
    {
        if (!$isPastDue) {
            return $this->andWhere(['>', "{$this->getAlias()}.due_date", time()]);
        }

        return $this->andWhere(['<=', "{$this->getAlias()}.due_date", time()])->notPaid();
    }

    /**
     * @return InvoiceQuery
     */
    public function notPaid()
    {
        return $this->paid(0);
    }

    /**
     * @param $isPaid
     *
     * @return $this
     */
    public function paid($isPaid)
    {
        return $this->andWhere(["{$this->getAlias()}.is_paid" => $isPaid]);
    }

    /**
     * @param boolean $hasPayment
     *
     * @return $this
     */
    public function hasPayment($hasPayment = true)
    {
        if (!$hasPayment) {
            return $this->andWhere([
                'OR',
                ["{$this->getAlias()}.total_paid" => 0],
                ["{$this->getAlias()}.total_paid" => null],
            ]);
        }

        return $this->andWhere(['>', "{$this->getAlias()}.total_paid", 0]);
    }

    /**
     * @param bool $hasPaymentDue
     *
     * @return $this
     */
    public function hasPaymentDue($hasPaymentDue = true)
    {
        if (!$hasPaymentDue) {
            return $this->andWhere(["{$this->getAlias()}.total_due" => 0]);
        }

        return $this->andWhere(['>', "{$this->getAlias()}.total_due", 0]);
    }
}
