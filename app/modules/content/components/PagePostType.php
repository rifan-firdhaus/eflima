<?php namespace modules\content\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class PagePostType extends PostType
{
    public $id = 'page';
    public $menu = 'main/content/page';
    public $icon = 'i8:page';

    public function getLabel()
    {
        return Yii::t('app', 'Page');
    }
}