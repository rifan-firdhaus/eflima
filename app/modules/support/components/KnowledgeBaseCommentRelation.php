<?php namespace modules\support\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\components\CommentRelation;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class KnowledgeBaseCommentRelation extends CommentRelation
{
    use KnowledgeBaseRelatedTrait;
}