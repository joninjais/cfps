<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Fwfhealth;

/**
 * FwfhealthSearch represents the model behind the search form of `frontend\models\Fwfhealth`.
 */
class FwfhealthSearch extends Fwfhealth
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fwfhealth_id'], 'integer'],
            [['datev', 'hcode', 'cid', 'prefix', 'fname', 'lname', 'birthday', 'nation', 'sex', 'address', 'company', 'bossname', 'tel', 'hn', 'card_id', 'typehealth_id', 'diesease_id'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Fwfhealth::find();

        Yii::$app->facilityAccess->scopeQuery($query, 'hcode');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'fwfhealth_id' => $this->fwfhealth_id,
        ]);

        $query->andFilterWhere(['like', 'datev', $this->datev])
            ->andFilterWhere(['like', 'hcode', $this->hcode])
            ->andFilterWhere(['like', 'cid', $this->cid])
            ->andFilterWhere(['like', 'prefix', $this->prefix])
            ->andFilterWhere(['like', 'fname', $this->fname])
            ->andFilterWhere(['like', 'lname', $this->lname])
            ->andFilterWhere(['like', 'birthday', $this->birthday])
            ->andFilterWhere(['like', 'nation', $this->nation])
            ->andFilterWhere(['like', 'sex', $this->sex])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'company', $this->company])
            ->andFilterWhere(['like', 'bossname', $this->bossname])
            ->andFilterWhere(['like', 'tel', $this->tel])
            ->andFilterWhere(['like', 'hn', $this->hn])
            ->andFilterWhere(['like', 'card_id', $this->card_id])
            ->andFilterWhere(['like', 'typehealth_id', $this->typehealth_id])
            ->andFilterWhere(['like', 'diesease_id', $this->diesease_id]);

        return $dataProvider;
    }
}
