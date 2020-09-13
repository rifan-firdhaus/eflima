<?php namespace modules\account\models\forms\account_notification;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\AccountNotification;
use modules\account\models\AccountNotificationReceiver;
use modules\account\models\queries\AccountNotificationQuery;
use modules\core\db\ActiveQuery;
use modules\core\models\interfaces\SearchableModel;
use modules\core\models\traits\SearchableModelTrait;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property AccountNotificationQuery $query
 * @property ActiveDataProvider       $dataProvider
 */
class AccountNotificationSearch extends AccountNotification implements SearchableModel
{
    use SearchableModelTrait;

    public $q;
    public $account_id;

    /**
     * @inheritDoc
     */
    public function apply($params = [], $formName = null)
    {
        $this->dataProvider->query = $query = $this->getQuery();

        $this->dataProvider->sort->defaultOrder = ['at' => SORT_DESC];

        if ($this->load($params, $formName) && $this->validate()) {

        }

        $this->trigger(self::EVENT_APPLY);

        return $this->dataProvider;
    }

    /**
     * @inheritDoc
     *
     * @return AccountNotificationQuery|ActiveQuery
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_query = $query= AccountNotification::find();

        if(isset($this->account_id)){
            $receiverQuery = AccountNotificationReceiver::find()
                ->select('DISTINCT account_notification_receiver.notification_id')
                ->andWhere(['account_notification_receiver.account_id' => $this->account_id])
                ->andWhere(new Expression("[[account_notification_receiver.notification_id]] = [[account_notification.id]]"));

            $query->andWhere(['account_notification.id' => $receiverQuery]);
        }

        $this->trigger(self::EVENT_QUERY);

        return $this->_query;
    }
}