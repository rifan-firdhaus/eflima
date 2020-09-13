<?php namespace modules\account\components\notification;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Yii;
use yii\base\InvalidCallException;
use yii\helpers\ArrayHelper;
use yii\mail\MailerInterface;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class EmailNotificationChannel extends NotificationChannel
{
    /** @var callable */
    public $compose;
    public $rawTo;

    public function send()
    {
        if (is_callable($this->compose)) {
            $message = call_user_func($this->compose, $this);

            if (!$message instanceof MailerInterface) {
                throw new InvalidCallException('compose callable must return MessageInterface');
            }
        } else {
            $message = Yii::$app->mailer->compose();
            $message->setSubject(Yii::t('app', $this->title, $this->titleParams))
                ->setHtmlBody(Yii::t('app', $this->body, $this->bodyParams));

            $emails = ArrayHelper::getColumn($this->toAccounts, 'email');

            $message->setTo($emails);
        }

        return $message->send();
    }
}