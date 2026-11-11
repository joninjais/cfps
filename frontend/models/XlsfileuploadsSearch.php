<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Xlsfileuploads;
use yii\web\ForbiddenHttpException;

/**
 * XlsfileuploadsSearch represents the model behind the search form of `frontend\models\Xlsfileuploads`.
 */
class XlsfileuploadsSearch extends Xlsfileuploads
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['xlsid'], 'integer'],
            [['exsfile', 'hoscode', 'user','uploaddatetime','typefileupload'], 'safe'],
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
        $query = Xlsfileuploads::find();

        $hoscode = trim((string)(Yii::$app->user->identity->hoscode ?? ''));
        if ($hoscode === '') {
            throw new ForbiddenHttpException('ไม่พบรหัสหน่วยบริการของบัญชี');
        }
        $query->andWhere(['hoscode' => $hoscode]);

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
            'xlsid' => $this->xlsid,
        ]);
        $query->andFilterWhere(['like', 'exsfile', $this->exsfile])
            ->andFilterWhere(['like', 'hoscode', $this->hoscode])
            ->andFilterWhere(['like', 'uploaddatetime', $this->uploaddatetime])
            ->andFilterWhere(['like', 'typefileupload', $this->typefileupload])
            // ->andFilterWhere(['like', 'user', $user])
            ->andFilterWhere(['like', 'user', $this->user])
			->orderBy(['uploaddatetime' => SORT_DESC])
            ;

        return $dataProvider;
    }
}
