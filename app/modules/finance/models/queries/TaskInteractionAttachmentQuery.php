<?php namespace modules\finance\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

/**
* @author Rifan Firdhaus Widigdo
<rifanfirdhaus@gmail.com>
*
* This is the ActiveQuery class for [[\modules\finance\models\TaskInteractionAttachment]].
*
* @see \modules\finance\models\TaskInteractionAttachment
*/
class TaskInteractionAttachmentQuery extends \modules\core\db\ActiveQuery
{
/**
* @inheritdoc
* @return \modules\finance\models\TaskInteractionAttachment[]|array
*/
public function all($db = null)
{
return parent::all($db);
}

/**
* @inheritdoc
* @return \modules\finance\models\TaskInteractionAttachment|array|null
*/
public function one($db = null)
{
return parent::one($db);
}
}
