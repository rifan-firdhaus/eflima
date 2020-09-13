<?php

use modules\account\web\admin\View;
use modules\core\components\SettingRenderer;
use modules\support\models\forms\knowledge_base_category\KnowledgeBaseCategorySearch;

/**
 * @var View                        $this
 * @var KnowledgeBaseCategorySearch $searchModel
 * @var SettingRenderer             $renderer
 */

$this->subTitle = Yii::t('app', 'Category');

$this->beginContent('@modules/support/views/admin/knowledge-base/components/index-layout.php', [
    'active' => 'category',
]);

echo $this->block('@begin');
echo $this->render('components/data-view', compact('searchModel'));
echo $this->block('@end');

$this->endContent();