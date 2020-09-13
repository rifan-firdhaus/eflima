<?php namespace modules\core\web;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Yii;
use yii\web\ErrorHandler as BaseErrorHandler;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ErrorHandler extends BaseErrorHandler
{
    protected function convertExceptionToArray($exception)
    {
        return [
            'success' => false,
            'messages' => ['danger' => [Yii::$app->response->statusText]],
            'status' => [
                'code' => Yii::$app->response->statusCode,
                'text' => Yii::$app->response->statusText,
            ],
            'type' => 'server-error',
            'data' => parent::convertExceptionToArray($exception),
        ];
    }
}