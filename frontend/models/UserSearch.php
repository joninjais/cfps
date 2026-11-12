<?php

namespace frontend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class UserSearch extends User
{
    public function rules()
    {
        return [
            [['userid'], 'integer'],
            [['user', 'fname', 'lname', 'orgname', 'hoscode', 'phonenumber', 'email'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = User::find()->where(['is_deleted' => User::NOT_DELETED]);
        $query->andWhere(['hoscode' => \Yii::$app->facilityAccess->userManagementFacilityCodes()]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['userid' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['userid' => $this->userid]);
        $query->andFilterWhere(['like', 'user',        $this->user])
              ->andFilterWhere(['like', 'fname',       $this->fname])
              ->andFilterWhere(['like', 'lname',       $this->lname])
              ->andFilterWhere(['hoscode' => trim((string)$this->hoscode)])
              ->andFilterWhere(['like', 'phonenumber', $this->phonenumber])
              ->andFilterWhere(['like', 'email',       $this->email]);

        return $dataProvider;
    }
}
