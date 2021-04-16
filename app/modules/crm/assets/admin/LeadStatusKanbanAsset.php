<?php namespace modules\crm\assets\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\assets\admin\MainAsset;
use modules\core\web\AssetBundle;
use modules\ui\assets\SortableJSAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class LeadStatusKanbanAsset extends AssetBundle
{
    public $sourcePath = '@modules/crm/assets/admin/source';

    public $js = [
        'js/lead-status-kanban.js',
    ];

    public $css = [
        'css/lead-status-kanban.css',
    ];

    public $depends = [
        MainAsset::class,
        SortableJSAsset::class
    ];
}
