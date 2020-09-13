<?php namespace modules\account\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

/**
* @author Rifan Firdhaus Widigdo
<rifanfirdhaus@gmail.com>
*
* This is the ActiveQuery class for [[\modules\account\models\AccountCommentAttachment]].
*
* @see \modules\account\models\AccountCommentAttachment
*/
class AccountCommentAttachmentQuery extends \modules\core\db\ActiveQuery
{
/**
* @inheritdoc
* @return \modules\account\models\AccountCommentAttachment[]|array
*/
public function all($db = null)
{
return parent::all($db);
}

/**
* @inheritdoc
* @return \modules\account\models\AccountCommentAttachment|array|null
*/
public function one($db = null)
{
return parent::one($db);
}
}
