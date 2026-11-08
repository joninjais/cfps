<?php

use yii\db\Migration;

/**
 * สร้างตาราง sql_code สำหรับเก็บคำสั่ง SQL แต่ละ HIS
 */
class m260316_000001_create_sql_code_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%sql_code}}', [
            'id'          => $this->primaryKey(),
            'his'         => $this->string(100)->notNull()->comment('ชื่อระบบ HIS'),
            'type'        => $this->string(10)->notNull()->comment('ประเภท: OP หรือ IP'),
            'title'       => $this->string(255)->notNull()->comment('ชื่อ/คำอธิบายคำสั่ง SQL'),
            'sql_command' => $this->text()->notNull()->comment('คำสั่ง SQL'),
            'note'        => $this->text()->defaultValue(null)->comment('หมายเหตุ'),
            'created_at'  => $this->integer()->unsigned()->defaultValue(null),
            'updated_at'  => $this->integer()->unsigned()->defaultValue(null),
        ]);

        $this->createIndex('idx_sql_code_his',  '{{%sql_code}}', 'his');
        $this->createIndex('idx_sql_code_type', '{{%sql_code}}', 'type');

        // ข้อมูลตัวอย่างเริ่มต้น
        $now = time();
        $this->insert('{{%sql_code}}', [
            'his'  => 'JHCIS',
            'type' => 'OP',
            'title' => 'JHCIS OP - นำเข้าข้อมูลผู้ป่วยนอก (update ล่าสุด)',
            'sql_command' => "SELECT q3.pcucode,q3.visitdate,q3.name,q3.hos as'กองทุน',q3.idcard,q3.HN,q3.VN,q3.diagcode,q3.room as'ค่าห้อง/ค่าอาหาร',q3.organ as 'ค่าอวัยวะเทียม',q3.drug1 as'ค่ายาในรพ.',q3.drug as 'ค่ายากลับบ้าน'\n,q3.m_supplies as 'ค่าเวชภัณฑ์ไม่ใช่ยา',q3.blood as 'ค่าบริการโลหิต',q3.lab,q3.x as 'x-ray',q3.diag1 as 'ค่าตรวจวินิจฉัยพิเศษ',q3.xx as 'ค่าอุปกรณ์ของใช้' ,q3.proced as 'ค่าหัตถการ'\n,q3.service as 'ค่าบริการทางการพยาบาล',q3.dental as 'ทันตกรรม',q3.aa as'กายภาพบำบัด',q3.bb as 'ฝังเข็ม',q3.cc as 'อื่นๆ'\n,(q3.room+q3.organ+q3.drug1+q3.drug+q3.m_supplies+q3.blood+q3.lab+q3.x+q3.diag1+q3.xx+q3.proced+q3.service+q3.dental+q3.aa+q3.bb+q3.cc)as 'total'\nfrom\n(SELECT v.pcucode,v.visitdate,concat(ctitle.titlename,p.fname,\"  \",p.lname)as'name',\"\" as'hos'\n,case when left(p.idcard,5)= p.pcucodeperson and  left(REPLACE(v.rightno,'-',''),1) is not null then REPLACE(v.rightno,'-','') else\np.idcard end as idcard,v.pid as 'HN',v.visitno as 'VN',d.diagcode\n, 0 as'room',0 as 'organ',0 as 'drug1'\n,case when q2.drug is not null and fp.drug is not null then q2.drug+fp.drug when q2.drug is null  and fp.drug is not null \nthen fp.drug when q2.drug is not  null and fp.drug is  null then q2.drug  else 0 end as 'drug',case when q2.m_supplies is not null then q2.m_supplies else 0 end as 'm_supplies'\n,0 as'blood',case when q2.lab is not null then q2.lab else 0 end as 'lab',0 as 'x',0 as 'diag1',0 as 'xx'\n,case when q2.proced is not null then q2.proced else 0 end as 'proced'\n,case when q2.service is not null then q2.service else 0 end as 'service'\n,case when q2.dental is not null then q2.dental else 0 end as 'dental'\n,0 as'aa',0 as 'bb',30 as 'cc'\nfrom visit v\nINNER JOIN person p on v.pid=p.pid and v.pcucodeperson=p.pcucodeperson\nINNER JOIN ctitle on p.prename=ctitle.titlecode\nINNER JOIN\n((SELECT pcucode,visitno,diagcode from visitdiag WHERE substring(diagcode,1,1) <> 'z' AND dxtype=01\n)\nUNION\n(SELECT pcucode,visitno,diagcode from visitdiag WHERE left(diagcode,3) in ('z48','Z03','Z04','Z08','Z09','Z40'\n,'Z41','Z42','Z43','Z44','Z45','Z46','Z47','Z27','Z24','Z23','Z34','Z30','Z49','Z50','Z51','Z54',  'Z93', 'Z94', 'Z95','Z96','Z97') AND dxtype=01\n)\n) d on v.pcucode=d.pcucode and v.visitno=d.visitno\nINNER JOIN cdisease c on d.diagcode=c.diseasecode\nINNER JOIN chospital on v.pcucode=chospital.hoscode\nINNER JOIN cdistrict on chospital.provcode=cdistrict.provcode and chospital.distcode=cdistrict.distcode\nLEFT JOIN cright on v.rightcode = cright.rightcode\nLEFT JOIN crightgroup on cright.rightgroup=crightgroup.rightgroupcode\nLEFT JOIN (SELECT vd.pcucode,vd.visitno\n,sum(case when cd.drugtype in('01','04','10')then (vd.unit*vd.realprice) else 0 END) as drug\n,sum(case when cd.drugtype ='02'and cd.drugtypesub not in('1','2')then (vd.unit*vd.realprice) else 0 END) as proced\n,sum(case when cd.drugtype ='02'and cd.drugtypesub in('1','2')then (vd.unit*vd.realprice) else 0 END) as dental\n,sum(case when vd.drugcode='897' then vd.realprice ELSE 50 end) as service\n,sum(case when cd.drugtype ='07'then (vd.unit*vd.realprice) else 0 END) as lab\n,sum(case when cd.drugtype ='03'then (vd.unit*vd.realprice) else 0 END) as 'm_supplies'\nfrom visitdrug vd\nINNER JOIN cdrug cd on vd.drugcode=cd.drugcode\nGROUP BY vd.visitno\nORDER BY visitno DESC)as q2 on v.visitno=q2.visitno and v.pcucode=q2.pcucode\nLEFT JOIN (SELECT vepi.pcucode,vepi.visitno,0 as drug,sum(CASE when vepi.visitno is not null then 50 ELSE 0 end)as proced\nfrom visitepi vepi\nGROUP BY vepi.visitno\nUNION\nSELECT vanc.pcucode,vanc.visitno,0 as drug,sum(CASE when vanc.visitno is not null then 200 ELSE 0 end)as proced\nfrom visitanc vanc\nGROUP BY vanc.visitno\nUNION\nSELECT vfp.pcucode,vfp.visitno,sum(CASE when vfp.fpcode is not null then vfp.unit*cdrug.sell ELSE 0 end)as drug\n,sum(CASE when vfp.visitno is not null then 50 ELSE 0 end)as proced\nfrom visitfp vfp\nINNER JOIN cdrug on vfp.fpcode=cdrug.drugcode\nGROUP BY vfp.visitno) fp on v.pcucode=fp.pcucode and v.visitno=fp.visitno\nwhere v.visitdate BETWEEN '20250101' and '20250110'\nAND v.rightcode in('51','8401','8403','8404','8405','8406')\norder by v.visitdate,v.visitno)as q3\nwhere left(q3.idcard,5) != q3.pcucode",
            'note' => 'ใช้กับ JHCIS MySQL',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%sql_code}}');
    }
}
