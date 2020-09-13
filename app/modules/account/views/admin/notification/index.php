<?php

use modules\account\models\AccountNotification;
use modules\account\models\forms\account_notification\AccountNotificationSearch;
use modules\account\web\admin\View;
use modules\ui\widgets\Icon;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

/**
 * @var View                      $this
 * @var AccountNotificationSearch $searchModel
 * @var ActiveDataProvider        $dataProvider
 * @var AccountNotification[]     $models
 */


$this->title = Yii::t('app', 'Notification');
$models = $dataProvider->models;

$this->beginContent('@modules/account/views/layouts/admin/components/side-panel-layout.php');
?>

    <div class="account-notifications">
        <?php
        if (empty($models)) {
            $icon = Icon::show('i8:no-reminders');
            $text = Html::tag('div', Yii::t('app', 'No notifications to show'), [
                'class' => 'text',
            ]);

            echo Html::tag('div', $icon . $text, [
                'class' => 'empty',
            ]);
        } else {
            foreach ($models AS $model) {
                echo $this->render('components/notification', compact('model'));
            }
        }
        ?>
    </div>

<?php
$this->endContent();