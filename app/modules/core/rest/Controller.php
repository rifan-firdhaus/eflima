<?php namespace modules\core\rest;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use Yii;
use yii\base\Arrayable;
use yii\base\Model;
use yii\data\DataProviderInterface;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\Controller as BaseRestController;
use yii\rest\Serializer;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Controller extends BaseRestController
{
    /** @var Serializer */
    public $serializer = Serializer::class;
    public $success = true;
    public $result = [
        'success' => true,
        'messages' => [],
        'status' => [
            'code' => null,
            'text' => null,
        ],
        'type' => null,
        'data' => null,
    ];
    protected $messages = [];

    /** @inheritDoc */
    public function init()
    {
        parent::init();

        $this->serializer = Yii::createObject($this->serializer);
    }

    /**
     * @param string $type
     * @param string $message
     *
     * @return $this
     */
    public function addMessage($type, $message)
    {
        $this->messages[$type][] = $message;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator']['authMethods'] = [
            HttpBasicAuth::class,
            HttpBearerAuth::class,
            [
                'class' => QueryParamAuth::class,
                'tokenParam' => 'access_token',
            ],
        ];

        return $behaviors;
    }

    /**
     * @return $this
     */
    public function failed()
    {
        return $this->success(false);
    }

    /**
     * @param bool $success
     *
     * @return $this
     */
    public function success($success = true)
    {
        $this->success = $success;

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function serializeData($data)
    {
        $this->result['data'] = $this->serializer->serialize($data);

        if ($data instanceof Model && $data->hasErrors()) {
            $this->result['type'] = 'model-errors';
            $this->success = false;
        } elseif ($data instanceof Arrayable) {
            $this->result['type'] = 'model';
        } elseif ($data instanceof DataProviderInterface) {
            $this->result['type'] = 'model-list';

            if (($pagination = $data->getPagination())) {
                $this->result['pagination'] = [
                    'total_count' => $pagination->totalCount,
                    'page_count' => $pagination->getPageCount(),
                    'current_page' => $pagination->getPage() + 1,
                    'page_size' => $pagination->pageSize,
                    'links' => $pagination->getLinks(true),
                ];
            }
        } else {
            $this->result['type'] = 'raw';
            $this->result['data'] = $data;
        }

        $this->result['status']['code'] = Yii::$app->response->statusCode;
        $this->result['status']['text'] = Yii::$app->response->statusText;
        $this->result['success'] = $this->success;
        $this->result['messages'] = $this->messages;

        return $this->result;
    }
}