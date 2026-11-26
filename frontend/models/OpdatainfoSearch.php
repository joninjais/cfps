<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Opdata;

/**
 * โมเดลสำหรับจัดการเงื่อนไขการค้นหาและกรองข้อมูลผู้ป่วยนอก (ตาราง opdata)
 * บังคับแสดงเฉพาะข้อมูลของรหัสหน่วยบริการที่ผู้ใช้งานกำลังล็อกอินอยู่เท่านั้น
 */
class OpdatainfoSearch extends Opdata
{
    public function rules()
    {
        return [
            [['opid', 'p_age'], 'integer'],
            [[
                'dateserv', 'name', 'fund', 'cid', 'hn', 'seq', 'icd10',
                'm1', 'm2', 'm3', 'm4', 'm5', 'm6', 'm7', 'm8',
                'm9', 'm10', 'm11', 'm12', 'm13', 'm14', 'm15', 'm16',
                'alltotal', 'FundHolderID', 'exsfile',
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
        $query = Opdata::find();

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
                    'dateserv' => SORT_DESC,
                    'opid' => SORT_DESC,
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
            'opid' => $this->opid,
            'dateserv' => $this->dateserv,
            'sex' => $this->sex,
            'p_age' => $this->p_age,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
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
            ->andFilterWhere(['like', 'FundHolderID', $this->FundHolderID])
            ->andFilterWhere(['like', 'exsfile', $this->exsfile])
            ->andFilterWhere(['like', 'refer_number', $this->refer_number])
            ->andFilterWhere(['like', 'refertohospcode', $this->refertohospcode])
            ->andFilterWhere(['like', 'nation', $this->nation]);

        return $dataProvider;
    }
}
