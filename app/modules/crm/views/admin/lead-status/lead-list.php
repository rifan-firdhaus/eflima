<?php

use modules\account\web\admin\View;
use modules\crm\models\forms\lead\LeadSearch;
use modules\crm\models\Lead;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\Html;

/**
 * @var View       $this
 * @var LeadSearch $searchModel
 * @var Lead[]     $models
 */

$models = $searchModel->dataProvider->models;

foreach ($models AS $lead): ?>
    <?php
    Lazy::begin([
        'id' => "lead-status-kanban-item-lead-lazy-{$lead->id}",
        'options' => [
            'data-id' => $lead->id,
            'class' => 'lead-status-kanban-item-lead-container'
        ],
    ])
    ?>
    <div class="lead-status-kanban-item-lead card border mb-2 shadow-sm">
        <div class="lead-status-kanban-item-lead-header card-header d-flex">
            <?= Html::a(Html::encode($lead->name), ['/crm/admin/lead/view', 'id' => $lead->id], [
                'data-lazy-modal' => 'lead-view-modal',
                'data-lazy-container' => '#main-container',
                'class' => 'lead-status-kanban-item-lead-title font-weight-semi-bold',
            ]); ?>
        </div>
        <div class="card-body pt-0">
            <table class="table table-sm table-borderless mb-0">
                <tr>
                    <th class="pl-0"><?= Yii::t('app', 'Source') ?></th>
                    <td><?= Html::tag('span', $lead->source->name, ['style' => ['color' => $lead->source->color_label]]) ?></td>
                </tr>
                <tr>
                    <th class="pl-0"><?= Yii::t('app', 'Created at') ?></th>
                    <td><?= Yii::$app->formatter->asDatetime($lead->created_at) ?></td>
                </tr>
                <tr>
                    <th class="pl-0"><?= Yii::t('app', 'Last follow up') ?></th>
                    <td class="<?= $lead->lastFollowUp ? '' : 'text-warning'; ?>"><?= $lead->lastFollowUp ? Yii::$app->formatter->asDatetime($lead->lastFollowUp->date) : Yii::t('app', 'Never') ?></td>
                </tr>
            </table>
        </div>
    </div>
    <?php Lazy::end(); ?>
<?php endforeach; ?>
