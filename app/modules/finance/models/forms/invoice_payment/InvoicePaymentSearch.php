<?php namespace modules\finance\models\forms\invoice_payment;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\components\SearchableModelEvent;
use modules\core\models\interfaces\SearchableModel;
use modules\core\models\traits\SearchableModelTrait;
use modules\core\validators\DateValidator;
use modules\finance\models\InvoicePayment;
use modules\finance\models\queries\CurrencyQuery;
use modules\finance\models\queries\InvoicePaymentQuery;
use modules\finance\models\queries\InvoiceQuery;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property InvoicePaymentQuery $query
 * @property ActiveDataProvider  $dataProvider
 *
 * @property float               $totalAmountYesterday
 * @property float               $totalAmountGrowthThisYear
 * @property float               $totalAmountThisYear
 * @property float               $totalAmountGrowthToday
 * @property float               $totalAmountLastYear
 * @property float               $totalAmountThisMonth
 * @property float               $totalAmountGrowthThisMonth
 * @property float               $totalAmountToday
 * @property float               $totalAmountLastMonth
 * @property string|float        $totalAmount
 */
class InvoicePaymentSearch extends InvoicePayment implements SearchableModel
{
    use SearchableModelTrait;

    public $q;

    public $at_from;
    public $at_to;
    public $accepted_at_to;
    public $accepted_at_from;
    public $customer_id;

    public function rules()
    {
        return [
            [['q', 'at_to', 'accepted_at_to', 'customer_id', 'invoice_id', 'method_id'], 'safe'],
            [
                'at_from',
                'daterange',
                'fullDay' => true,
                'type' => DateValidator::TYPE_DATE,
                'dateTo' => 'at_to',
            ],
            [
                'accepted_at_from',
                'daterange',
                'fullDay' => true,
                'type' => DateValidator::TYPE_DATE,
                'dateTo' => 'accepted_at_to',
            ],
        ];
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'q' => Yii::t('app', 'Keyword'),
            'customer_id' => Yii::t('app', 'Customer'),
        ]);
    }

    /**
     * @return float|string
     */
    public function getTotalAmount()
    {
        $query = clone $this->getQuery();

        return $query->sum('real_amount');
    }

    /**
     * @return float
     *
     * @throws InvalidConfigException
     */
    public function getTotalAmountToday()
    {
        $query = clone $this->getQuery();

        $timeStart = strtotime(date('Y-m-d 00:00:00'));
        $timeEnd = strtotime(date('Y-m-d 23:59:59'));

        return floatval($query->dateRange($timeStart, $timeEnd)->sum('real_amount'));
    }

    /**
     * @return float
     *
     * @throws InvalidConfigException
     */
    public function getTotalAmountYesterday()
    {
        $query = clone $this->getQuery();

        $today = strtotime(date('Y-m-d 00:00:00'));
        $yesterday = strtotime('-1 day', $today);
        $yesterdayStart = strtotime(date('Y-m-d 00:00:00', $yesterday));
        $yesterdayEnd = strtotime(date('Y-m-d 23:59:59', $yesterday));

        return floatval($query->dateRange($yesterdayStart, $yesterdayEnd)->sum('real_amount'));
    }

    /**
     * @return float
     */
    public function getTotalAmountGrowthToday()
    {
        $today = $this->totalAmountToday;
        $yesterday = $this->totalAmountYesterday;

        $growth = $today - $yesterday;

        return $yesterday != 0 ? round($growth / $yesterday, 4) : 0;
    }

    /**
     * @return float
     *
     * @throws InvalidConfigException
     */
    public function getTotalAmountThisMonth()
    {
        $query = clone $this->getQuery();

        $timeStart = strtotime(date('Y-m-01 00:00:00'));
        $timeEnd = strtotime(date('Y-m-t 23:59:59'));

        return floatval($query->dateRange($timeStart, $timeEnd)->sum('real_amount'));
    }

    /**
     * @return float
     *
     * @throws InvalidConfigException
     */
    public function getTotalAmountLastMonth()
    {
        $query = clone $this->getQuery();

        $thisMonth = strtotime(date('Y-m-01 00:00:00'));
        $lastMonth = strtotime('-1 month', $thisMonth);
        $lastMonthStart = strtotime(date('Y-m-01 00:00:00', $lastMonth));
        $lastMonthEnd = strtotime(date('Y-m-t 23:59:59', $lastMonth));

        return floatval($query->dateRange($lastMonthStart, $lastMonthEnd)->sum('real_amount'));
    }

    /**
     * @return float
     */
    public function getTotalAmountGrowthThisMonth()
    {
        $thisMonth = $this->totalAmountThisMonth;
        $lastMonth = $this->totalAmountLastMonth;

        $growth = $thisMonth - $lastMonth;

        return $lastMonth != 0 ? round($growth / $lastMonth, 4) : 0;
    }

    /**
     * @return float
     *
     * @throws InvalidConfigException
     */
    public function getTotalAmountThisYear()
    {
        $query = clone $this->getQuery();

        $timeStart = strtotime(date('Y-01-01 00:00:00'));
        $timeEnd = strtotime(date('Y-12-31 23:59:59'));

        return floatval($query->dateRange($timeStart, $timeEnd)->sum('real_amount'));
    }

    /**
     * @return float
     *
     * @throws InvalidConfigException
     */
    public function getTotalAmountLastYear()
    {
        $query = clone $this->getQuery();

        $today = strtotime(date('Y-01-01 00:00:00'));
        $thisYear = strtotime('-1 year', $today);
        $thisYearStart = strtotime(date('Y-01-01 00:00:00', $thisYear));
        $thisYearEnd = strtotime(date('Y-12-31 23:59:59', $thisYear));

        return floatval($query->dateRange($thisYearStart, $thisYearEnd)->sum('real_amount'));
    }

    /**
     * @return float
     */
    public function getTotalAmountGrowthThisYear()
    {
        $thisYear = $this->totalAmountThisYear;
        $lastYear = $this->totalAmountLastYear;

        $growth = $thisYear - $lastYear;

        return $lastYear != 0 ? round($growth / $lastYear, 4) : 0;
    }

    /**
     * @inheritDoc
     */
    public function getDataProvider()
    {
        if (isset($this->_dataProvider)) {
            return $this->_dataProvider;
        }

        $this->_dataProvider = Yii::createObject([
            'class' => ActiveDataProvider::class,
            'query' => $this->getQuery(),
            'sort' => [
                'defaultOrder' => ['at' => SORT_DESC],
            ],
        ]);

        return $this->_dataProvider;
    }

    /**
     * @param InvoicePaymentQuery|ActiveQuery|null $query
     *
     * @return InvoicePaymentQuery|ActiveQuery
     * @throws InvalidConfigException
     */
    public function filterQuery($query = null)
    {
        if (is_null($query)) {
            $query = $this->getQuery();
        }

        $query->andFilterWhere(['invoice_payment.method_id' => $this->method_id])
            ->andFilterWhere(['invoice_payment.status' => $this->status])
            ->andFilterWhere(['invoice_payment.invoice_id' => $this->invoice_id])
            ->andFilterWhere(['invoice_of_payment.customer_id' => $this->customer_id])
            ->andFilterWhere(['>=', 'invoice_payment.accepted_at', $this->accepted_at_from])
            ->andFilterWhere(['<=', 'invoice_payment.accepted_at', $this->accepted_at_to])
            ->andFilterWhere(['>=', 'invoice_payment.at', $this->at_from])
            ->andFilterWhere(['<=', 'invoice_payment.at', $this->at_to]);

        $query->andFilterWhere([
            'OR',
            ['LIKE', 'invoice_payment.number', $this->q],
            ['LIKE', 'invoice_of_payment.number', $this->q],
            ['LIKE', 'customer_of_invoice.company_name', $this->q],
            ['LIKE', 'CONCAT(primary_contact_of_customer.first_name," ",primary_contact_of_customer.last_name)', $this->q],
        ]);

        $this->trigger(self::EVENT_FILTER_QUERY, new SearchableModelEvent([
            'query' => $query,
        ]));

        return $query;
    }

    /**
     * @inheritdoc
     *
     * @return InvoicePaymentQuery|ActiveQuery
     *
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_query = InvoicePayment::find()->joinWith([
            'invoice' => function ($invoiceQuery) {
                /** @var InvoiceQuery $invoiceQuery */

                return $invoiceQuery->joinWith([
                    'customer' => function ($customerQuery) {
                        /** @var CurrencyQuery $customerQuery */

                        return $customerQuery->joinWith('primaryContact');
                    },
                ]);
            },
        ]);

        $this->trigger(self::EVENT_QUERY, new SearchableModelEvent([
            'query' => $this->_query,
        ]));

        return $this->_query;
    }
}