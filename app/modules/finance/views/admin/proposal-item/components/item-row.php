<?php

use modules\account\web\admin\View;
use modules\finance\models\ProposalItem;
use modules\ui\widgets\Icon;
use yii\helpers\Html;

/**
 * @var View        $this
 * @var ProposalItem $model
 * @var boolean     $temp
 */

if (!isset($temp)) {
    $temp = in_array($model->scenario, ['admin/temp/add', 'admin/temp/update']);
}
?>

<?php if (in_array($model->type, ['raw', 'product'])): ?>
    <tr data-id="<?= $model->id ?>">
        <td class="align-middle text-center text-nowrap">
            <div class="handle"></div>
            <?php
            if (Yii::$app->user->can('admin.proposal.item.update')) {
                echo Html::a(Icon::show('i8:edit'), $temp ? ['/finance/admin/proposal-item/add'] : ['/finance/admin/proposal-item/update', 'id' => $model->id], [
                    'class' => 'text-primary align-middle update-proposal-item-button m-0 h4',
                    'data-lazy' => 0,
                ]);
            }

            if (Yii::$app->user->can('admin.proposal.item.delete')) {
                echo Html::a(Icon::show('i8:trash'), ['/finance/admin/proposal-item/delete', 'id' => $model->id], [
                    'class' => 'text-danger align-middle delete-proposal-item-button m-0 h4',
                    'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure', [
                        'object_name' => Yii::t('app', 'this item'),
                    ]),
                    'data-lazy-container' => false,
                    'title' => Yii::t('app', 'Delete'),
                    'data-lazy-options' => ['method' => 'DELETE']
                ]);
            }
            ?>
        </td>
        <td class="align-middle">
            <?php
            if (Yii::$app->user->can('admin.proposal.item.update')) {
                echo Html::a(Html::encode($model->name), $temp ? ['/finance/admin/proposal-item/add'] : ['/finance/admin/proposal-item/update', 'id' => $model->id], [
                    'class' => 'update-proposal-item-button',
                    'data-lazy' => 0,
                ]);
            } else {
                echo Html::encode($model->name);
            }
            ?>
        </td>
        <td class="text-right align-middle">
            <?= Yii::$app->formatter->asCurrency($model->price, $model->proposal->currency_code) ?>
        </td>
        <td class="text-right align-middle"><?= Yii::$app->formatter->asDecimal($model->amount) ?></td>
        <td class="text-right align-middle">
            <?php
            $taxes = [];

            foreach ($model->taxes AS $tax) {
                $taxes[] = Html::tag('span', Html::encode($tax->tax->name), [
                    'data-toggle' => 'tooltip',
                    'title' => Yii::$app->formatter->asDecimal($tax->rate) . '%',
                ]);
            };

            echo implode(', ', $taxes);
            ?>
        </td>
        <td class="text-right align-middle"><?= Yii::$app->formatter->asCurrency($model->sub_total, $model->proposal->currency_code) ?></td>
    </tr>
<?php else: ?>

    <?= $this->block('@render', compact('model')) ?>

<?php endif; ?>
