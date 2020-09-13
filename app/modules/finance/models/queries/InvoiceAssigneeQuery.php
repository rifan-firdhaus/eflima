<?php namespace modules\finance\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

/**
* @author Rifan Firdhaus Widigdo
<rifanfirdhaus@gmail.com>
*
* This is the ActiveQuery class for [[\modules\finance\models\InvoiceAssignee]].
*
* @see \modules\finance\models\InvoiceAssignee
*/
class InvoiceAssigneeQuery extends \modules\core\db\ActiveQuery
{
/**
* @inheritdoc
* @return \modules\finance\models\InvoiceAssignee[]|array
*/
public function all($db = null)
{
return parent::all($db);
}

/**
* @inheritdoc
* @return \modules\finance\models\InvoiceAssignee|array|null
*/
public function one($db = null)
{
return parent::one($db);
}
}
