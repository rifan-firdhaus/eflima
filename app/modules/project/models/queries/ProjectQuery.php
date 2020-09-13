<?php namespace modules\project\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\db\ActiveQuery;
use modules\finance\models\queries\ProductQuery;
use modules\project\models\Project;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\project\models\Project]].
 *
 * @see    Project
 */
class ProjectQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return Project[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return Project|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param bool $hasInvoice
     *
     * @return $this
     */
    public function hasInvoice($hasInvoice = true)
    {

    }
}
