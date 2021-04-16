<?php

use modules\account\web\admin\View;
use modules\account\widgets\StaffCommentWidget;
use modules\address\models\Country;
use modules\core\components\Setting;
use modules\finance\models\Proposal;
use modules\ui\widgets\Card;
use modules\ui\widgets\Icon;
use modules\ui\widgets\inputs\TinyMceInput;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * @var View     $this
 * @var Proposal $model
 * @var Setting  $setting
 */

$setting = Yii::$app->setting;

if (Yii::$app->user->can('admin.proposal.delete')) {
    $this->toolbar['delete-proposal'] = Html::a([
        'url' => ['/finance/admin/proposal/delete', 'id' => $model->id],
        'class' => 'btn btn-outline-danger btn-icon',
        'icon' => 'i8:trash',
        'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure?', [
            'object_name' => Html::tag('strong', $model->title),
        ]),
        'data-placement' => 'bottom',
        'title' => Yii::t('app', 'Delete'),
        'data-lazy-options' => ['method' => 'DELETE'],
    ]);
}

if (Yii::$app->user->can('admin.proposal.update')) {
    $this->toolbar['update-proposal'] = Html::a([
        'label' => Yii::t('app', 'Update'),
        'url' => ['/finance/admin/proposal/update', 'id' => $model->id],
        'class' => 'btn btn-outline-secondary',
        'data-lazy-modal' => 'proposal-form-modal',
        'data-lazy-container' => '#main-container',
        'icon' => 'i8:edit',
    ]);
}

$this->icon = 'i8:handshake';
$this->fullHeightContent = true;

$address = $model->getRelatedObject()->getAddress($model->getRelatedModel());

$this->beginContent('@modules/finance/views/admin/proposal/components/view-layout.php', [
    'model' => $model,
]);
echo $this->block('@begin');
?>

    <div class="d-flex h-100">
        <?php Lazy::begin([
            'id' => 'proposal-view-wrapper-lazy',
            'options' => [
                'class' => 'h-100 py-3 w-100 overflow-auto',
            ],
        ]); ?>

        <div id="proposal-view-wrapper-<?= $this->uniqueId ?>" data-rid="proposal-view-wrapper" class="h-100">
            <div class="d-flex border-bottom justify-content-between border-right bg-really-light p-3">
                <div class="proposal-view-header">
                    <h1 class="text-uppercase"><?= Yii::t('app', 'Proposal') ?></h1>
                    <div>
                        <span>#<?= Html::encode($model->number) ?></span>
                        <?= Icon::show('i8:record', ['class' => 'text-muted mx-1']) ?>
                        <span><?= Yii::$app->formatter->asDate($model->date) ?></span>
                    </div>
                </div>

                <div class="grand-total">

                </div>

                <div class="proposal-view-bill-to">
                    <h4 class="mb-2"><?= Yii::t('app', 'From:') ?></h4>

                    <div class="font-weight-semi-bold"><?= $setting->get('company/name') ?></div>
                    <div><?= Html::encode($setting->get('company/address')) ?></div>
                    <div><?= Html::encode($setting->get('company/city')) ?>, <?= Html::encode($setting->get('company/province')) ?></div>
                    <div><?= Html::encode(Country::find()->andWhere(['code' => $setting->get('company/country_code')])->select('name')->createCommand()->queryScalar()) ?></div>
                </div>

                <div class="proposal-view-bill-to">
                    <h4 class="mb-2"><?= Yii::t('app', 'To:') ?></h4>

                    <div class="font-weight-semi-bold"><?= $model->getRelatedObject()->getLink($model->getRelatedModel()) ?></div>
                    <div><?= Html::encode($address['address']) ?></div>
                    <div><?= Html::encode($address['city']) ?>, <?= Html::encode($address['province']) ?></div>
                    <div><?= Html::encode($address['country']) ?></div>
                </div>
            </div>

            <div class="proposal-view-items">
                <?php
                echo $this->render('/admin/proposal-item/components/item-table', [
                    'model' => $model,
                    'hardcoded' => true,
                ]);
                ?>
            </div>

            <div class="proposal-view-content">
                <?php
                Card::begin([
                    'title' => Yii::t('app', 'Content'),
                    'icon' => 'i8:rules',
                ]);
                echo Html::tag('div','',['class' => 'content-tinymce-panel']);
                echo TinyMceInput::widget([
                    'id' => 'proposal-view',
                    'name' => 'proposal_view',
                    'value' => $model->content,
                    'inline' => true,
                    'options' => [
                        'class' => 'form-control',
                    ],

                    'jsOptions' => [
                        'fixed_toolbar_container' => "[data-rid='proposal-view-wrapper'] .content-tinymce-panel",
                    ],
                ]);
                Card::end();
                ?>
            </div>

            <div class="proposal-comment bg-really-light p-3 border-top">
                <h3 class="mb-3 font-size-lg">
                    <?= Icon::show('i8:chat', ['class' => 'text-primary mr-2 icon icons8-size']) . Yii::t('app', 'Discussion') ?>
                </h3>

                <?= StaffCommentWidget::widget([
                    'relatedModel' => 'proposal',
                    'relatedModelId' => $model->id,
                ]) ?>
            </div>
        </div>

        <?php
        $jsOptions = Json::encode([
            'sortUrl' => Url::to(['/finance/admin/proposal-item/sort', 'proposal_id' => $model->id]),
            'saveContentUrl' => Url::to(['/finance/admin/proposal/save-content', 'id' => $model->id]),
        ]);

        $this->registerJs("$('#proposal-view-wrapper-{$this->uniqueId}').proposalView({$jsOptions})");
        ?>

        <?php Lazy::end(); ?>

        <div class="border-left bg-really-light content-sidebar proposal-view-sidebar h-100 overflow-auto">
            <?= $this->render('@modules/note/views/admin/note/components/container', [
                'configurations' => [
                    'id' => 'proposal-note',
                    'model' => 'proposal',
                    'model_id' => $model->id,
                    'inline' => true,
                    'search' => false,
                    'jsOptions' => [
                        'autoLoad' => true,
                    ],
                ],
            ]) ?>
        </div>

    </div>

<?php
echo $this->block('@end');
$this->endContent();
