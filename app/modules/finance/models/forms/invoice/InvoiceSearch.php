<?php namespace modules\finance\models\forms\invoice;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\components\SearchableModelEvent;
use modules\core\helpers\Common;
use modules\core\models\interfaces\SearchableModel;
use modules\core\models\traits\SearchableModelTrait;
use modules\core\validators\DateValidator;
use modules\crm\models\queries\CustomerQuery;
use modules\finance\models\Invoice;
use modules\finance\models\queries\InvoiceQuery;
use modules\ui\widgets\inputs\Select2Data;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property InvoiceQuery       $query
 *
 * @property string|float       $totalTax
 * @property string|float       $sumOfGrandTotal
 * @property string|float       $sumOfSubTotal
 * @property float              $grandTotalLastMonth
 * @property float              $grandTotalLastYear
 * @property float              $grandTotalToday
 * @property float              $grandTotalThisMonth
 * @property float              $grandTotalYesterday
 * @property float              $grandTotalThisYear
 * @property float              $grandTotalGrowthToday
 * @property float              $grandTotalGrowthThisMonth
 * @property float              $grandTotalGrowthThisYear
 * @property string|float       $sumOfTotalDue
 * @property string|float       $sumOfTotalPaid
 * @property string|float       $sumOfTotalPastDue
 * @property ActiveDataProvider $dataProvider
 */
class InvoiceSearch extends Invoice implements SearchableModel
{
    use SearchableModelTrait;

    public $q;
    public $date_from;
    public $date_to;
    public $due_date_from;
    public $due_date_to;

    public $addUrlParams = [];

    public $is_paid = '';
    public $is_past_due = '';
    public $payment;
    public $has_payment = '';
    public $has_due = '';

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['q', 'date_to', 'currency_code', 'customer_id'], 'safe'],
            [
                'date_from',
                'daterange',
                'fullDay' => true,
                'type' => DateValidator::TYPE_DATE,
                'dateTo' => 'date_to',
            ],
            [
                'due_date_from',
                'daterange',
                'fullDay' => true,
                'type' => DateValidator::TYPE_DATE,
                'dateTo' => 'due_date_to',
            ],
            [
                ['is_paid', 'is_past_due', 'has_due', 'has_payment'],
                'boolean',
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        unset($behaviors['attributeTypecast']);

        return $behaviors;
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'q' => Yii::t('app', 'Keyword'),
        ]);
    }

    /**
     * @return float
     *
     * @throws InvalidConfigException
     */
    public function getGrandTotalToday()
    {
        $query = clone $this->getQuery();

        $timeStart = strtotime(date('Y-m-d 00:00:00'));
        $timeEnd = strtotime(date('Y-m-d 23:59:59'));

        return floatval($query->dateRange($timeStart, $timeEnd)->sum('real_grand_total'));
    }

    /**
     * @return float
     *
     * @throws InvalidConfigException
     */
    public function getGrandTotalYesterday()
    {
        $query = clone $this->getQuery();

        $today = strtotime(date('Y-m-d 00:00:00'));
        $yesterday = strtotime('-1 day', $today);
        $yesterdayStart = strtotime(date('Y-m-d 00:00:00', $yesterday));
        $yesterdayEnd = strtotime(date('Y-m-d 23:59:59', $yesterday));

        return floatval($query->dateRange($yesterdayStart, $yesterdayEnd)->sum('real_grand_total'));
    }

    /**
     * @return float
     */
    public function getGrandTotalGrowthToday()
    {
        $today = $this->grandTotalToday;
        $yesterday = $this->grandTotalYesterday;

        $growth = $today - $yesterday;

        return $yesterday != 0 ? round($growth / $yesterday, 4) : 0;
    }

    /**
     * @return float
     *
     * @throws InvalidConfigException
     */
    public function getGrandTotalThisMonth()
    {
        $query = clone $this->getQuery();

        $timeStart = strtotime(date('Y-m-01 00:00:00'));
        $timeEnd = strtotime(date('Y-m-t 23:59:59'));

        return floatval($query->dateRange($timeStart, $timeEnd)->sum('real_grand_total'));
    }

    /**
     * @return float
     *
     * @throws InvalidConfigException
     */
    public function getGrandTotalLastMonth()
    {
        $query = clone $this->getQuery();

        $thisMonth = strtotime(date('Y-m-01 00:00:00'));
        $lastMonth = strtotime('-1 month', $thisMonth);
        $lastMonthStart = strtotime(date('Y-m-01 00:00:00', $lastMonth));
        $lastMonthEnd = strtotime(date('Y-m-t 23:59:59', $lastMonth));

        return floatval($query->dateRange($lastMonthStart, $lastMonthEnd)->sum('real_grand_total'));
    }

    /**
     * @return float
     */
    public function getGrandTotalGrowthThisMonth()
    {
        $thisMonth = $this->grandTotalThisMonth;
        $lastMonth = $this->grandTotalLastMonth;

        $growth = $thisMonth - $lastMonth;

        return $lastMonth != 0 ? round($growth / $lastMonth, 4) : 0;
    }

    /**
     * @return float
     *
     * @throws InvalidConfigException
     */
    public function getGrandTotalThisYear()
    {
        $query = clone $this->getQuery();

        $timeStart = strtotime(date('Y-01-01 00:00:00'));
        $timeEnd = strtotime(date('Y-12-31 23:59:59'));

        return floatval($query->dateRange($timeStart, $timeEnd)->sum('real_grand_total'));
    }

    /**
     * @return float
     *
     * @throws InvalidConfigException
     */
    public function getGrandTotalLastYear()
    {
        $query = clone $this->getQuery();

        $today = strtotime(date('Y-01-01 00:00:00'));
        $thisYear = strtotime('-1 year', $today);
        $thisYearStart = strtotime(date('Y-01-01 00:00:00', $thisYear));
        $thisYearEnd = strtotime(date('Y-12-31 23:59:59', $thisYear));

        return floatval($query->dateRange($thisYearStart, $thisYearEnd)->sum('real_grand_total'));
    }

    /**
     * @return float
     */
    public function getGrandTotalGrowthThisYear()
    {
        $thisYear = $this->grandTotalThisYear;
        $lastYear = $this->grandTotalLastYear;

        $growth = $thisYear - $lastYear;

        return $lastYear != 0 ? round($growth / $lastYear, 4) : 0;
    }

    /**
     * @return float|string
     * @throws InvalidConfigException
     */
    public function getSumOfGrandTotal()
    {
        $query = clone $this->getQuery();

        return $query->sum('real_grand_total');
    }

    /**
     * @return float|string
     * @throws InvalidConfigException
     */
    public function getSumOfSubTotal()
    {
        $query = clone $this->getQuery();

        return $query->sum('real_sub_total');
    }

    /**
     * @return float|string
     * @throws InvalidConfigException
     */
    public function getSumOfTotalPaid()
    {
        $query = clone $this->getQuery();

        return $query->sum('real_total_paid');
    }

    /**
     * @return float|string
     * @throws InvalidConfigException
     */
    public function getSumOfTotalDue()
    {
        $query = clone $this->getQuery();

        return $query->sum('real_total_due');
    }

    /**
     * @return float|string
     * @throws InvalidConfigException
     */
    public function getSumOfTotalPastDue()
    {
        $query = clone $this->getQuery();

        return $query->pastDue()->sum('real_total_due');
    }

    /**
     * @return float|string
     * @throws InvalidConfigException
     */
    public function getTotalTax()
    {
        $query = clone $this->getQuery();

        return $query->sum('real_tax');
    }

    /**
     * @param string $url
     * @param array  $params
     *
     * @return string
     */
    public function addUrl($url, $params = [])
    {
        $params = ArrayHelper::merge($this->addUrlParams, $params);

        array_unshift($params, $url);

        return Url::to($params);
    }

    /**
     * @param $params
     *
     * @return array
     * @throws InvalidConfigException
     */
    public function autoComplete($params)
    {
        $this->apply($params, '');

        /** @var Select2Data $dataFactory */
        $dataFactory = Yii::createObject([
            'class' => Select2Data::class,
            'dataProvider' => $this->dataProvider,
            'id' => 'id',
            'label' => 'number',
            'attributes' => [
                'customer_name' => 'customer.name',
            ],
        ]);

        return $dataFactory->serialize();
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
                'defaultOrder' => ['created_at' => SORT_DESC],
            ],
        ]);

        return $this->_dataProvider;
    }

    /**
     * @param ActiveQuery|InvoiceQuery|null $query
     *
     * @return ActiveQuery|InvoiceQuery
     * @throws InvalidConfigException
     */
    public function filterQuery($query = null)
    {
        if (is_null($query)) {
            $query = $this->getQuery();
        }

        $query->andFilterWhere(['>=', 'invoice.date', $this->date_from])
            ->andFilterWhere(['<=', 'invoice.date', $this->date_to])
            ->andFilterWhere(['>=', 'invoice.due_date', $this->due_date_from])
            ->andFilterWhere(['<=', 'invoice.due_date', $this->due_date_to])
            ->andFilterWhere(['invoice.currency_code' => $this->currency_code])
            ->andFilterWhere(['invoice.is_paid' => $this->is_paid])
            ->andFilterWhere(['invoice.customer_id' => $this->customer_id])
            ->andFilterWhere(['invoice.is_paid' => $this->is_published]);

        if (!Common::isEmpty($this->is_past_due)) {
            $query->pastDue($this->is_past_due);
        }

        if (!Common::isEmpty($this->has_payment)) {
            $query->hasPayment($this->has_payment);
        }
        if (!Common::isEmpty($this->has_due)) {
            $query->hasPaymentDue($this->has_due);
        }

        $query->andFilterWhere([
            'or',
            ['like', 'invoice.number', $this->q],
            ['like', 'customer_of_invoice.company_name', $this->q],
            ['like', 'CONCAT(primary_contact_of_customer.first_name," ",primary_contact_of_customer.last_name)', $this->q],
        ]);

        $this->trigger(self::EVENT_FILTER_QUERY, new SearchableModelEvent([
            'query' => $query,
        ]));

        return $query;
    }

    /**
     * @inheritdoc
     *
     * @return InvoiceQuery|ActiveQuery
     *
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_query = Invoice::find()->joinWith([
            'customer' => function ($customerQuery) {
                /** @var CustomerQuery $customerQuery */

                return $customerQuery->joinWith('primaryContact');
            },
        ]);

        if (isset($this->params['customer_id'])) {
            $this->_query->andWhere(['invoice.customer_id' => $this->params['customer_id']]);
        }

        $this->trigger(self::EVENT_QUERY, new SearchableModelEvent([
            'query' => $this->_query,
        ]));

        return $this->_query;
    }
}