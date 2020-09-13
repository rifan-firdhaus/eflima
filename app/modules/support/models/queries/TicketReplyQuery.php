<?php namespace modules\support\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

/**
* @author Rifan Firdhaus Widigdo
<rifanfirdhaus@gmail.com>
*
* This is the ActiveQuery class for [[\modules\support\models\TicketReply]].
*
* @see \modules\support\models\TicketReply
*/
class TicketReplyQuery extends \modules\core\db\ActiveQuery
{
/**
* @inheritdoc
* @return \modules\support\models\TicketReply[]|array
*/
public function all($db = null)
{
return parent::all($db);
}

/**
* @inheritdoc
* @return \modules\support\models\TicketReply|array|null
*/
public function one($db = null)
{
return parent::one($db);
}
}
