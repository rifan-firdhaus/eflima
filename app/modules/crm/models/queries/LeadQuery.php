<?php namespace modules\crm\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\db\ActiveQuery;
use modules\crm\models\Lead;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\crm\models\Lead]].
 *
 * @see    Lead
 */
class LeadQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return Lead[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return Lead|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
