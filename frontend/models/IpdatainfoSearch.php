<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Ipdata;

/**
 * โมเดลสำหรับจัดการเงื่อนไขการค้นหาและกรองข้อมูลผู้ป่วยใน (ตาราง ipdata)
 * บังคับแสดงเฉพาะข้อมูลของรหัสหน่วยบริการที่ผู้ใช้งานกำลังล็อกอินอยู่เท่านั้น
 */
class IpdatainfoSearch extends Ipdata
{
    public function rules()
    {
        return [
            [['ipid', 'los', 'p_age'], 'integer'],
            [[
                'admitdate', 'dischartdate', 'name', 'fund', 'cid', 'hn', 'an',
                'icd10', 'icd9', 'cost', 'adjrw', 'costadjrw',
                'FundHolderID', 'exsfile',
                'refer_number', 'refertohospcode', 'sex', 'nation',
            ], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Ipdata::find();

        // ตรวจสอบโหมดแสดงทั้งหมด (per-page=all) หรือเลือกจำนวนรายการต่อหน้า
        $perPageParam = trim((string)($params['per-page'] ?? ''));
        $paginationConfig = ($perPageParam === 'all')
            ? false
            : [
                'defaultPageSize' => 10,
                'pageSizeLimit' => [1, 5000],
            ];

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => $paginationConfig,
            'sort' => [
                'defaultOrder' => [
                    'admitdate' => SORT_DESC,
                    'ipid' => SORT_DESC,
                ],
            ],
        ]);

        $identity = Yii::$app->user->identity ?? null;
        $ownHoscode = trim((string)($identity->hoscode ?? Yii::$app->session->get('hoscode') ?? ''));

        if ($ownHoscode === '') {
            $query->where('0=1');
            return $dataProvider;
        }

        // บังคับกรองเฉพาะรหัสหน่วยบริการตนเองเสมอ
        $query->andWhere(['hospcode' => $ownHoscode]);

        $this->load($params);
        $this->hospcode = $ownHoscode;

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'ipid' => $this->ipid,
            'admitdate' => $this->admitdate,
            'dischartdate' => $this->dischartdate,
            'los' => $this->los,
            'sex' => $this->sex,
            'p_age' => $this->p_age,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'fund', $this->fund])
            ->andFilterWhere(['like', 'cid', $this->cid])
            ->andFilterWhere(['like', 'hn', $this->hn])
            ->andFilterWhere(['like', 'an', $this->an])
            ->andFilterWhere(['like', 'icd10', $this->icd10])
            ->andFilterWhere(['like', 'icd9', $this->icd9])
            ->andFilterWhere(['like', 'cost', $this->cost])
            ->andFilterWhere(['like', 'adjrw', $this->adjrw])
            ->andFilterWhere(['like', 'costadjrw', $this->costadjrw])
            ->andFilterWhere(['like', 'FundHolderID', $this->FundHolderID])
            ->andFilterWhere(['like', 'exsfile', $this->exsfile])
            ->andFilterWhere(['like', 'refer_number', $this->refer_number])
            ->andFilterWhere(['like', 'refertohospcode', $this->refertohospcode])
            ->andFilterWhere(['like', 'nation', $this->nation]);

        return $dataProvider;
    }
}
