<?php namespace modules\account\components\notification;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\AccountNotification;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class DatabaseNotificationChannel extends NotificationChannel
{
    public $url;
    public $is_internal_url;
    public $category;

    public function send()
    {
        $model = new AccountNotification([
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
            'title_params' => $this->titleParams,
            'body_params' => $this->bodyParams,
            'url' => $this->url,
            'is_internal_url' => $this->is_internal_url,
            'category' => $this->category,
            'to' => (array) $this->to,
            'toAccountType' => (array) $this->toAccountType,
        ]);

        if (!$model->save()) {
            return false;
        }

        return true;
    }
}