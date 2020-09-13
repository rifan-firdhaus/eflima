<?php

use modules\account\web\admin\View;
use modules\core\components\SettingRenderer;
use modules\support\models\forms\knowledge_base\KnowledgeBaseSearch;

/**
 * @var View                $this
 * @var KnowledgeBaseSearch $searchModel
 * @var SettingRenderer     $renderer
 */

$this->beginContent('@modules/support/views/admin/knowledge-base/components/index-layout.php');
echo $this->block('@begin');
echo $this->render('components/data-view', compact('searchModel'));
echo $this->block('@end');
$this->endContent();