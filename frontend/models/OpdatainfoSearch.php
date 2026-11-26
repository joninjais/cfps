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
    /**
     * กำหนดกฎการตรวจสอบค่าที่รับเข้ามาจากช่องค้นหาบนหัวตาราง (Filter)
     * - opid และ p_age (อายุ) ต้องเป็นตัวเลขจำนวนเต็มเท่านั้น
     * - ตัด hospcode ออกจาก safe เพื่อป้องกันการส่งพารามิเตอร์ขอดูรหัสหน่วยบริการอื่นผ่าน URL
     * - เพิ่ม refer_number, refertohospcode, sex, nation ในกลุ่ม safe
     */
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

    /**
     * ข้ามการใช้ scenarios() ของโมเดลแม่ (Opdata) เพื่อให้ช่องค้นหาเว้นว่างได้
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * สร้าง ActiveDataProvider โดยล็อกเฉพาะรหัสหน่วยบริการของผู้ใช้งานปัจจุบัน
     *
     * @param array $params พารามิเตอร์คำค้นหาที่ส่งมาจาก URL (Query Params)
     * @return ActiveDataProvider ตัวจัดการข้อมูลสำหรับส่งให้ GridView แสดงผล
     */
    public function search($params)
    {
        // เริ่มต้นสร้างคิวรี่จากตาราง opdata
        $query = Opdata::find();

        // ตั้งค่าการแบ่งหน้า (หน้าละ 10 รายการ) และการเรียงลำดับเริ่มต้นตามวันรับบริการล่าสุด
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10],
            'sort' => [
                'defaultOrder' => [
                    'dateserv' => SORT_DESC,
                    'opid' => SORT_DESC,
                ],
            ],
        ]);

        // ดึงรหัสหน่วยบริการของบัญชีที่ล็อกอินอยู่ (ใช้ ?? ป้องกัน Warning บน PHP 8.3)
        $identity = Yii::$app->user->identity ?? null;
        $ownHoscode = trim((string)($identity->hoscode ?? Yii::$app->session->get('hoscode') ?? ''));

        // หากไม่พบรหัสหน่วยบริการในระบบ ให้บล็อกการดึงข้อมูลทั้งหมดทันทีเพื่อความปลอดภัย
        if ($ownHoscode === '') {
            $query->where('0=1');
            return $dataProvider;
        }

        // บังคับกรองเฉพาะรหัสหน่วยบริการตนเองเสมอ (ทำงานแม้พิมพ์ค่าค้นหาไม่ผ่าน validate)
        $query->andWhere(['hospcode' => $ownHoscode]);

        // โหลดค่าค้นหาจากตาราง และตรึงค่า hospcode ให้แสดงรหัสหน่วยบริการตนเองเท่านั้น
        $this->load($params);
        $this->hospcode = $ownHoscode;

        if (!$this->validate()) {
            return $dataProvider;
        }

        // กรองข้อมูลแบบเทียบค่าตรงตัว (=) สำหรับรหัสอ้างอิง วันที่ เพศ และอายุ
        $query->andFilterWhere([
            'opid' => $this->opid,
            'dateserv' => $this->dateserv,
            'sex' => $this->sex,
            'p_age' => $this->p_age,
        ]);

        // กรองข้อมูลแบบค้นหาคำบางส่วน (LIKE '%...%') สำหรับข้อมูลทั่วไป หมวดค่ารักษา และข้อมูลส่งต่อ/สัญชาติ
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
