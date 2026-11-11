<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Insurancepolicies;

/**
 * InsurancepoliciesSearch represents the model behind the search form of `frontend\models\Insurancepolicies`.
 */
class InsurancepoliciesSearch extends Insurancepolicies
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['agency_code', 'agency_name', 'province', 'f_code', 'passport_no', 'citizen_id', 'alien_ref_id', 'title_name', 'full_name', 'nationality', 'ethnicity', 'hn_code', 'purchase_date', 'effective_date', 'expiry_date', 'policy_status', 'coverage_period', 'father_passport_no', 'father_citizen_id', 'father_alien_ref_id', 'father_full_name', 'mother_passport_no', 'mother_citizen_id', 'mother_alien_ref_id', 'mother_full_name', 'created_at'], 'safe'],
            [['card_price'], 'number'],
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
        $query = Insurancepolicies::find();

        Yii::$app->facilityAccess->scopeQuery($query, 'agency_code');

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
            'id' => $this->id,
            'purchase_date' => $this->purchase_date,
            'effective_date' => $this->effective_date,
            'expiry_date' => $this->expiry_date,
            'card_price' => $this->card_price,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'agency_code', $this->agency_code])
            ->andFilterWhere(['like', 'agency_name', $this->agency_name])
            ->andFilterWhere(['like', 'province', $this->province])
            ->andFilterWhere(['like', 'f_code', $this->f_code])
            ->andFilterWhere(['like', 'passport_no', $this->passport_no])
            ->andFilterWhere(['like', 'citizen_id', $this->citizen_id])
            ->andFilterWhere(['like', 'alien_ref_id', $this->alien_ref_id])
            ->andFilterWhere(['like', 'title_name', $this->title_name])
            ->andFilterWhere(['like', 'full_name', $this->full_name])
            ->andFilterWhere(['like', 'nationality', $this->nationality])
            ->andFilterWhere(['like', 'ethnicity', $this->ethnicity])
            ->andFilterWhere(['like', 'hn_code', $this->hn_code])
            ->andFilterWhere(['like', 'policy_status', $this->policy_status])
            ->andFilterWhere(['like', 'coverage_period', $this->coverage_period])
            ->andFilterWhere(['like', 'father_passport_no', $this->father_passport_no])
            ->andFilterWhere(['like', 'father_citizen_id', $this->father_citizen_id])
            ->andFilterWhere(['like', 'father_alien_ref_id', $this->father_alien_ref_id])
            ->andFilterWhere(['like', 'father_full_name', $this->father_full_name])
            ->andFilterWhere(['like', 'mother_passport_no', $this->mother_passport_no])
            ->andFilterWhere(['like', 'mother_citizen_id', $this->mother_citizen_id])
            ->andFilterWhere(['like', 'mother_alien_ref_id', $this->mother_alien_ref_id])
            ->andFilterWhere(['like', 'mother_full_name', $this->mother_full_name]);

        return $dataProvider;
    }
}
