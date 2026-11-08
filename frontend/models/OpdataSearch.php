<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Opdata;

/**
 * OpdataSearch represents the model behind the search form of `frontend\models\Opdata`.
 */
class OpdataSearch extends Opdata
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['opid'], 'integer'],
            [['hospcode', 'dateserv', 'name', 'fund', 'cid', 'hn', 'seq', 'icd10', 'm1', 'm2', 'm3', 'm4', 'm5', 'm6', 'm7', 'm8', 'm9', 'm10', 'm11', 'm12', 'm13', 'm14', 'm15', 'm16','alltotal','FundHolderID'], 'safe'],
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
        $query = Opdata::find();

        // add conditions that should always apply here

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
            'opid' => $this->opid,
            'dateserv' => $this->dateserv,
        ]);
        
        $session = Yii::$app->session;
        $hoscode=$session['hoscode'];

        $query->andFilterWhere(['like', 'hospcode', $hoscode])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'fund', $this->fund])
            ->andFilterWhere(['like', 'cid', $this->cid])
            ->andFilterWhere(['like', 'hn', $this->hn])
            ->andFilterWhere(['like', 'seq', $this->seq])
            ->andFilterWhere(['like', 'icd10', $this->icd10])
            ->andFilterWhere(['like', 'm1', $this->m1])
            ->andFilterWhere(['like', 'm2', $this->m2])
            ->andFilterWhere(['like', 'm3', $this->m3])
            ->andFilterWhere(['like', 'm4', $this->m4])
            ->andFilterWhere(['like', 'm5', $this->m5])
            ->andFilterWhere(['like', 'm6', $this->m6])
            ->andFilterWhere(['like', 'm7', $this->m7])
            ->andFilterWhere(['like', 'm8', $this->m8])
            ->andFilterWhere(['like', 'm9', $this->m9])
            ->andFilterWhere(['like', 'm10', $this->m10])
            ->andFilterWhere(['like', 'm11', $this->m11])
            ->andFilterWhere(['like', 'm12', $this->m12])
            ->andFilterWhere(['like', 'm13', $this->m13])
            ->andFilterWhere(['like', 'm14', $this->m14])
            ->andFilterWhere(['like', 'm15', $this->m15])
            ->andFilterWhere(['like', 'm16', $this->m16])
            ->andFilterWhere(['like', 'alltotal', $this->alltotal])
            ->andFilterWhere(['like', 'FundHolderID', $this->FundHolderID]);

        return $dataProvider;
    }
}
