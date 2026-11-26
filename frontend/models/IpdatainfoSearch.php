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
    /**
     * กำหนดกฎการตรวจสอบค่าที่รับเข้ามาจากช่องค้นหาบนหัวตาราง (Filter)
     * - ipid, los (วันนอน) และ p_age (อายุ) ต้องเป็นตัวเลขจำนวนเต็มเท่านั้น
     * - ตัด hospcode ออกจาก safe เพื่อป้องกันการส่งพารามิเตอร์ขอดูรหัสหน่วยบริการอื่นผ่าน URL
     * - เพิ่ม refer_number, refertohospcode, sex, nation ในกลุ่ม safe
     */
    public function rules()
    {
        return [
            [['ipid', 'los', 'p_age'], 'integer'],
            [[
                'admitdate', 'dischartdate', 'name', 'fund', 'cid', 'hn', 'an',
                'icd10', 'icd9', 'cost', 'adjrw', 'costadjrw', 'FundHolderID', 'exsfile',
                'refer_number', 'refertohospcode', 'sex', 'nation',
            ], 'safe'],
        ];
    }

    /**
     * ข้ามการใช้ scenarios() ของโมเดลแม่ (Ipdata) เพื่อให้ช่องค้นหาเว้นว่างได้
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
        // เริ่มต้นสร้างคิวรี่จากตาราง ipdata
        $query = Ipdata::find();

        // ตั้งค่าการแบ่งหน้า (หน้าละ 10 รายการ) และการเรียงลำดับเริ่มต้นตามวันรับรักษาล่าสุด
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10],
            'sort' => [
                'defaultOrder' => [
                    'admitdate' => SORT_DESC,
                    'ipid' => SORT_DESC,
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

        // กรองข้อมูลแบบเทียบค่าตรงตัว (=) สำหรับรหัสอ้างอิง วันที่ วันนอน เพศ และอายุ
        $query->andFilterWhere([
            'ipid' => $this->ipid,
            'admitdate' => $this->admitdate,
            'dischartdate' => $this->dischartdate,
            'los' => $this->los,
            'sex' => $this->sex,
            'p_age' => $this->p_age,
        ]);

        // กรองข้อมูลแบบค้นหาคำบางส่วน (LIKE '%...%') สำหรับข้อมูลผู้ป่วย รหัสโรค ค่ารักษา และข้อมูลส่งต่อ/สัญชาติ
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
