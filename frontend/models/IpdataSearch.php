<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Ipdata;

/**
 * IpdataSearch represents the model behind the search form of `frontend\models\Ipdata`.
 */
class IpdataSearch extends Ipdata
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ipid', 'los'], 'integer'],
            [['hospcode', 'admitdate', 'dischartdate', 'name', 'fund', 'cid', 'hn', 'an', 'icd10', 'icd9', 'adjrw'], 'safe'],
            [['cost', 'costadjrw'], 'number'],
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
        $query = Ipdata::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            //  'pagination' => FALSE,
            'pagination' => true,
            'pagination' => ['pagesize' => 10],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'ipid' => $this->ipid,
            'admitdate' => $this->admitdate,
            'dischartdate' => $this->dischartdate,
            'cost' => $this->cost,
            'los' => $this->los,
            'costadjrw' => $this->costadjrw,
        ]);

        $session = Yii::$app->session;
        $hoscode=$session['hoscode'];

        $query->andFilterWhere(['like', 'hospcode', $hoscode])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'fund', $this->fund])
            ->andFilterWhere(['like', 'cid', $this->cid])
            ->andFilterWhere(['like', 'hn', $this->hn])
            ->andFilterWhere(['like', 'an', $this->an])
            ->andFilterWhere(['like', 'icd10', $this->icd10])
            ->andFilterWhere(['like', 'icd9', $this->icd9])
            ->andFilterWhere(['like', 'adjrw', $this->adjrw]);

        return $dataProvider;
    }
}
