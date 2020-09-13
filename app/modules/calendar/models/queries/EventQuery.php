<?php namespace modules\calendar\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\calendar\models\Event;
use modules\core\db\ActiveQuery;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\calendar\models\Event]].
 *
 * @see    \modules\calendar\models\Event
 */
class EventQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return Event[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return Event|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function between($start, $end)
    {
        $this->andWhere([
            'OR',
            [
                'AND',
                ['>=', "{$this->getAlias()}.start_date", $start],
                ['<=', "{$this->getAlias()}.end_date", $end],
            ],
            [
                'AND',
                ['<=', "{$this->getAlias()}.start_date", $start],
                ['>=', "{$this->getAlias()}.end_date", $end],
            ],
            [
                'AND',
                ['>=', "{$this->getAlias()}.start_date", $start],
                ['<=', "{$this->getAlias()}.start_date", $end],
            ],
            [
                'AND',
                ['>=', "{$this->getAlias()}.end_date", $start],
                ['<=', "{$this->getAlias()}.end_date", $end],
            ],
        ]);
    }
}
