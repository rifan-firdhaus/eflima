<?php namespace modules\support\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\support\models\KnowledgeBase;
use modules\support\models\queries\KnowledgeBaseQuery;
use modules\support\models\queries\TicketPredefinedReplyQuery;
use modules\support\models\TicketPredefinedReply;
use modules\ui\widgets\inputs\Select2Input;
use yii\base\InvalidConfigException;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property KnowledgeBaseQuery $query
 */
class KnowledgeBaseInput extends Select2Input
{
    public $_query;
    public $idAttribute = 'id';
    public $labelAttribute = 'title';
    public $jsOptions = [
        'width' => '100%',
    ];

    /**
     * @param KnowledgeBaseQuery $query
     */
    public function setQuery($query)
    {
        $this->_query = $query;
    }

    /**
     * @return KnowledgeBaseQuery
     *
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if (!isset($this->_query)) {
            $this->_query = KnowledgeBase::find();
        }

        return $this->_query;
    }

    /**
     * @inheritdoc
     */
    public function normalize()
    {
        $this->source = $this->query->select(['id', 'title'])->map($this->idAttribute, $this->labelAttribute);

        parent::normalize();
    }
}
