<?php namespace modules\project\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

/**
* @author Rifan Firdhaus Widigdo
<rifanfirdhaus@gmail.com>
*
* This is the ActiveQuery class for [[\modules\project\models\ProjectMilestone]].
*
* @see \modules\project\models\ProjectMilestone
*/
class ProjectMilestoneQuery extends \modules\core\db\ActiveQuery
{
/**
* @inheritdoc
* @return \modules\project\models\ProjectMilestone[]|array
*/
public function all($db = null)
{
return parent::all($db);
}

/**
* @inheritdoc
* @return \modules\project\models\ProjectMilestone|array|null
*/
public function one($db = null)
{
return parent::one($db);
}
}
