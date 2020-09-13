<?php namespace modules\ui\widgets\lazy;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Yii;
use yii\web\JsonResponseFormatter;
use yii\web\Response;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class LazyResponse extends JsonResponseFormatter
{
    /**
     * @inheritdoc
     */
    public function format($response)
    {
        $response->data = $this->formatData($response);

        parent::format($response);
    }

    /**
     * @param Response $response
     *
     * @return array
     */
    public function formatData($response)
    {
        if ($response->data instanceof Lazy) {
            $data = [
                'title' => $response->data->view->title,
                'content' => $response->data->content,
            ];
        } else {
            $data = [
                'title' => Yii::$app->view->title,
                'content' => $response->data,
            ];
        }

        $data['messages'] = $response->getStatusCode() !== 302 ? Yii::$app->session->getAllFlashes(true) : [];
        $data['csrf_token'] = Yii::$app->request->getCsrfToken();
        $data['csrf_param'] = Yii::$app->request->csrfParam;

        return $data;
    }
}