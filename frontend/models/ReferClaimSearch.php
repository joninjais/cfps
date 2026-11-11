<?php

namespace frontend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ReferClaimSearch — ตัวกรองสำหรับหน้ารายการ refer_claim
 */
class ReferClaimSearch extends ReferClaim
{
    public function rules()
    {
        return [
            [['hospcode', 'serv_hospcode', 'book_number', 'approved_status', 'dateserv', 'hn', 'cid', 'name', 'refer_number', 'username'], 'safe'],
            [['pay_total'], 'number', 'min' => 0],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = ReferClaim::find();

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 50],
            'sort'       => ['defaultOrder' => ['dateserv' => SORT_DESC, 'rcid' => SORT_DESC]],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['hospcode' => $this->hospcode])
            ->andFilterWhere(['serv_hospcode' => $this->serv_hospcode])
            ->andFilterWhere(['approved_status' => $this->approved_status])
            ->andFilterWhere(['like', 'book_number', $this->book_number])
            ->andFilterWhere(['dateserv' => $this->dateserv])
            ->andFilterWhere(['like', 'hn', $this->hn])
            ->andFilterWhere(['like', 'cid', $this->cid])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'refer_number', $this->refer_number])
            ->andFilterWhere(['pay_total' => $this->pay_total])
            ->andFilterWhere(['like', 'username', $this->username]);

        return $dataProvider;
    }
}
