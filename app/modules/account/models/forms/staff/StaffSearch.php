<?php namespace modules\account\models\forms\staff;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\AccountContact;
use modules\account\models\Staff;
use modules\account\models\StaffAccount;
use modules\core\components\SearchableModelEvent;
use modules\core\db\ActiveQuery;
use modules\core\helpers\Common;
use modules\core\models\interfaces\SearchableModel;
use modules\core\models\traits\SearchableModelTrait;
use modules\ui\widgets\inputs\Select2Data;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property ActiveQuery        $query
 * @property ActiveDataProvider $dataProvider
 */
class StaffSearch extends Staff implements SearchableModel
{
    use SearchableModelTrait;

    public $q;
    public $is_blocked = '';
    public $created_at_from;
    public $created_at_to;

    public function init()
    {
        parent::init();

        if ($this->dataProvider->sort !== false) {
            $this->setAssociateSort([
                'account' => [
                    'model' => StaffAccount::instance(),
                    'alias' => 'account_of_staff',
                    'except' => [
                        'id',
                        'type',
                        'password',
                        'access_token',
                        'auth_key',
                        'password_reset_token',
                        'password_reset_token_expired_at',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'contact' => [
                    'model' => AccountContact::instance(),
                    'alias' => 'contact_of_account',
                    'except' => [
                        'id',
                    ],
                ],
            ]);
        }

        unset($this->dataProvider->sort->attributes['account_id']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['q'], 'string'],
            [['is_blocked'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'q' => Yii::t('app', 'Search Query'),
            'is_blocked' => Yii::t('app', 'Block'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_query = Staff::find()->joinWith([
            'account' => function ($query) {
                /** @var ActiveQuery $query */

                return $query->joinWith('contact');
            },
        ]);

        $this->trigger(self::EVENT_QUERY);

        return $this->_query;
    }

    /**
     * @param $params
     *
     * @return array
     * @throws InvalidConfigException
     */
    public function autoComplete($params)
    {
        $this->apply($params, '');

        /** @var Select2Data $dataFactory */
        $dataFactory = Yii::createObject([
            'class' => Select2Data::class,
            'dataProvider' => $this->dataProvider,
            'id' => 'id',
            'label' => 'name',
            'attributes' => [
                'email' => 'account.email',
            ],
        ]);

        return $dataFactory->serialize();
    }

    /**
     * @inheritDoc
     */
    public function filterQuery($query = null)
    {

        if (is_null($query)) {
            $query = $this->getQuery();
        }

        $query->andFilterWhere(['account_of_staff.is_blocked' => intval($this->is_blocked)]);

        if (!Common::isEmpty($this->q)) {
            $query->andWhere([
                'OR',
                ['LIKE', 'CONCAT([[first_name]]," ",[[last_name]])', $this->q],
                ['LIKE', 'account_of_staff.email', $this->q],
                ['LIKE', 'account_of_staff.username', $this->q],
            ]);
        }

        $this->trigger(self::EVENT_FILTER_QUERY, new SearchableModelEvent([
            'query' => $query,
        ]));
    }
}
