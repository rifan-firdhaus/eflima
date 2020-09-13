<?php namespace modules\account\components\notification;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\Account;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property array|Account[] $toAccounts
 */
class Notification extends Component
{
    public $title;
    public $body;
    public $titleParams;
    public $bodyParams;
    public $data;
    public $to;
    public $toAccountType;
    public $channels = [];

    protected $_toAccounts;

    /**
     * @throws InvalidConfigException
     */
    public function send()
    {
        $channels = (array) $this->channels;

        foreach ($channels AS $channel => $config) {
            if (is_string($config)) {
                $channel = $config;
                $config = [];
            }

            $channelConfig = ArrayHelper::merge([
                'title' => $this->title,
                'body' => $this->body,
                'titleParams' => $this->titleParams,
                'bodyParams' => $this->bodyParams,
                'data' => $this->data,
                'to' => $this->to,
                'toAccountType' => $this->toAccountType,
            ], $config);

            /** @var NotificationChannel $channel */
            $channel = Yii::createObject($channel, [$this, $channelConfig]);

            if (!$channel instanceof NotificationChannel) {
                throw new InvalidConfigException("Notification channel must instance of " . NotificationChannel::class);
            }

            $channel->send();
        }
    }


    /**
     * @return array|Account[]
     *
     * @throws InvalidConfigException
     */
    public function getToAccounts()
    {
        if (!isset($this->_toAccounts)) {
            $this->_toAccounts = Account::find()->andWhere(['id' => $this->to])->all();
        }

        return $this->_toAccounts;
    }
}