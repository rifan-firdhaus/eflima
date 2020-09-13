<?php

use modules\ui\widgets\form\fields\RegularField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\Icon;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

if (!isset($configurations)) {
    $configurations = [];
}

$configurations = ArrayHelper::merge([
    'id' => 'note-container-' . rand(0, 100000000),
    'model' => null,
    'model_id' => null,
    'inline' => false,
    'search' => true,
    'jsOptions' => [],
], $configurations);

$configurations['id'] = $configurations['id'] . '-' . $this->uniqueId;

$addUrl = isset($configurations['addUrl']) ? $configurations['addUrl'] : [
    '/note/admin/note/add',
    'model' => $configurations['model'],
    'model_id' => $configurations['model_id'],
];

$url = isset($configurations['url']) ? $configurations['url'] : [
    '/note/admin/note/index',
    'model' => $configurations['model'],
    'model_id' => $configurations['model_id'],
];

if (!isset($configurations['jsOptions']['url'])) {
    $configurations['jsOptions']['url'] = Url::to($url);
}

?>

    <div class="note-container <?= ($configurations['inline'] ? 'note-container-inline' : '') ?>" id="<?= $configurations['id'] ?>" <?= (!$configurations['inline'] ? 'style="display: none"' : ''); ?>>
        <div class="note-toolbar">
            <div class="d-flex w-100 align-items-center">
                <?php if ($configurations['inline']): ?>
                    <h3 class="m-0 font-size-lg"><?= Icon::show('i8:note', ['class' => 'icon icons8-size text-primary mr-2']) . Yii::t('app', 'Notes') ?></h3>
                    <?= Html::a(Icon::show('i8:plus') . Yii::t('app', 'Add Note'), $addUrl, [
                        'class' => 'btn-add-note btn btn-outline-primary ml-auto',
                        'data-lazy' => '0',
                    ]) ?>
                <?php else: ?>
                    <?= Html::a(Icon::show('i8:plus') . Yii::t('app', 'Add Note'), $addUrl, ['class' => 'btn-add-note btn btn-lg btn-primary', 'data-lazy' => '0']) ?>
                <?php endif; ?>
                <?php
                if ($configurations['search']) {
                    $form = Form::begin([
                        'layout' => Form::LAYOUT_VERTICAL,
                        'lazy' => false,
                        'autoRenderActions' => false,
                        'enableClient' => false,
                        'options' => [
                            'class' => 'flex-grow-1 ml-3 note-search-form',
                        ],
                    ]);

                    echo $form->field([
                        'class' => RegularField::class,
                        'inputOnly' => true,
                        'placeholder' => Yii::t('app', 'Search...'),
                        'name' => 'NoteSearch[q]',
                        'inputOptions' => [
                            'class' => 'form-control-lg form-control note-search-query-input',
                        ],
                    ]);

                    Form::end();
                }
                ?>
            </div>
        </div>
        <div class="note-items">

        </div>
        <div class="note-container-overlay"></div>
    </div>

<?php
$jsOptions = Json::encode($configurations['jsOptions']);
$this->registerJs("$('#{$configurations['id']}').noteContainer({$jsOptions})");