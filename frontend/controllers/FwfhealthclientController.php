<?php

namespace frontend\controllers;
use yii\data\ArrayDataProvider;
use yii;

class FwfhealthclientController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $session = Yii::$app->session;
        $hoscode=$session['hoscode'];
        
        $data = Yii::$app->request->get();
        
        $peengob = $data['peengob'] ?? '';
        
        if(empty($peengob)){
            $peengob=date('Y');
            $yg=$peengob-1;//ปี่ก่อน
            $yg1=$peengob;//ปีปัจจุบัน
        $sql = "
        SELECT
        '10-ต.ค-$yg' as m
        ,(SELECT hospcode FROM chospital2 WHERE hospcode='$hoscode') as hcode
        ,(SELECT hosname FROM chospital2 WHERE hospcode='$hoscode') as hosname
        ,IFNULL(sum(case when q.price = '365' then 1 else 0 end),'0') as c365
        ,IFNULL(sum(case when q.price = '500' then 1 else 0 end),'0') as c500
        ,IFNULL(sum(case when q.price = '730' then 1 else 0 end),'0') as c730
        ,IFNULL(sum(case when q.price = '900' then 1 else 0 end),'0') as c900
        ,IFNULL(sum(case when q.price = '1600' then 1 else 0 end),'0') as c1600
        ,IFNULL(sum(case when q.price = '2200' then 1 else 0 end),'0') as c2200
        ,IFNULL(sum(case when q.price = '3200' then 1 else 0 end),'0') as c3200
        ,IFNULL(sum(case when q.price = '3000' then 1 else 0 end),'0') as c3000
        ,IFNULL(sum(case when q.price = '365' then 1 else 0 end)
            + sum(case when q.price = '500' then 1 else 0 end) 
            + sum(case when q.price = '730' then 1 else 0 end) 
            + sum(case when q.price = '900' then 1 else 0 end) 
            + sum(case when q.price = '1600' then 1 else 0 end) 
            + sum(case when q.price = '2200' then 1 else 0 end) 
            + sum(case when q.price = '3200' then 1 else 0 end) 
            + sum(case when q.price = '3000' then 1 else 0 end),'0') as total_card
            -- เงินจัดสรร
        ,IFNULL(sum(q.manages_sj),'0') as manages_sj
        ,IFNULL(sum(q.P_and_P),'0') as P_and_P
        ,IFNULL(sum(q.claim),'0') as claim
        ,IFNULL(sum(q.manages_sj) + sum(q.P_and_P) + sum(q.claim),'0') as total_ssj
        
        ,IFNULL(sum(q.manage_sp),'0') as manage_sp
        ,IFNULL(sum(q.HC),'0') as HC
        ,IFNULL(sum(q.manage_sp) + sum(q.HC),'0') as total_sp
        FROM
        (
        SELECT 
         card.price
         -- ,fwfhealth.vn
         ,fwfhealth.hcode
         ,chospital2.hosname
         ,fwfhealth.card_id
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สสจ') as manages_sj
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='P&P' and typeprice.sentprice='สสจ') as P_and_P
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='claim' and typeprice.sentprice='สสจ') as claim
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สป') as manage_sp
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='HC' and typeprice.sentprice='สป') as HC
        FROM fwfhealth
        inner join card on fwfhealth.card_id = card.card_id
        inner join fwfdatabase on fwfhealth.cid = fwfdatabase.CID
                    and fwfhealth.datev = fwfdatabase.regisdate
        left join chospital2 on fwfhealth.hcode = chospital2.hospcode
        
          WHERE fwfhealth.hcode='$hoscode'
            and fwfhealth.datev like '$yg-10%'
            -- and fwfhealth.check_card='pass'
            )as q
            
            UNION
            
            SELECT
        '11-พ.ย-$yg' as m
        ,(SELECT hospcode FROM chospital2 WHERE hospcode='$hoscode') as hcode
        ,(SELECT hosname FROM chospital2 WHERE hospcode='$hoscode') as hosname
        ,IFNULL(sum(case when q.price = '365' then 1 else 0 end),'0') as c365
        ,IFNULL(sum(case when q.price = '500' then 1 else 0 end),'0') as c500
        ,IFNULL(sum(case when q.price = '730' then 1 else 0 end),'0') as c730
        ,IFNULL(sum(case when q.price = '900' then 1 else 0 end),'0') as c900
        ,IFNULL(sum(case when q.price = '1600' then 1 else 0 end),'0') as c1600
        ,IFNULL(sum(case when q.price = '2200' then 1 else 0 end),'0') as c2200
        ,IFNULL(sum(case when q.price = '3200' then 1 else 0 end),'0') as c3200
        ,IFNULL(sum(case when q.price = '3000' then 1 else 0 end),'0') as c3000
        ,IFNULL(sum(case when q.price = '365' then 1 else 0 end)
            + sum(case when q.price = '500' then 1 else 0 end) 
            + sum(case when q.price = '730' then 1 else 0 end) 
            + sum(case when q.price = '900' then 1 else 0 end) 
            + sum(case when q.price = '1600' then 1 else 0 end) 
            + sum(case when q.price = '2200' then 1 else 0 end) 
            + sum(case when q.price = '3200' then 1 else 0 end) 
            + sum(case when q.price = '3000' then 1 else 0 end),'0') as total_card
            -- เงินจัดสรร
        ,IFNULL(sum(q.manages_sj),'0') as manages_sj
        ,IFNULL(sum(q.P_and_P),'0') as P_and_P
        ,IFNULL(sum(q.claim),'0') as claim
        ,IFNULL(sum(q.manages_sj) + sum(q.P_and_P) + sum(q.claim),'0') as total_ssj
        
        ,IFNULL(sum(q.manage_sp),'0') as manage_sp
        ,IFNULL(sum(q.HC),'0') as HC
        ,IFNULL(sum(q.manage_sp) + sum(q.HC),'0') as total_sp
        FROM
        (
        SELECT 
         card.price
         -- ,fwfhealth.vn
         ,fwfhealth.hcode
         ,chospital2.hosname
         ,fwfhealth.card_id
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สสจ') as manages_sj
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='P&P' and typeprice.sentprice='สสจ') as P_and_P
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='claim' and typeprice.sentprice='สสจ') as claim
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สป') as manage_sp
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='HC' and typeprice.sentprice='สป') as HC
        FROM fwfhealth
        inner join card on fwfhealth.card_id = card.card_id
        inner join fwfdatabase on fwfhealth.cid = fwfdatabase.CID
                    and fwfhealth.datev = fwfdatabase.regisdate
        left join chospital2 on fwfhealth.hcode = chospital2.hospcode
        
          WHERE fwfhealth.hcode='$hoscode'
            and fwfhealth.datev like '$yg-11%'
            -- and fwfhealth.check_card='pass'
            )as q
        
        
            UNION
            
            SELECT
        '12-ธ.ค-$yg' as m
        ,(SELECT hospcode FROM chospital2 WHERE hospcode='$hoscode') as hcode
        ,(SELECT hosname FROM chospital2 WHERE hospcode='$hoscode') as hosname
        ,IFNULL(sum(case when q.price = '365' then 1 else 0 end),'0') as c365
        ,IFNULL(sum(case when q.price = '500' then 1 else 0 end),'0') as c500
        ,IFNULL(sum(case when q.price = '730' then 1 else 0 end),'0') as c730
        ,IFNULL(sum(case when q.price = '900' then 1 else 0 end),'0') as c900
        ,IFNULL(sum(case when q.price = '1600' then 1 else 0 end),'0') as c1600
        ,IFNULL(sum(case when q.price = '2200' then 1 else 0 end),'0') as c2200
        ,IFNULL(sum(case when q.price = '3200' then 1 else 0 end),'0') as c3200
        ,IFNULL(sum(case when q.price = '3000' then 1 else 0 end),'0') as c3000
        ,IFNULL(sum(case when q.price = '365' then 1 else 0 end)
            + sum(case when q.price = '500' then 1 else 0 end) 
            + sum(case when q.price = '730' then 1 else 0 end) 
            + sum(case when q.price = '900' then 1 else 0 end) 
            + sum(case when q.price = '1600' then 1 else 0 end) 
            + sum(case when q.price = '2200' then 1 else 0 end) 
            + sum(case when q.price = '3200' then 1 else 0 end) 
            + sum(case when q.price = '3000' then 1 else 0 end),'0') as total_card
            -- เงินจัดสรร
        ,IFNULL(sum(q.manages_sj),'0') as manages_sj
        ,IFNULL(sum(q.P_and_P),'0') as P_and_P
        ,IFNULL(sum(q.claim),'0') as claim
        ,IFNULL(sum(q.manages_sj) + sum(q.P_and_P) + sum(q.claim),'0') as total_ssj
        
        ,IFNULL(sum(q.manage_sp),'0') as manage_sp
        ,IFNULL(sum(q.HC),'0') as HC
        ,IFNULL(sum(q.manage_sp) + sum(q.HC),'0') as total_sp
        FROM
        (
        SELECT 
         card.price
         -- ,fwfhealth.vn
         ,fwfhealth.hcode
         ,chospital2.hosname
         ,fwfhealth.card_id
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สสจ') as manages_sj
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='P&P' and typeprice.sentprice='สสจ') as P_and_P
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='claim' and typeprice.sentprice='สสจ') as claim
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สป') as manage_sp
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='HC' and typeprice.sentprice='สป') as HC
        FROM fwfhealth
        inner join card on fwfhealth.card_id = card.card_id
        inner join fwfdatabase on fwfhealth.cid = fwfdatabase.CID
                    and fwfhealth.datev = fwfdatabase.regisdate
        left join chospital2 on fwfhealth.hcode = chospital2.hospcode
        
          WHERE fwfhealth.hcode='$hoscode'
            and fwfhealth.datev like '$yg-12%'
            -- and fwfhealth.check_card='pass'
            )as q
        
                
            UNION
            
            SELECT
        '1-ม.ค-$yg1' as m
        ,(SELECT hospcode FROM chospital2 WHERE hospcode='$hoscode') as hcode
        ,(SELECT hosname FROM chospital2 WHERE hospcode='$hoscode') as hosname
        ,IFNULL(sum(case when q.price = '365' then 1 else 0 end),'0') as c365
        ,IFNULL(sum(case when q.price = '500' then 1 else 0 end),'0') as c500
        ,IFNULL(sum(case when q.price = '730' then 1 else 0 end),'0') as c730
        ,IFNULL(sum(case when q.price = '900' then 1 else 0 end),'0') as c900
        ,IFNULL(sum(case when q.price = '1600' then 1 else 0 end),'0') as c1600
        ,IFNULL(sum(case when q.price = '2200' then 1 else 0 end),'0') as c2200
        ,IFNULL(sum(case when q.price = '3200' then 1 else 0 end),'0') as c3200
        ,IFNULL(sum(case when q.price = '3000' then 1 else 0 end),'0') as c3000
        ,IFNULL(sum(case when q.price = '365' then 1 else 0 end)
            + sum(case when q.price = '500' then 1 else 0 end) 
            + sum(case when q.price = '730' then 1 else 0 end) 
            + sum(case when q.price = '900' then 1 else 0 end) 
            + sum(case when q.price = '1600' then 1 else 0 end) 
            + sum(case when q.price = '2200' then 1 else 0 end) 
            + sum(case when q.price = '3200' then 1 else 0 end) 
            + sum(case when q.price = '3000' then 1 else 0 end),'0') as total_card
            -- เงินจัดสรร
        ,IFNULL(sum(q.manages_sj),'0') as manages_sj
        ,IFNULL(sum(q.P_and_P),'0') as P_and_P
        ,IFNULL(sum(q.claim),'0') as claim
        ,IFNULL(sum(q.manages_sj) + sum(q.P_and_P) + sum(q.claim),'0') as total_ssj
        
        ,IFNULL(sum(q.manage_sp),'0') as manage_sp
        ,IFNULL(sum(q.HC),'0') as HC
        ,IFNULL(sum(q.manage_sp) + sum(q.HC),'0') as total_sp
        FROM
        (
        SELECT 
         card.price
         -- ,fwfhealth.vn
         ,fwfhealth.hcode
         ,chospital2.hosname
         ,fwfhealth.card_id
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สสจ') as manages_sj
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='P&P' and typeprice.sentprice='สสจ') as P_and_P
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='claim' and typeprice.sentprice='สสจ') as claim
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สป') as manage_sp
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='HC' and typeprice.sentprice='สป') as HC
        FROM fwfhealth
        inner join card on fwfhealth.card_id = card.card_id
        inner join fwfdatabase on fwfhealth.cid = fwfdatabase.CID
                    and fwfhealth.datev = fwfdatabase.regisdate
        left join chospital2 on fwfhealth.hcode = chospital2.hospcode
        
          WHERE fwfhealth.hcode='$hoscode'
            and fwfhealth.datev like '$yg1-01%'
            -- and fwfhealth.check_card='pass'
            )as q
            
            UNION
            
            SELECT
        '2-ก.พ-$yg1' as m
        ,(SELECT hospcode FROM chospital2 WHERE hospcode='$hoscode') as hcode
        ,(SELECT hosname FROM chospital2 WHERE hospcode='$hoscode') as hosname
        ,IFNULL(sum(case when q.price = '365' then 1 else 0 end),'0') as c365
        ,IFNULL(sum(case when q.price = '500' then 1 else 0 end),'0') as c500
        ,IFNULL(sum(case when q.price = '730' then 1 else 0 end),'0') as c730
        ,IFNULL(sum(case when q.price = '900' then 1 else 0 end),'0') as c900
        ,IFNULL(sum(case when q.price = '1600' then 1 else 0 end),'0') as c1600
        ,IFNULL(sum(case when q.price = '2200' then 1 else 0 end),'0') as c2200
        ,IFNULL(sum(case when q.price = '3200' then 1 else 0 end),'0') as c3200
        ,IFNULL(sum(case when q.price = '3000' then 1 else 0 end),'0') as c3000
        ,IFNULL(sum(case when q.price = '365' then 1 else 0 end)
            + sum(case when q.price = '500' then 1 else 0 end) 
            + sum(case when q.price = '730' then 1 else 0 end) 
            + sum(case when q.price = '900' then 1 else 0 end) 
            + sum(case when q.price = '1600' then 1 else 0 end) 
            + sum(case when q.price = '2200' then 1 else 0 end) 
            + sum(case when q.price = '3200' then 1 else 0 end) 
            + sum(case when q.price = '3000' then 1 else 0 end),'0') as total_card
            -- เงินจัดสรร
        ,IFNULL(sum(q.manages_sj),'0') as manages_sj
        ,IFNULL(sum(q.P_and_P),'0') as P_and_P
        ,IFNULL(sum(q.claim),'0') as claim
        ,IFNULL(sum(q.manages_sj) + sum(q.P_and_P) + sum(q.claim),'0') as total_ssj
        
        ,IFNULL(sum(q.manage_sp),'0') as manage_sp
        ,IFNULL(sum(q.HC),'0') as HC
        ,IFNULL(sum(q.manage_sp) + sum(q.HC),'0') as total_sp
        FROM
        (
        SELECT 
         card.price
         -- ,fwfhealth.vn
         ,fwfhealth.hcode
         ,chospital2.hosname
         ,fwfhealth.card_id
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สสจ') as manages_sj
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='P&P' and typeprice.sentprice='สสจ') as P_and_P
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='claim' and typeprice.sentprice='สสจ') as claim
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สป') as manage_sp
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='HC' and typeprice.sentprice='สป') as HC
        FROM fwfhealth
        inner join card on fwfhealth.card_id = card.card_id
        inner join fwfdatabase on fwfhealth.cid = fwfdatabase.CID
                    and fwfhealth.datev = fwfdatabase.regisdate
        left join chospital2 on fwfhealth.hcode = chospital2.hospcode
        
          WHERE fwfhealth.hcode='$hoscode'
            and fwfhealth.datev like '$yg1-02%'
            -- and fwfhealth.check_card='pass'
            )as q
            
            UNION
            
            SELECT
        '3-มี.ค-$yg1' as m
        ,(SELECT hospcode FROM chospital2 WHERE hospcode='$hoscode') as hcode
        ,(SELECT hosname FROM chospital2 WHERE hospcode='$hoscode') as hosname
        ,IFNULL(sum(case when q.price = '365' then 1 else 0 end),'0') as c365
        ,IFNULL(sum(case when q.price = '500' then 1 else 0 end),'0') as c500
        ,IFNULL(sum(case when q.price = '730' then 1 else 0 end),'0') as c730
        ,IFNULL(sum(case when q.price = '900' then 1 else 0 end),'0') as c900
        ,IFNULL(sum(case when q.price = '1600' then 1 else 0 end),'0') as c1600
        ,IFNULL(sum(case when q.price = '2200' then 1 else 0 end),'0') as c2200
        ,IFNULL(sum(case when q.price = '3200' then 1 else 0 end),'0') as c3200
        ,IFNULL(sum(case when q.price = '3000' then 1 else 0 end),'0') as c3000
        ,IFNULL(sum(case when q.price = '365' then 1 else 0 end)
            + sum(case when q.price = '500' then 1 else 0 end) 
            + sum(case when q.price = '730' then 1 else 0 end) 
            + sum(case when q.price = '900' then 1 else 0 end) 
            + sum(case when q.price = '1600' then 1 else 0 end) 
            + sum(case when q.price = '2200' then 1 else 0 end) 
            + sum(case when q.price = '3200' then 1 else 0 end) 
            + sum(case when q.price = '3000' then 1 else 0 end),'0') as total_card
            -- เงินจัดสรร
        ,IFNULL(sum(q.manages_sj),'0') as manages_sj
        ,IFNULL(sum(q.P_and_P),'0') as P_and_P
        ,IFNULL(sum(q.claim),'0') as claim
        ,IFNULL(sum(q.manages_sj) + sum(q.P_and_P) + sum(q.claim),'0') as total_ssj
        
        ,IFNULL(sum(q.manage_sp),'0') as manage_sp
        ,IFNULL(sum(q.HC),'0') as HC
        ,IFNULL(sum(q.manage_sp) + sum(q.HC),'0') as total_sp
        FROM
        (
        SELECT 
         card.price
         -- ,fwfhealth.vn
         ,fwfhealth.hcode
         ,chospital2.hosname
         ,fwfhealth.card_id
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สสจ') as manages_sj
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='P&P' and typeprice.sentprice='สสจ') as P_and_P
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='claim' and typeprice.sentprice='สสจ') as claim
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สป') as manage_sp
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='HC' and typeprice.sentprice='สป') as HC
        FROM fwfhealth
        inner join card on fwfhealth.card_id = card.card_id
        inner join fwfdatabase on fwfhealth.cid = fwfdatabase.CID
                    and fwfhealth.datev = fwfdatabase.regisdate
        left join chospital2 on fwfhealth.hcode = chospital2.hospcode
        
          WHERE fwfhealth.hcode='$hoscode'
            and fwfhealth.datev like '$yg1-03%'
            -- and fwfhealth.check_card='pass'
            )as q
            
            UNION
            
            SELECT
        '4-เม.ย-$yg1' as m
        ,(SELECT hospcode FROM chospital2 WHERE hospcode='$hoscode') as hcode
        ,(SELECT hosname FROM chospital2 WHERE hospcode='$hoscode') as hosname
        ,IFNULL(sum(case when q.price = '365' then 1 else 0 end),'0') as c365
        ,IFNULL(sum(case when q.price = '500' then 1 else 0 end),'0') as c500
        ,IFNULL(sum(case when q.price = '730' then 1 else 0 end),'0') as c730
        ,IFNULL(sum(case when q.price = '900' then 1 else 0 end),'0') as c900
        ,IFNULL(sum(case when q.price = '1600' then 1 else 0 end),'0') as c1600
        ,IFNULL(sum(case when q.price = '2200' then 1 else 0 end),'0') as c2200
        ,IFNULL(sum(case when q.price = '3200' then 1 else 0 end),'0') as c3200
        ,IFNULL(sum(case when q.price = '3000' then 1 else 0 end),'0') as c3000
        ,IFNULL(sum(case when q.price = '365' then 1 else 0 end)
            + sum(case when q.price = '500' then 1 else 0 end) 
            + sum(case when q.price = '730' then 1 else 0 end) 
            + sum(case when q.price = '900' then 1 else 0 end) 
            + sum(case when q.price = '1600' then 1 else 0 end) 
            + sum(case when q.price = '2200' then 1 else 0 end) 
            + sum(case when q.price = '3200' then 1 else 0 end) 
            + sum(case when q.price = '3000' then 1 else 0 end),'0') as total_card
            -- เงินจัดสรร
        ,IFNULL(sum(q.manages_sj),'0') as manages_sj
        ,IFNULL(sum(q.P_and_P),'0') as P_and_P
        ,IFNULL(sum(q.claim),'0') as claim
        ,IFNULL(sum(q.manages_sj) + sum(q.P_and_P) + sum(q.claim),'0') as total_ssj
        
        ,IFNULL(sum(q.manage_sp),'0') as manage_sp
        ,IFNULL(sum(q.HC),'0') as HC
        ,IFNULL(sum(q.manage_sp) + sum(q.HC),'0') as total_sp
        FROM
        (
        SELECT 
         card.price
         -- ,fwfhealth.vn
         ,fwfhealth.hcode
         ,chospital2.hosname
         ,fwfhealth.card_id
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สสจ') as manages_sj
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='P&P' and typeprice.sentprice='สสจ') as P_and_P
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='claim' and typeprice.sentprice='สสจ') as claim
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สป') as manage_sp
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='HC' and typeprice.sentprice='สป') as HC
        FROM fwfhealth
        inner join card on fwfhealth.card_id = card.card_id
        inner join fwfdatabase on fwfhealth.cid = fwfdatabase.CID
                    and fwfhealth.datev = fwfdatabase.regisdate
        left join chospital2 on fwfhealth.hcode = chospital2.hospcode
        
          WHERE fwfhealth.hcode='$hoscode'
            and fwfhealth.datev like '$yg1-04%'
            -- and fwfhealth.check_card='pass'
            )as q
        
            
            UNION
            
            SELECT
        '5-พ.ค-$yg1' as m
        ,(SELECT hospcode FROM chospital2 WHERE hospcode='$hoscode') as hcode
        ,(SELECT hosname FROM chospital2 WHERE hospcode='$hoscode') as hosname
        ,IFNULL(sum(case when q.price = '365' then 1 else 0 end),'0') as c365
        ,IFNULL(sum(case when q.price = '500' then 1 else 0 end),'0') as c500
        ,IFNULL(sum(case when q.price = '730' then 1 else 0 end),'0') as c730
        ,IFNULL(sum(case when q.price = '900' then 1 else 0 end),'0') as c900
        ,IFNULL(sum(case when q.price = '1600' then 1 else 0 end),'0') as c1600
        ,IFNULL(sum(case when q.price = '2200' then 1 else 0 end),'0') as c2200
        ,IFNULL(sum(case when q.price = '3200' then 1 else 0 end),'0') as c3200
        ,IFNULL(sum(case when q.price = '3000' then 1 else 0 end),'0') as c3000
        ,IFNULL(sum(case when q.price = '365' then 1 else 0 end)
            + sum(case when q.price = '500' then 1 else 0 end) 
            + sum(case when q.price = '730' then 1 else 0 end) 
            + sum(case when q.price = '900' then 1 else 0 end) 
            + sum(case when q.price = '1600' then 1 else 0 end) 
            + sum(case when q.price = '2200' then 1 else 0 end) 
            + sum(case when q.price = '3200' then 1 else 0 end) 
            + sum(case when q.price = '3000' then 1 else 0 end),'0') as total_card
            -- เงินจัดสรร
        ,IFNULL(sum(q.manages_sj),'0') as manages_sj
        ,IFNULL(sum(q.P_and_P),'0') as P_and_P
        ,IFNULL(sum(q.claim),'0') as claim
        ,IFNULL(sum(q.manages_sj) + sum(q.P_and_P) + sum(q.claim),'0') as total_ssj
        
        ,IFNULL(sum(q.manage_sp),'0') as manage_sp
        ,IFNULL(sum(q.HC),'0') as HC
        ,IFNULL(sum(q.manage_sp) + sum(q.HC),'0') as total_sp
        FROM
        (
        SELECT 
         card.price
         -- ,fwfhealth.vn
         ,fwfhealth.hcode
         ,chospital2.hosname
         ,fwfhealth.card_id
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สสจ') as manages_sj
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='P&P' and typeprice.sentprice='สสจ') as P_and_P
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='claim' and typeprice.sentprice='สสจ') as claim
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สป') as manage_sp
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='HC' and typeprice.sentprice='สป') as HC
        FROM fwfhealth
        inner join card on fwfhealth.card_id = card.card_id
        inner join fwfdatabase on fwfhealth.cid = fwfdatabase.CID
                    and fwfhealth.datev = fwfdatabase.regisdate
        left join chospital2 on fwfhealth.hcode = chospital2.hospcode
        
          WHERE fwfhealth.hcode='$hoscode'
            and fwfhealth.datev like '$yg1-05%'
            -- and fwfhealth.check_card='pass'
            )as q
            
            UNION
            
            SELECT
        '6-มิ.ย-$yg1' as m
        ,(SELECT hospcode FROM chospital2 WHERE hospcode='$hoscode') as hcode
        ,(SELECT hosname FROM chospital2 WHERE hospcode='$hoscode') as hosname
        ,IFNULL(sum(case when q.price = '365' then 1 else 0 end),'0') as c365
        ,IFNULL(sum(case when q.price = '500' then 1 else 0 end),'0') as c500
        ,IFNULL(sum(case when q.price = '730' then 1 else 0 end),'0') as c730
        ,IFNULL(sum(case when q.price = '900' then 1 else 0 end),'0') as c900
        ,IFNULL(sum(case when q.price = '1600' then 1 else 0 end),'0') as c1600
        ,IFNULL(sum(case when q.price = '2200' then 1 else 0 end),'0') as c2200
        ,IFNULL(sum(case when q.price = '3200' then 1 else 0 end),'0') as c3200
        ,IFNULL(sum(case when q.price = '3000' then 1 else 0 end),'0') as c3000
        ,IFNULL(sum(case when q.price = '365' then 1 else 0 end)
            + sum(case when q.price = '500' then 1 else 0 end) 
            + sum(case when q.price = '730' then 1 else 0 end) 
            + sum(case when q.price = '900' then 1 else 0 end) 
            + sum(case when q.price = '1600' then 1 else 0 end) 
            + sum(case when q.price = '2200' then 1 else 0 end) 
            + sum(case when q.price = '3200' then 1 else 0 end) 
            + sum(case when q.price = '3000' then 1 else 0 end),'0') as total_card
            -- เงินจัดสรร
        ,IFNULL(sum(q.manages_sj),'0') as manages_sj
        ,IFNULL(sum(q.P_and_P),'0') as P_and_P
        ,IFNULL(sum(q.claim),'0') as claim
        ,IFNULL(sum(q.manages_sj) + sum(q.P_and_P) + sum(q.claim),'0') as total_ssj
        
        ,IFNULL(sum(q.manage_sp),'0') as manage_sp
        ,IFNULL(sum(q.HC),'0') as HC
        ,IFNULL(sum(q.manage_sp) + sum(q.HC),'0') as total_sp
        FROM
        (
        SELECT 
         card.price
         -- ,fwfhealth.vn
         ,fwfhealth.hcode
         ,chospital2.hosname
         ,fwfhealth.card_id
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สสจ') as manages_sj
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='P&P' and typeprice.sentprice='สสจ') as P_and_P
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='claim' and typeprice.sentprice='สสจ') as claim
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สป') as manage_sp
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='HC' and typeprice.sentprice='สป') as HC
        FROM fwfhealth
        inner join card on fwfhealth.card_id = card.card_id
        inner join fwfdatabase on fwfhealth.cid = fwfdatabase.CID
                    and fwfhealth.datev = fwfdatabase.regisdate
        left join chospital2 on fwfhealth.hcode = chospital2.hospcode
        
          WHERE fwfhealth.hcode='$hoscode'
            and fwfhealth.datev like '$yg1-06%'
            -- and fwfhealth.check_card='pass'
            )as q
            
            UNION
            
            SELECT
        '7-ก.ค-$yg1' as m
        ,(SELECT hospcode FROM chospital2 WHERE hospcode='$hoscode') as hcode
        ,(SELECT hosname FROM chospital2 WHERE hospcode='$hoscode') as hosname
        ,IFNULL(sum(case when q.price = '365' then 1 else 0 end),'0') as c365
        ,IFNULL(sum(case when q.price = '500' then 1 else 0 end),'0') as c500
        ,IFNULL(sum(case when q.price = '730' then 1 else 0 end),'0') as c730
        ,IFNULL(sum(case when q.price = '900' then 1 else 0 end),'0') as c900
        ,IFNULL(sum(case when q.price = '1600' then 1 else 0 end),'0') as c1600
        ,IFNULL(sum(case when q.price = '2200' then 1 else 0 end),'0') as c2200
        ,IFNULL(sum(case when q.price = '3200' then 1 else 0 end),'0') as c3200
        ,IFNULL(sum(case when q.price = '3000' then 1 else 0 end),'0') as c3000
        ,IFNULL(sum(case when q.price = '365' then 1 else 0 end)
            + sum(case when q.price = '500' then 1 else 0 end) 
            + sum(case when q.price = '730' then 1 else 0 end) 
            + sum(case when q.price = '900' then 1 else 0 end) 
            + sum(case when q.price = '1600' then 1 else 0 end) 
            + sum(case when q.price = '2200' then 1 else 0 end) 
            + sum(case when q.price = '3200' then 1 else 0 end) 
            + sum(case when q.price = '3000' then 1 else 0 end),'0') as total_card
            -- เงินจัดสรร
        ,IFNULL(sum(q.manages_sj),'0') as manages_sj
        ,IFNULL(sum(q.P_and_P),'0') as P_and_P
        ,IFNULL(sum(q.claim),'0') as claim
        ,IFNULL(sum(q.manages_sj) + sum(q.P_and_P) + sum(q.claim),'0') as total_ssj
        
        ,IFNULL(sum(q.manage_sp),'0') as manage_sp
        ,IFNULL(sum(q.HC),'0') as HC
        ,IFNULL(sum(q.manage_sp) + sum(q.HC),'0') as total_sp
        FROM
        (
        SELECT 
         card.price
         -- ,fwfhealth.vn
         ,fwfhealth.hcode
         ,chospital2.hosname
         ,fwfhealth.card_id
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สสจ') as manages_sj
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='P&P' and typeprice.sentprice='สสจ') as P_and_P
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='claim' and typeprice.sentprice='สสจ') as claim
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สป') as manage_sp
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='HC' and typeprice.sentprice='สป') as HC
        FROM fwfhealth
        inner join card on fwfhealth.card_id = card.card_id
        inner join fwfdatabase on fwfhealth.cid = fwfdatabase.CID
                    and fwfhealth.datev = fwfdatabase.regisdate
        left join chospital2 on fwfhealth.hcode = chospital2.hospcode
        
          WHERE fwfhealth.hcode='$hoscode'
            and fwfhealth.datev like '$yg1-07%'
            -- and fwfhealth.check_card='pass'
            )as q
            
            UNION
            
            SELECT
        '8-ส.ค-$yg1' as m
        ,(SELECT hospcode FROM chospital2 WHERE hospcode='$hoscode') as hcode
        ,(SELECT hosname FROM chospital2 WHERE hospcode='$hoscode') as hosname
        ,IFNULL(sum(case when q.price = '365' then 1 else 0 end),'0') as c365
        ,IFNULL(sum(case when q.price = '500' then 1 else 0 end),'0') as c500
        ,IFNULL(sum(case when q.price = '730' then 1 else 0 end),'0') as c730
        ,IFNULL(sum(case when q.price = '900' then 1 else 0 end),'0') as c900
        ,IFNULL(sum(case when q.price = '1600' then 1 else 0 end),'0') as c1600
        ,IFNULL(sum(case when q.price = '2200' then 1 else 0 end),'0') as c2200
        ,IFNULL(sum(case when q.price = '3200' then 1 else 0 end),'0') as c3200
        ,IFNULL(sum(case when q.price = '3000' then 1 else 0 end),'0') as c3000
        ,IFNULL(sum(case when q.price = '365' then 1 else 0 end)
            + sum(case when q.price = '500' then 1 else 0 end) 
            + sum(case when q.price = '730' then 1 else 0 end) 
            + sum(case when q.price = '900' then 1 else 0 end) 
            + sum(case when q.price = '1600' then 1 else 0 end) 
            + sum(case when q.price = '2200' then 1 else 0 end) 
            + sum(case when q.price = '3200' then 1 else 0 end) 
            + sum(case when q.price = '3000' then 1 else 0 end),'0') as total_card
            -- เงินจัดสรร
        ,IFNULL(sum(q.manages_sj),'0') as manages_sj
        ,IFNULL(sum(q.P_and_P),'0') as P_and_P
        ,IFNULL(sum(q.claim),'0') as claim
        ,IFNULL(sum(q.manages_sj) + sum(q.P_and_P) + sum(q.claim),'0') as total_ssj
        
        ,IFNULL(sum(q.manage_sp),'0') as manage_sp
        ,IFNULL(sum(q.HC),'0') as HC
        ,IFNULL(sum(q.manage_sp) + sum(q.HC),'0') as total_sp
        FROM
        (
        SELECT 
         card.price
         -- ,fwfhealth.vn
         ,fwfhealth.hcode
         ,chospital2.hosname
         ,fwfhealth.card_id
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สสจ') as manages_sj
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='P&P' and typeprice.sentprice='สสจ') as P_and_P
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='claim' and typeprice.sentprice='สสจ') as claim
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สป') as manage_sp
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='HC' and typeprice.sentprice='สป') as HC
        FROM fwfhealth
        inner join card on fwfhealth.card_id = card.card_id
        inner join fwfdatabase on fwfhealth.cid = fwfdatabase.CID
                    and fwfhealth.datev = fwfdatabase.regisdate
        left join chospital2 on fwfhealth.hcode = chospital2.hospcode
        
          WHERE fwfhealth.hcode='$hoscode'
            and fwfhealth.datev like '$yg1-08%'
            -- and fwfhealth.check_card='pass'
            )as q
            
            UNION
            
        SELECT
        '9-ก.ย-$yg1' as m
        ,(SELECT hospcode FROM chospital2 WHERE hospcode='$hoscode') as hcode
        ,(SELECT hosname FROM chospital2 WHERE hospcode='$hoscode') as hosname
        ,IFNULL(sum(case when q.price = '365' then 1 else 0 end),'0') as c365
        ,IFNULL(sum(case when q.price = '500' then 1 else 0 end),'0') as c500
        ,IFNULL(sum(case when q.price = '730' then 1 else 0 end),'0') as c730
        ,IFNULL(sum(case when q.price = '900' then 1 else 0 end),'0') as c900
        ,IFNULL(sum(case when q.price = '1600' then 1 else 0 end),'0') as c1600
        ,IFNULL(sum(case when q.price = '2200' then 1 else 0 end),'0') as c2200
        ,IFNULL(sum(case when q.price = '3200' then 1 else 0 end),'0') as c3200
        ,IFNULL(sum(case when q.price = '3000' then 1 else 0 end),'0') as c3000
        ,IFNULL(sum(case when q.price = '365' then 1 else 0 end)
            + sum(case when q.price = '500' then 1 else 0 end) 
            + sum(case when q.price = '730' then 1 else 0 end) 
            + sum(case when q.price = '900' then 1 else 0 end) 
            + sum(case when q.price = '1600' then 1 else 0 end) 
            + sum(case when q.price = '2200' then 1 else 0 end) 
            + sum(case when q.price = '3200' then 1 else 0 end) 
            + sum(case when q.price = '3000' then 1 else 0 end),'0') as total_card
            -- เงินจัดสรร
        ,IFNULL(sum(q.manages_sj),'0') as manages_sj
        ,IFNULL(sum(q.P_and_P),'0') as P_and_P
        ,IFNULL(sum(q.claim),'0') as claim
        ,IFNULL(sum(q.manages_sj) + sum(q.P_and_P) + sum(q.claim),'0') as total_ssj
        
        ,IFNULL(sum(q.manage_sp),'0') as manage_sp
        ,IFNULL(sum(q.HC),'0') as HC
        ,IFNULL(sum(q.manage_sp) + sum(q.HC),'0') as total_sp
        FROM
        (
        SELECT 
         card.price
         -- ,fwfhealth.vn
         ,fwfhealth.hcode
         ,chospital2.hosname
         ,fwfhealth.card_id
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สสจ') as manages_sj
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='P&P' and typeprice.sentprice='สสจ') as P_and_P
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='claim' and typeprice.sentprice='สสจ') as claim
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สป') as manage_sp
         ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='HC' and typeprice.sentprice='สป') as HC
        FROM fwfhealth
        inner join card on fwfhealth.card_id = card.card_id
        inner join fwfdatabase on fwfhealth.cid = fwfdatabase.CID
                    and fwfhealth.datev = fwfdatabase.regisdate
        left join chospital2 on fwfhealth.hcode = chospital2.hospcode
        
          WHERE fwfhealth.hcode='$hoscode'
            and fwfhealth.datev like '$yg1-09%'
            -- and fwfhealth.check_card='pass'
            )as q
        
        
        ";
  
}else{
    $yg=$peengob-1-543;//ปี่ก่อน
    $yg1=$peengob-543;//ปีปัจจุบัน
$sql = "
SELECT
'10-ต.ค-$yg' as m
,(SELECT hospcode FROM chospital2 WHERE hospcode='$hoscode') as hcode
,(SELECT hosname FROM chospital2 WHERE hospcode='$hoscode') as hosname
,IFNULL(sum(case when q.price = '365' then 1 else 0 end),'0') as c365
,IFNULL(sum(case when q.price = '500' then 1 else 0 end),'0') as c500
,IFNULL(sum(case when q.price = '730' then 1 else 0 end),'0') as c730
,IFNULL(sum(case when q.price = '900' then 1 else 0 end),'0') as c900
,IFNULL(sum(case when q.price = '1600' then 1 else 0 end),'0') as c1600
,IFNULL(sum(case when q.price = '2200' then 1 else 0 end),'0') as c2200
,IFNULL(sum(case when q.price = '3200' then 1 else 0 end),'0') as c3200
,IFNULL(sum(case when q.price = '3000' then 1 else 0 end),'0') as c3000
,IFNULL(sum(case when q.price = '365' then 1 else 0 end)
    + sum(case when q.price = '500' then 1 else 0 end) 
    + sum(case when q.price = '730' then 1 else 0 end) 
    + sum(case when q.price = '900' then 1 else 0 end) 
    + sum(case when q.price = '1600' then 1 else 0 end) 
    + sum(case when q.price = '2200' then 1 else 0 end) 
    + sum(case when q.price = '3200' then 1 else 0 end) 
    + sum(case when q.price = '3000' then 1 else 0 end),'0') as total_card
    -- เงินจัดสรร
,IFNULL(sum(q.manages_sj),'0') as manages_sj
,IFNULL(sum(q.P_and_P),'0') as P_and_P
,IFNULL(sum(q.claim),'0') as claim
,IFNULL(sum(q.manages_sj) + sum(q.P_and_P) + sum(q.claim),'0') as total_ssj

,IFNULL(sum(q.manage_sp),'0') as manage_sp
,IFNULL(sum(q.HC),'0') as HC
,IFNULL(sum(q.manage_sp) + sum(q.HC),'0') as total_sp
FROM
(
SELECT 
 card.price
 -- ,fwfhealth.vn
 ,fwfhealth.hcode
 ,chospital2.hosname
 ,fwfhealth.card_id
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สสจ') as manages_sj
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='P&P' and typeprice.sentprice='สสจ') as P_and_P
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='claim' and typeprice.sentprice='สสจ') as claim
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สป') as manage_sp
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='HC' and typeprice.sentprice='สป') as HC
FROM fwfhealth
inner join card on fwfhealth.card_id = card.card_id
inner join fwfdatabase on fwfhealth.cid = fwfdatabase.CID
            and fwfhealth.datev = fwfdatabase.regisdate
left join chospital2 on fwfhealth.hcode = chospital2.hospcode

  WHERE fwfhealth.hcode='$hoscode'
    and fwfhealth.datev like '$yg-10%'
    -- and fwfhealth.check_card='pass'
    )as q
    
    UNION
    
    SELECT
'11-พ.ย-$yg' as m
,(SELECT hospcode FROM chospital2 WHERE hospcode='$hoscode') as hcode
,(SELECT hosname FROM chospital2 WHERE hospcode='$hoscode') as hosname
,IFNULL(sum(case when q.price = '365' then 1 else 0 end),'0') as c365
,IFNULL(sum(case when q.price = '500' then 1 else 0 end),'0') as c500
,IFNULL(sum(case when q.price = '730' then 1 else 0 end),'0') as c730
,IFNULL(sum(case when q.price = '900' then 1 else 0 end),'0') as c900
,IFNULL(sum(case when q.price = '1600' then 1 else 0 end),'0') as c1600
,IFNULL(sum(case when q.price = '2200' then 1 else 0 end),'0') as c2200
,IFNULL(sum(case when q.price = '3200' then 1 else 0 end),'0') as c3200
,IFNULL(sum(case when q.price = '3000' then 1 else 0 end),'0') as c3000
,IFNULL(sum(case when q.price = '365' then 1 else 0 end)
    + sum(case when q.price = '500' then 1 else 0 end) 
    + sum(case when q.price = '730' then 1 else 0 end) 
    + sum(case when q.price = '900' then 1 else 0 end) 
    + sum(case when q.price = '1600' then 1 else 0 end) 
    + sum(case when q.price = '2200' then 1 else 0 end) 
    + sum(case when q.price = '3200' then 1 else 0 end) 
    + sum(case when q.price = '3000' then 1 else 0 end),'0') as total_card
    -- เงินจัดสรร
,IFNULL(sum(q.manages_sj),'0') as manages_sj
,IFNULL(sum(q.P_and_P),'0') as P_and_P
,IFNULL(sum(q.claim),'0') as claim
,IFNULL(sum(q.manages_sj) + sum(q.P_and_P) + sum(q.claim),'0') as total_ssj

,IFNULL(sum(q.manage_sp),'0') as manage_sp
,IFNULL(sum(q.HC),'0') as HC
,IFNULL(sum(q.manage_sp) + sum(q.HC),'0') as total_sp
FROM
(
SELECT 
 card.price
 -- ,fwfhealth.vn
 ,fwfhealth.hcode
 ,chospital2.hosname
 ,fwfhealth.card_id
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สสจ') as manages_sj
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='P&P' and typeprice.sentprice='สสจ') as P_and_P
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='claim' and typeprice.sentprice='สสจ') as claim
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สป') as manage_sp
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='HC' and typeprice.sentprice='สป') as HC
FROM fwfhealth
inner join card on fwfhealth.card_id = card.card_id
inner join fwfdatabase on fwfhealth.cid = fwfdatabase.CID
            and fwfhealth.datev = fwfdatabase.regisdate
left join chospital2 on fwfhealth.hcode = chospital2.hospcode

  WHERE fwfhealth.hcode='$hoscode'
    and fwfhealth.datev like '$yg-11%'
    -- and fwfhealth.check_card='pass'
    )as q


    UNION
    
    SELECT
'12-ธ.ค-$yg' as m
,(SELECT hospcode FROM chospital2 WHERE hospcode='$hoscode') as hcode
,(SELECT hosname FROM chospital2 WHERE hospcode='$hoscode') as hosname
,IFNULL(sum(case when q.price = '365' then 1 else 0 end),'0') as c365
,IFNULL(sum(case when q.price = '500' then 1 else 0 end),'0') as c500
,IFNULL(sum(case when q.price = '730' then 1 else 0 end),'0') as c730
,IFNULL(sum(case when q.price = '900' then 1 else 0 end),'0') as c900
,IFNULL(sum(case when q.price = '1600' then 1 else 0 end),'0') as c1600
,IFNULL(sum(case when q.price = '2200' then 1 else 0 end),'0') as c2200
,IFNULL(sum(case when q.price = '3200' then 1 else 0 end),'0') as c3200
,IFNULL(sum(case when q.price = '3000' then 1 else 0 end),'0') as c3000
,IFNULL(sum(case when q.price = '365' then 1 else 0 end)
    + sum(case when q.price = '500' then 1 else 0 end) 
    + sum(case when q.price = '730' then 1 else 0 end) 
    + sum(case when q.price = '900' then 1 else 0 end) 
    + sum(case when q.price = '1600' then 1 else 0 end) 
    + sum(case when q.price = '2200' then 1 else 0 end) 
    + sum(case when q.price = '3200' then 1 else 0 end) 
    + sum(case when q.price = '3000' then 1 else 0 end),'0') as total_card
    -- เงินจัดสรร
,IFNULL(sum(q.manages_sj),'0') as manages_sj
,IFNULL(sum(q.P_and_P),'0') as P_and_P
,IFNULL(sum(q.claim),'0') as claim
,IFNULL(sum(q.manages_sj) + sum(q.P_and_P) + sum(q.claim),'0') as total_ssj

,IFNULL(sum(q.manage_sp),'0') as manage_sp
,IFNULL(sum(q.HC),'0') as HC
,IFNULL(sum(q.manage_sp) + sum(q.HC),'0') as total_sp
FROM
(
SELECT 
 card.price
 -- ,fwfhealth.vn
 ,fwfhealth.hcode
 ,chospital2.hosname
 ,fwfhealth.card_id
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สสจ') as manages_sj
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='P&P' and typeprice.sentprice='สสจ') as P_and_P
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='claim' and typeprice.sentprice='สสจ') as claim
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สป') as manage_sp
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='HC' and typeprice.sentprice='สป') as HC
FROM fwfhealth
inner join card on fwfhealth.card_id = card.card_id
inner join fwfdatabase on fwfhealth.cid = fwfdatabase.CID
            and fwfhealth.datev = fwfdatabase.regisdate
left join chospital2 on fwfhealth.hcode = chospital2.hospcode

  WHERE fwfhealth.hcode='$hoscode'
    and fwfhealth.datev like '$yg-12%'
    -- and fwfhealth.check_card='pass'
    )as q

        
    UNION
    
    SELECT
'1-ม.ค-$yg1' as m
,(SELECT hospcode FROM chospital2 WHERE hospcode='$hoscode') as hcode
,(SELECT hosname FROM chospital2 WHERE hospcode='$hoscode') as hosname
,IFNULL(sum(case when q.price = '365' then 1 else 0 end),'0') as c365
,IFNULL(sum(case when q.price = '500' then 1 else 0 end),'0') as c500
,IFNULL(sum(case when q.price = '730' then 1 else 0 end),'0') as c730
,IFNULL(sum(case when q.price = '900' then 1 else 0 end),'0') as c900
,IFNULL(sum(case when q.price = '1600' then 1 else 0 end),'0') as c1600
,IFNULL(sum(case when q.price = '2200' then 1 else 0 end),'0') as c2200
,IFNULL(sum(case when q.price = '3200' then 1 else 0 end),'0') as c3200
,IFNULL(sum(case when q.price = '3000' then 1 else 0 end),'0') as c3000
,IFNULL(sum(case when q.price = '365' then 1 else 0 end)
    + sum(case when q.price = '500' then 1 else 0 end) 
    + sum(case when q.price = '730' then 1 else 0 end) 
    + sum(case when q.price = '900' then 1 else 0 end) 
    + sum(case when q.price = '1600' then 1 else 0 end) 
    + sum(case when q.price = '2200' then 1 else 0 end) 
    + sum(case when q.price = '3200' then 1 else 0 end) 
    + sum(case when q.price = '3000' then 1 else 0 end),'0') as total_card
    -- เงินจัดสรร
,IFNULL(sum(q.manages_sj),'0') as manages_sj
,IFNULL(sum(q.P_and_P),'0') as P_and_P
,IFNULL(sum(q.claim),'0') as claim
,IFNULL(sum(q.manages_sj) + sum(q.P_and_P) + sum(q.claim),'0') as total_ssj

,IFNULL(sum(q.manage_sp),'0') as manage_sp
,IFNULL(sum(q.HC),'0') as HC
,IFNULL(sum(q.manage_sp) + sum(q.HC),'0') as total_sp
FROM
(
SELECT 
 card.price
 -- ,fwfhealth.vn
 ,fwfhealth.hcode
 ,chospital2.hosname
 ,fwfhealth.card_id
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สสจ') as manages_sj
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='P&P' and typeprice.sentprice='สสจ') as P_and_P
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='claim' and typeprice.sentprice='สสจ') as claim
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สป') as manage_sp
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='HC' and typeprice.sentprice='สป') as HC
FROM fwfhealth
inner join card on fwfhealth.card_id = card.card_id
inner join fwfdatabase on fwfhealth.cid = fwfdatabase.CID
            and fwfhealth.datev = fwfdatabase.regisdate
left join chospital2 on fwfhealth.hcode = chospital2.hospcode

  WHERE fwfhealth.hcode='$hoscode'
    and fwfhealth.datev like '$yg1-01%'
    -- and fwfhealth.check_card='pass'
    )as q
    
    UNION
    
    SELECT
'2-ก.พ-$yg1' as m
,(SELECT hospcode FROM chospital2 WHERE hospcode='$hoscode') as hcode
,(SELECT hosname FROM chospital2 WHERE hospcode='$hoscode') as hosname
,IFNULL(sum(case when q.price = '365' then 1 else 0 end),'0') as c365
,IFNULL(sum(case when q.price = '500' then 1 else 0 end),'0') as c500
,IFNULL(sum(case when q.price = '730' then 1 else 0 end),'0') as c730
,IFNULL(sum(case when q.price = '900' then 1 else 0 end),'0') as c900
,IFNULL(sum(case when q.price = '1600' then 1 else 0 end),'0') as c1600
,IFNULL(sum(case when q.price = '2200' then 1 else 0 end),'0') as c2200
,IFNULL(sum(case when q.price = '3200' then 1 else 0 end),'0') as c3200
,IFNULL(sum(case when q.price = '3000' then 1 else 0 end),'0') as c3000
,IFNULL(sum(case when q.price = '365' then 1 else 0 end)
    + sum(case when q.price = '500' then 1 else 0 end) 
    + sum(case when q.price = '730' then 1 else 0 end) 
    + sum(case when q.price = '900' then 1 else 0 end) 
    + sum(case when q.price = '1600' then 1 else 0 end) 
    + sum(case when q.price = '2200' then 1 else 0 end) 
    + sum(case when q.price = '3200' then 1 else 0 end) 
    + sum(case when q.price = '3000' then 1 else 0 end),'0') as total_card
    -- เงินจัดสรร
,IFNULL(sum(q.manages_sj),'0') as manages_sj
,IFNULL(sum(q.P_and_P),'0') as P_and_P
,IFNULL(sum(q.claim),'0') as claim
,IFNULL(sum(q.manages_sj) + sum(q.P_and_P) + sum(q.claim),'0') as total_ssj

,IFNULL(sum(q.manage_sp),'0') as manage_sp
,IFNULL(sum(q.HC),'0') as HC
,IFNULL(sum(q.manage_sp) + sum(q.HC),'0') as total_sp
FROM
(
SELECT 
 card.price
 -- ,fwfhealth.vn
 ,fwfhealth.hcode
 ,chospital2.hosname
 ,fwfhealth.card_id
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สสจ') as manages_sj
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='P&P' and typeprice.sentprice='สสจ') as P_and_P
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='claim' and typeprice.sentprice='สสจ') as claim
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สป') as manage_sp
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='HC' and typeprice.sentprice='สป') as HC
FROM fwfhealth
inner join card on fwfhealth.card_id = card.card_id
inner join fwfdatabase on fwfhealth.cid = fwfdatabase.CID
            and fwfhealth.datev = fwfdatabase.regisdate
left join chospital2 on fwfhealth.hcode = chospital2.hospcode

  WHERE fwfhealth.hcode='$hoscode'
    and fwfhealth.datev like '$yg1-02%'
    -- and fwfhealth.check_card='pass'
    )as q
    
    UNION
    
    SELECT
'3-มี.ค-$yg1' as m
,(SELECT hospcode FROM chospital2 WHERE hospcode='$hoscode') as hcode
,(SELECT hosname FROM chospital2 WHERE hospcode='$hoscode') as hosname
,IFNULL(sum(case when q.price = '365' then 1 else 0 end),'0') as c365
,IFNULL(sum(case when q.price = '500' then 1 else 0 end),'0') as c500
,IFNULL(sum(case when q.price = '730' then 1 else 0 end),'0') as c730
,IFNULL(sum(case when q.price = '900' then 1 else 0 end),'0') as c900
,IFNULL(sum(case when q.price = '1600' then 1 else 0 end),'0') as c1600
,IFNULL(sum(case when q.price = '2200' then 1 else 0 end),'0') as c2200
,IFNULL(sum(case when q.price = '3200' then 1 else 0 end),'0') as c3200
,IFNULL(sum(case when q.price = '3000' then 1 else 0 end),'0') as c3000
,IFNULL(sum(case when q.price = '365' then 1 else 0 end)
    + sum(case when q.price = '500' then 1 else 0 end) 
    + sum(case when q.price = '730' then 1 else 0 end) 
    + sum(case when q.price = '900' then 1 else 0 end) 
    + sum(case when q.price = '1600' then 1 else 0 end) 
    + sum(case when q.price = '2200' then 1 else 0 end) 
    + sum(case when q.price = '3200' then 1 else 0 end) 
    + sum(case when q.price = '3000' then 1 else 0 end),'0') as total_card
    -- เงินจัดสรร
,IFNULL(sum(q.manages_sj),'0') as manages_sj
,IFNULL(sum(q.P_and_P),'0') as P_and_P
,IFNULL(sum(q.claim),'0') as claim
,IFNULL(sum(q.manages_sj) + sum(q.P_and_P) + sum(q.claim),'0') as total_ssj

,IFNULL(sum(q.manage_sp),'0') as manage_sp
,IFNULL(sum(q.HC),'0') as HC
,IFNULL(sum(q.manage_sp) + sum(q.HC),'0') as total_sp
FROM
(
SELECT 
 card.price
 -- ,fwfhealth.vn
 ,fwfhealth.hcode
 ,chospital2.hosname
 ,fwfhealth.card_id
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สสจ') as manages_sj
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='P&P' and typeprice.sentprice='สสจ') as P_and_P
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='claim' and typeprice.sentprice='สสจ') as claim
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สป') as manage_sp
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='HC' and typeprice.sentprice='สป') as HC
FROM fwfhealth
inner join card on fwfhealth.card_id = card.card_id
inner join fwfdatabase on fwfhealth.cid = fwfdatabase.CID
            and fwfhealth.datev = fwfdatabase.regisdate
left join chospital2 on fwfhealth.hcode = chospital2.hospcode

  WHERE fwfhealth.hcode='$hoscode'
    and fwfhealth.datev like '$yg1-03%'
    -- and fwfhealth.check_card='pass'
    )as q
    
    UNION
    
    SELECT
'4-เม.ย-$yg1' as m
,(SELECT hospcode FROM chospital2 WHERE hospcode='$hoscode') as hcode
,(SELECT hosname FROM chospital2 WHERE hospcode='$hoscode') as hosname
,IFNULL(sum(case when q.price = '365' then 1 else 0 end),'0') as c365
,IFNULL(sum(case when q.price = '500' then 1 else 0 end),'0') as c500
,IFNULL(sum(case when q.price = '730' then 1 else 0 end),'0') as c730
,IFNULL(sum(case when q.price = '900' then 1 else 0 end),'0') as c900
,IFNULL(sum(case when q.price = '1600' then 1 else 0 end),'0') as c1600
,IFNULL(sum(case when q.price = '2200' then 1 else 0 end),'0') as c2200
,IFNULL(sum(case when q.price = '3200' then 1 else 0 end),'0') as c3200
,IFNULL(sum(case when q.price = '3000' then 1 else 0 end),'0') as c3000
,IFNULL(sum(case when q.price = '365' then 1 else 0 end)
    + sum(case when q.price = '500' then 1 else 0 end) 
    + sum(case when q.price = '730' then 1 else 0 end) 
    + sum(case when q.price = '900' then 1 else 0 end) 
    + sum(case when q.price = '1600' then 1 else 0 end) 
    + sum(case when q.price = '2200' then 1 else 0 end) 
    + sum(case when q.price = '3200' then 1 else 0 end) 
    + sum(case when q.price = '3000' then 1 else 0 end),'0') as total_card
    -- เงินจัดสรร
,IFNULL(sum(q.manages_sj),'0') as manages_sj
,IFNULL(sum(q.P_and_P),'0') as P_and_P
,IFNULL(sum(q.claim),'0') as claim
,IFNULL(sum(q.manages_sj) + sum(q.P_and_P) + sum(q.claim),'0') as total_ssj

,IFNULL(sum(q.manage_sp),'0') as manage_sp
,IFNULL(sum(q.HC),'0') as HC
,IFNULL(sum(q.manage_sp) + sum(q.HC),'0') as total_sp
FROM
(
SELECT 
 card.price
 -- ,fwfhealth.vn
 ,fwfhealth.hcode
 ,chospital2.hosname
 ,fwfhealth.card_id
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สสจ') as manages_sj
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='P&P' and typeprice.sentprice='สสจ') as P_and_P
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='claim' and typeprice.sentprice='สสจ') as claim
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สป') as manage_sp
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='HC' and typeprice.sentprice='สป') as HC
FROM fwfhealth
inner join card on fwfhealth.card_id = card.card_id
inner join fwfdatabase on fwfhealth.cid = fwfdatabase.CID
            and fwfhealth.datev = fwfdatabase.regisdate
left join chospital2 on fwfhealth.hcode = chospital2.hospcode

  WHERE fwfhealth.hcode='$hoscode'
    and fwfhealth.datev like '$yg1-04%'
    -- and fwfhealth.check_card='pass'
    )as q

    
    UNION
    
    SELECT
'5-พ.ค-$yg1' as m
,(SELECT hospcode FROM chospital2 WHERE hospcode='$hoscode') as hcode
,(SELECT hosname FROM chospital2 WHERE hospcode='$hoscode') as hosname
,IFNULL(sum(case when q.price = '365' then 1 else 0 end),'0') as c365
,IFNULL(sum(case when q.price = '500' then 1 else 0 end),'0') as c500
,IFNULL(sum(case when q.price = '730' then 1 else 0 end),'0') as c730
,IFNULL(sum(case when q.price = '900' then 1 else 0 end),'0') as c900
,IFNULL(sum(case when q.price = '1600' then 1 else 0 end),'0') as c1600
,IFNULL(sum(case when q.price = '2200' then 1 else 0 end),'0') as c2200
,IFNULL(sum(case when q.price = '3200' then 1 else 0 end),'0') as c3200
,IFNULL(sum(case when q.price = '3000' then 1 else 0 end),'0') as c3000
,IFNULL(sum(case when q.price = '365' then 1 else 0 end)
    + sum(case when q.price = '500' then 1 else 0 end) 
    + sum(case when q.price = '730' then 1 else 0 end) 
    + sum(case when q.price = '900' then 1 else 0 end) 
    + sum(case when q.price = '1600' then 1 else 0 end) 
    + sum(case when q.price = '2200' then 1 else 0 end) 
    + sum(case when q.price = '3200' then 1 else 0 end) 
    + sum(case when q.price = '3000' then 1 else 0 end),'0') as total_card
    -- เงินจัดสรร
,IFNULL(sum(q.manages_sj),'0') as manages_sj
,IFNULL(sum(q.P_and_P),'0') as P_and_P
,IFNULL(sum(q.claim),'0') as claim
,IFNULL(sum(q.manages_sj) + sum(q.P_and_P) + sum(q.claim),'0') as total_ssj

,IFNULL(sum(q.manage_sp),'0') as manage_sp
,IFNULL(sum(q.HC),'0') as HC
,IFNULL(sum(q.manage_sp) + sum(q.HC),'0') as total_sp
FROM
(
SELECT 
 card.price
 -- ,fwfhealth.vn
 ,fwfhealth.hcode
 ,chospital2.hosname
 ,fwfhealth.card_id
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สสจ') as manages_sj
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='P&P' and typeprice.sentprice='สสจ') as P_and_P
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='claim' and typeprice.sentprice='สสจ') as claim
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สป') as manage_sp
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='HC' and typeprice.sentprice='สป') as HC
FROM fwfhealth
inner join card on fwfhealth.card_id = card.card_id
inner join fwfdatabase on fwfhealth.cid = fwfdatabase.CID
            and fwfhealth.datev = fwfdatabase.regisdate
left join chospital2 on fwfhealth.hcode = chospital2.hospcode

  WHERE fwfhealth.hcode='$hoscode'
    and fwfhealth.datev like '$yg1-05%'
    -- and fwfhealth.check_card='pass'
    )as q
    
    UNION
    
    SELECT
'6-มิ.ย-$yg1' as m
,(SELECT hospcode FROM chospital2 WHERE hospcode='$hoscode') as hcode
,(SELECT hosname FROM chospital2 WHERE hospcode='$hoscode') as hosname
,IFNULL(sum(case when q.price = '365' then 1 else 0 end),'0') as c365
,IFNULL(sum(case when q.price = '500' then 1 else 0 end),'0') as c500
,IFNULL(sum(case when q.price = '730' then 1 else 0 end),'0') as c730
,IFNULL(sum(case when q.price = '900' then 1 else 0 end),'0') as c900
,IFNULL(sum(case when q.price = '1600' then 1 else 0 end),'0') as c1600
,IFNULL(sum(case when q.price = '2200' then 1 else 0 end),'0') as c2200
,IFNULL(sum(case when q.price = '3200' then 1 else 0 end),'0') as c3200
,IFNULL(sum(case when q.price = '3000' then 1 else 0 end),'0') as c3000
,IFNULL(sum(case when q.price = '365' then 1 else 0 end)
    + sum(case when q.price = '500' then 1 else 0 end) 
    + sum(case when q.price = '730' then 1 else 0 end) 
    + sum(case when q.price = '900' then 1 else 0 end) 
    + sum(case when q.price = '1600' then 1 else 0 end) 
    + sum(case when q.price = '2200' then 1 else 0 end) 
    + sum(case when q.price = '3200' then 1 else 0 end) 
    + sum(case when q.price = '3000' then 1 else 0 end),'0') as total_card
    -- เงินจัดสรร
,IFNULL(sum(q.manages_sj),'0') as manages_sj
,IFNULL(sum(q.P_and_P),'0') as P_and_P
,IFNULL(sum(q.claim),'0') as claim
,IFNULL(sum(q.manages_sj) + sum(q.P_and_P) + sum(q.claim),'0') as total_ssj

,IFNULL(sum(q.manage_sp),'0') as manage_sp
,IFNULL(sum(q.HC),'0') as HC
,IFNULL(sum(q.manage_sp) + sum(q.HC),'0') as total_sp
FROM
(
SELECT 
 card.price
 -- ,fwfhealth.vn
 ,fwfhealth.hcode
 ,chospital2.hosname
 ,fwfhealth.card_id
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สสจ') as manages_sj
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='P&P' and typeprice.sentprice='สสจ') as P_and_P
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='claim' and typeprice.sentprice='สสจ') as claim
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สป') as manage_sp
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='HC' and typeprice.sentprice='สป') as HC
FROM fwfhealth
inner join card on fwfhealth.card_id = card.card_id
inner join fwfdatabase on fwfhealth.cid = fwfdatabase.CID
            and fwfhealth.datev = fwfdatabase.regisdate
left join chospital2 on fwfhealth.hcode = chospital2.hospcode

  WHERE fwfhealth.hcode='$hoscode'
    and fwfhealth.datev like '$yg1-06%'
    -- and fwfhealth.check_card='pass'
    )as q
    
    UNION
    
    SELECT
'7-ก.ค-$yg1' as m
,(SELECT hospcode FROM chospital2 WHERE hospcode='$hoscode') as hcode
,(SELECT hosname FROM chospital2 WHERE hospcode='$hoscode') as hosname
,IFNULL(sum(case when q.price = '365' then 1 else 0 end),'0') as c365
,IFNULL(sum(case when q.price = '500' then 1 else 0 end),'0') as c500
,IFNULL(sum(case when q.price = '730' then 1 else 0 end),'0') as c730
,IFNULL(sum(case when q.price = '900' then 1 else 0 end),'0') as c900
,IFNULL(sum(case when q.price = '1600' then 1 else 0 end),'0') as c1600
,IFNULL(sum(case when q.price = '2200' then 1 else 0 end),'0') as c2200
,IFNULL(sum(case when q.price = '3200' then 1 else 0 end),'0') as c3200
,IFNULL(sum(case when q.price = '3000' then 1 else 0 end),'0') as c3000
,IFNULL(sum(case when q.price = '365' then 1 else 0 end)
    + sum(case when q.price = '500' then 1 else 0 end) 
    + sum(case when q.price = '730' then 1 else 0 end) 
    + sum(case when q.price = '900' then 1 else 0 end) 
    + sum(case when q.price = '1600' then 1 else 0 end) 
    + sum(case when q.price = '2200' then 1 else 0 end) 
    + sum(case when q.price = '3200' then 1 else 0 end) 
    + sum(case when q.price = '3000' then 1 else 0 end),'0') as total_card
    -- เงินจัดสรร
,IFNULL(sum(q.manages_sj),'0') as manages_sj
,IFNULL(sum(q.P_and_P),'0') as P_and_P
,IFNULL(sum(q.claim),'0') as claim
,IFNULL(sum(q.manages_sj) + sum(q.P_and_P) + sum(q.claim),'0') as total_ssj

,IFNULL(sum(q.manage_sp),'0') as manage_sp
,IFNULL(sum(q.HC),'0') as HC
,IFNULL(sum(q.manage_sp) + sum(q.HC),'0') as total_sp
FROM
(
SELECT 
 card.price
 -- ,fwfhealth.vn
 ,fwfhealth.hcode
 ,chospital2.hosname
 ,fwfhealth.card_id
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สสจ') as manages_sj
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='P&P' and typeprice.sentprice='สสจ') as P_and_P
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='claim' and typeprice.sentprice='สสจ') as claim
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สป') as manage_sp
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='HC' and typeprice.sentprice='สป') as HC
FROM fwfhealth
inner join card on fwfhealth.card_id = card.card_id
inner join fwfdatabase on fwfhealth.cid = fwfdatabase.CID
            and fwfhealth.datev = fwfdatabase.regisdate
left join chospital2 on fwfhealth.hcode = chospital2.hospcode

  WHERE fwfhealth.hcode='$hoscode'
    and fwfhealth.datev like '$yg1-07%'
    -- and fwfhealth.check_card='pass'
    )as q
    
    UNION
    
    SELECT
'8-ส.ค-$yg1' as m
,(SELECT hospcode FROM chospital2 WHERE hospcode='$hoscode') as hcode
,(SELECT hosname FROM chospital2 WHERE hospcode='$hoscode') as hosname
,IFNULL(sum(case when q.price = '365' then 1 else 0 end),'0') as c365
,IFNULL(sum(case when q.price = '500' then 1 else 0 end),'0') as c500
,IFNULL(sum(case when q.price = '730' then 1 else 0 end),'0') as c730
,IFNULL(sum(case when q.price = '900' then 1 else 0 end),'0') as c900
,IFNULL(sum(case when q.price = '1600' then 1 else 0 end),'0') as c1600
,IFNULL(sum(case when q.price = '2200' then 1 else 0 end),'0') as c2200
,IFNULL(sum(case when q.price = '3200' then 1 else 0 end),'0') as c3200
,IFNULL(sum(case when q.price = '3000' then 1 else 0 end),'0') as c3000
,IFNULL(sum(case when q.price = '365' then 1 else 0 end)
    + sum(case when q.price = '500' then 1 else 0 end) 
    + sum(case when q.price = '730' then 1 else 0 end) 
    + sum(case when q.price = '900' then 1 else 0 end) 
    + sum(case when q.price = '1600' then 1 else 0 end) 
    + sum(case when q.price = '2200' then 1 else 0 end) 
    + sum(case when q.price = '3200' then 1 else 0 end) 
    + sum(case when q.price = '3000' then 1 else 0 end),'0') as total_card
    -- เงินจัดสรร
,IFNULL(sum(q.manages_sj),'0') as manages_sj
,IFNULL(sum(q.P_and_P),'0') as P_and_P
,IFNULL(sum(q.claim),'0') as claim
,IFNULL(sum(q.manages_sj) + sum(q.P_and_P) + sum(q.claim),'0') as total_ssj

,IFNULL(sum(q.manage_sp),'0') as manage_sp
,IFNULL(sum(q.HC),'0') as HC
,IFNULL(sum(q.manage_sp) + sum(q.HC),'0') as total_sp
FROM
(
SELECT 
 card.price
 -- ,fwfhealth.vn
 ,fwfhealth.hcode
 ,chospital2.hosname
 ,fwfhealth.card_id
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สสจ') as manages_sj
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='P&P' and typeprice.sentprice='สสจ') as P_and_P
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='claim' and typeprice.sentprice='สสจ') as claim
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สป') as manage_sp
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='HC' and typeprice.sentprice='สป') as HC
FROM fwfhealth
inner join card on fwfhealth.card_id = card.card_id
inner join fwfdatabase on fwfhealth.cid = fwfdatabase.CID
            and fwfhealth.datev = fwfdatabase.regisdate
left join chospital2 on fwfhealth.hcode = chospital2.hospcode

  WHERE fwfhealth.hcode='$hoscode'
    and fwfhealth.datev like '$yg1-08%'
    -- and fwfhealth.check_card='pass'
    )as q
    
    UNION
    
SELECT
'9-ก.ย-$yg1' as m
,(SELECT hospcode FROM chospital2 WHERE hospcode='$hoscode') as hcode
,(SELECT hosname FROM chospital2 WHERE hospcode='$hoscode') as hosname
,IFNULL(sum(case when q.price = '365' then 1 else 0 end),'0') as c365
,IFNULL(sum(case when q.price = '500' then 1 else 0 end),'0') as c500
,IFNULL(sum(case when q.price = '730' then 1 else 0 end),'0') as c730
,IFNULL(sum(case when q.price = '900' then 1 else 0 end),'0') as c900
,IFNULL(sum(case when q.price = '1600' then 1 else 0 end),'0') as c1600
,IFNULL(sum(case when q.price = '2200' then 1 else 0 end),'0') as c2200
,IFNULL(sum(case when q.price = '3200' then 1 else 0 end),'0') as c3200
,IFNULL(sum(case when q.price = '3000' then 1 else 0 end),'0') as c3000
,IFNULL(sum(case when q.price = '365' then 1 else 0 end)
    + sum(case when q.price = '500' then 1 else 0 end) 
    + sum(case when q.price = '730' then 1 else 0 end) 
    + sum(case when q.price = '900' then 1 else 0 end) 
    + sum(case when q.price = '1600' then 1 else 0 end) 
    + sum(case when q.price = '2200' then 1 else 0 end) 
    + sum(case when q.price = '3200' then 1 else 0 end) 
    + sum(case when q.price = '3000' then 1 else 0 end),'0') as total_card
    -- เงินจัดสรร
,IFNULL(sum(q.manages_sj),'0') as manages_sj
,IFNULL(sum(q.P_and_P),'0') as P_and_P
,IFNULL(sum(q.claim),'0') as claim
,IFNULL(sum(q.manages_sj) + sum(q.P_and_P) + sum(q.claim),'0') as total_ssj

,IFNULL(sum(q.manage_sp),'0') as manage_sp
,IFNULL(sum(q.HC),'0') as HC
,IFNULL(sum(q.manage_sp) + sum(q.HC),'0') as total_sp
FROM
(
SELECT 
 card.price
 -- ,fwfhealth.vn
 ,fwfhealth.hcode
 ,chospital2.hosname
 ,fwfhealth.card_id
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สสจ') as manages_sj
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='P&P' and typeprice.sentprice='สสจ') as P_and_P
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='claim' and typeprice.sentprice='สสจ') as claim
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='บริหารจัดการ' and typeprice.sentprice='สป') as manage_sp
 ,(SELECT typeprice.price as stprice FROM typeprice WHERE typeprice.card_id = fwfhealth.card_id and typeprice.description='HC' and typeprice.sentprice='สป') as HC
FROM fwfhealth
inner join card on fwfhealth.card_id = card.card_id
inner join fwfdatabase on fwfhealth.cid = fwfdatabase.CID
            and fwfhealth.datev = fwfdatabase.regisdate
left join chospital2 on fwfhealth.hcode = chospital2.hospcode

  WHERE fwfhealth.hcode='$hoscode'
    and fwfhealth.datev like '$yg1-09%'
    -- and fwfhealth.check_card='pass'
    )as q


"; 
}

    $query = Yii::$app->db->createCommand($sql)->queryAll();
    $dataProvider = new ArrayDataProvider([
            'allModels' => $query,
            'pagination' => FALSE,
            // 'pagination' => true,
            // 'pagination' => ['pagesize' => 10],
    ]);
  
  
                return $this->render('index', [
                    'dataProvider' => $dataProvider,
                    'query' => $query,
                    'sql' => $sql,
                    'data' => $data,
                    'peengob' => $peengob,
        ]);
    }

}
