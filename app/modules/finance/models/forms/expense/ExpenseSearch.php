<?php namespace modules\finance\models\forms\expense;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\components\SearchableModelEvent;
use modules\core\helpers\Common;
use modules\core\models\interfaces\SearchableModel;
use modules\core\models\traits\SearchableModelTrait;
use modules\core\validators\DateValidator;
use modules\crm\models\queries\CustomerQuery;
use modules\finance\models\Expense;
use modules\finance\models\queries\ExpenseQuery;
use modules\ui\widgets\inputs\Select2Data;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property float              $totalValueToday
 * @property float              $totalValueThisMonth
 * @property float              $totalValue
 * @property mixed              $totalValueGrowthToday
 * @property mixed              $totalValueYesterday
 * @property float              $totalValueGrowthThisMonth
 * @property float              $totalValueLastMonth
 * @property float              $totalValueLastYear
 * @property float              $totalValueThisYear
 * @property float              $totalValueGrowthThisYear
 * @property string|float       $totalAmount
 * @property string|float       $totalTax
 * @property string|float|int   $totalNotBilled
 * @property string|float|int   $totalNonBillable
 * @property string|float|int   $totalBillable
 * @property string|float|int   $totalBilled
 * @property ExpenseQuery       $query
 * @property ActiveDataProvider $dataProvider
 */
class ExpenseSearch extends Expense implements SearchableModel
{
    use SearchableModelTrait;

    public $q;
    public $date_from;
    public $date_to;

    public $is_billable = '';
    public $is_billed = '';

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['q', 'date_to', 'category_id', 'currency_code', 'customer_id'], 'safe'],
            [['is_billable', 'is_billed'], 'boolean'],
            [
                'date_from',
                'daterange',
                'fullDay' => true,
                'type' => DateValidator::TYPE_DATE,
                'dateTo' => 'date_to',
            ],
        ];
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
    public function getTotalValue()
    {
        $query = clone $this->getQuery();

        return floatval($query->sum('real_total'));
    }

    /**
     * @return float
     *
     * @throws InvalidConfigException
     */
    public function getTotalValueToday()
    {
        $query = clone $this->getQuery();

        $timeStart = strtotime(date('Y-m-d 00:00:00'));
        $timeEnd = strtotime(date('Y-m-d 23:59:59'));

        return floatval($query->dateRange($timeStart, $timeEnd)->sum('real_total'));
    }

    /**
     * @return float
     *
     * @throws InvalidConfigException
     */
    public function getTotalValueYesterday()
    {
        $query = clone $this->getQuery();

        $today = strtotime(date('Y-m-d 00:00:00'));
        $yesterday = strtotime('-1 day', $today);
        $yesterdayStart = strtotime(date('Y-m-d 00:00:00', $yesterday));
        $yesterdayEnd = strtotime(date('Y-m-d 23:59:59', $yesterday));

        return floatval($query->dateRange($yesterdayStart, $yesterdayEnd)->sum('real_total'));
    }

    /**
     * @return float
     */
    public function getTotalValueGrowthToday()
    {
        $today = $this->totalValueToday;
        $yesterday = $this->totalValueYesterday;

        $growth = $today - $yesterday;

        return $yesterday != 0 ? round($growth / $yesterday, 4) : 0;
    }

    /**
     * @return float
     *
     * @throws InvalidConfigException
     */
    public function getTotalValueThisMonth()
    {
        $query = clone $this->getQuery();

        $timeStart = strtotime(date('Y-m-01 00:00:00'));
        $timeEnd = strtotime(date('Y-m-t 23:59:59'));

        return floatval($query->dateRange($timeStart, $timeEnd)->sum('real_total'));
    }

    /**
     * @return float
     *
     * @throws InvalidConfigException
     */
    public function getTotalValueLastMonth()
    {
        $query = clone $this->getQuery();

        $thisMonth = strtotime(date('Y-m-01 00:00:00'));
        $lastMonth = strtotime('-1 month', $thisMonth);
        $lastMonthStart = strtotime(date('Y-m-01 00:00:00', $lastMonth));
        $lastMonthEnd = strtotime(date('Y-m-t 23:59:59', $lastMonth));

        return floatval($query->dateRange($lastMonthStart, $lastMonthEnd)->sum('real_total'));
    }

    /**
     * @return float
     */
    public function getTotalValueGrowthThisMonth()
    {
        $thisMonth = $this->totalValueThisMonth;
        $lastMonth = $this->totalValueLastMonth;

        $growth = $thisMonth - $lastMonth;

        return $lastMonth != 0 ? round($growth / $lastMonth, 4) : 0;
    }


    /**
     * @return float
     *
     * @throws InvalidConfigException
     */
    public function getTotalValueThisYear()
    {
        $query = clone $this->getQuery();

        $timeStart = strtotime(date('Y-01-01 00:00:00'));
        $timeEnd = strtotime(date('Y-12-31 23:59:59'));

        return floatval($query->dateRange($timeStart, $timeEnd)->sum('real_total'));
    }

    /**
     * @return float
     *
     * @throws InvalidConfigException
     */
    public function getTotalValueLastYear()
    {
        $query = clone $this->getQuery();

        $today = strtotime(date('Y-01-01 00:00:00'));
        $thisYear = strtotime('-1 year', $today);
        $thisYearStart = strtotime(date('Y-01-01 00:00:00', $thisYear));
        $thisYearEnd = strtotime(date('Y-12-31 23:59:59', $thisYear));

        return floatval($query->dateRange($thisYearStart, $thisYearEnd)->sum('real_total'));
    }

    /**
     * @return float
     */
    public function getTotalValueGrowthThisYear()
    {
        $thisYear = $this->totalValueThisYear;
        $lastYear = $this->totalValueLastYear;

        $growth = $thisYear - $lastYear;

        return $lastYear != 0 ? round($growth / $lastYear, 4) : 0;
    }

    /**
     * @return float|string
     *
     * @throws InvalidConfigException
     */
    public function getTotalAmount()
    {
        $query = clone $this->getQuery();

        return $query->sum('real_amount');
    }

    /**
     * @return float|string
     *
     * @throws InvalidConfigException
     */
    public function getTotalTax()
    {
        $query = clone $this->getQuery();

        return $query->sum('real_tax');
    }

    /**
     * @return string|int|float
     *
     * @throws InvalidConfigException
     */
    public function getTotalBillable()
    {
        $query = clone $this->getQuery();

        return $query->billable()->sum('real_total');
    }

    /**
     * @return string|int|float
     *
     * @throws InvalidConfigException
     */
    public function getTotalNonBillable()
    {
        $query = clone $this->getQuery();

        return $query->nonBillable()->sum('real_total');
    }

    /**
     * @return string|int|float
     *
     * @throws InvalidConfigException
     */
    public function getTotalBilled()
    {
        $query = clone $this->getQuery();

        return $query->billable()->billed()->sum('real_total');
    }

    /**
     * @return string|int|float
     *
     * @throws InvalidConfigException
     */
    public function getTotalNotBilled()
    {
        $query = clone $this->getQuery();

        return $query->billable()->notBilled()->sum('real_total');
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
            'label' => 'name',
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
     * @inheritDoc
     *
     * @param ExpenseQuery|ActiveQuery|null $query
     *
     * @return ExpenseQuery|ActiveQuery
     *
     * @throws InvalidConfigException
     */
    public function filterQuery($query = null)
    {
        if (is_null($query)) {
            $query = $this->getQuery();
        }

        $query->andFilterWhere(['>=', 'expense.date', $this->date_from])
            ->andFilterWhere(['<=', 'expense.date', $this->date_to])
            ->andFilterWhere(['expense.category_id' => $this->category_id])
            ->andFilterWhere(['expense.is_billable' => $this->is_billable])
            ->andFilterWhere(['expense.customer_id' => $this->customer_id])
            ->andFilterWhere(['expense.currency_code' => $this->currency_code]);

        if (!Common::isEmpty($this->is_billed)) {
            if ($this->is_billed) {
                $query->billed();
            } else {
                $query->notBilled();
            }
        }

        $query->andFilterWhere([
            'or',
            ['like', 'expense.name', $this->q],
            ['like', 'expense.reference', $this->q],
            ['like', 'expense.description', $this->q],
            ['like', 'category_of_expense.name', $this->q],
            ['like', 'customer_of_expense.company_name', $this->q],
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
     * @return ExpenseQuery|ActiveQuery
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_query = Expense::find()->joinWith([
            'customer' => function ($customerQuery) {
                /** @var $customerQuery CustomerQuery */

                return $customerQuery->joinWith('primaryContact');
            },
            'category',
        ]);

        if (isset($this->params['customer_id'])) {
            $this->_query->andWhere(['expense.customer_id' => $this->params['customer_id']]);
        }

        $this->trigger(self::EVENT_QUERY, new SearchableModelEvent([
            'query' => $this->_query,
        ]));

        return $this->_query;
    }
}