<?php

namespace frontend\controllers;
use Yii;
use frontend\models\User;
use yii\data\ArrayDataProvider;
use yii\helpers\Url;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

class DeptorController extends BaseController
{
    public function actionIndex()
    {
        $session = Yii::$app->session;
        $hoscode = $session['hoscode'];

        $data    = Yii::$app->request->get();
        $date1   = $data['date1'] ?? '';
        $date2   = $data['date2'] ?? '';
        $back    = $data['back'] ?? '';
        $forward = $data['forward'] ?? '';
        $peengob = $data['peengob'] ?? '';

        if (!empty($forward)) { $back = ''; }
        if (!empty($back))    { $forward = ''; }

        // ── Date resolution ──────────────────────────────────────
        #########################

        if (!empty($date1) && !empty($date2)) {
            $y1=substr($date1, 0, 4);
            $yt1=$y1-543;
            $m1=substr($date1, 5, 2);
            $d1=substr($date1, 8, 2);
            $dq1=$yt1.'-'.$m1.'-'.$d1;
            $bd1=543+$yt1.'-'.$m1.'-'.$d1;
            $date1=$bd1;
            
        //$dq1=วันที่เริ่มต้น
            $y2=substr($date2, 0, 4);
            $yt2=$y2-543;
            $m2=substr($date2, 5, 2);
            $d2=substr($date2, 8, 2);
            $dq2=$yt2.'-'.$m2.'-'.$d2;
            $bd2=543+$yt2.'-'.$m2.'-'.$d2;
            $date2=$bd2;
            
        //$dq2=วันที่สิ้นสุด
        }else {
              //ดึงข้อมูลทั้งปีงบ
		$datenow=date('Y-m-d');
            $yt=$y1=substr($datenow, 0, 4);
			$m1=substr($datenow, 5, 2);
			
			if($m1<=10){
				$ys=$yt-1;
				$ye=$yt;
				$ym=$yt; //ปีงบก่อน
				$yg=$yt-1; //61 ต.ค.-ธค
				$yg1=$yt; //62 ม.ค.-ก.ย.
			}
			if($m1>9){
				$ys=$yt;
				$ye=$yt+1;	
				$ym=$yt+1; //ปีงบปัจจุบัน
				$yg=$yt;//62 ต.ค.-ธค
				$yg1=$yt+1;//63 ม.ค.-ก.ย.
			}
              $date1=$yg1;
             // $date2=date('Y-m-d');              

              $dq1=$yg.'-10-01';
              $date1=543+$yg.'-10-01';
        	  //$dq1=วันที่เริ่มต้น

              $dq2=$yg1.'-09-30';
              $date2=543+$yg1.'-09-30';
              //$dq2=วันที่สิ้นสุด   
        }
        // =========================================================
        //  Non-SSJ branch  (individual hospital)
        // =========================================================
        $queryop = [];
        if ($hoscode !== '00064') {

            // ── op aggregate queries ──────────────────────────────
            $sqlop = "
                SELECT 
                    q.hospcode,
                    q.hosname,
                    COUNT(DISTINCT q.sq) AS total_visit,
                    SUM(CAST(q.alltotal AS DECIMAL(20,2))) AS alltotal,
                    SUM(CAST(q.claim AS DECIMAL(20,2))) AS claim
                FROM (
                    SELECT 
                        opdata.hospcode,
                        chospital2.hosname,
                        opdata.dateserv,
                        opdata.hn,
                        opdata.seq AS sq,
                        opdata.cid,
                        opdata.`name`,
                        opdata.icd10,
                        opdata.alltotal,
                        CASE 
                            WHEN CAST(opdata.alltotal AS DECIMAL(20,2)) >= 700 THEN 700
                            WHEN CAST(opdata.alltotal AS DECIMAL(20,2)) < 700 THEN opdata.alltotal
                            ELSE NULL 
                        END AS claim,
                        opdata.FundHolderID AS fundHos
                    FROM opdata
                    LEFT JOIN chospital2 ON opdata.hospcode = chospital2.hospcode
                    WHERE opdata.FundHolderID = '$hoscode'
                    AND opdata.FundHolderID <> 'ccccc'
                        AND opdata.dateserv BETWEEN ':startDate' and ':endDate'
                        AND opdata.seq NOT IN (
                          SELECT DISTINCT opdata.seq
                          FROM opdata
                          WHERE opdata.dateserv BETWEEN ':startDate' and ':endDate'
                          AND opdata.icd10 REGEXP '(^|,|\\{|\\})('
                              'Z34|Z13|Z35|Z30|Z31|Z32|Z33|Z39|Z364|Z37|Z21|Z232|Z240|Z244|Z245|Z271|Z246|Z251|Z274|Z273|Z11|Z10|Z298|Z001|Z115|U119'
                          ')($|,|\\}|\\{)'
                    )
                ) AS q
                GROUP BY q.hospcode, q.hosname;
            ";

            $sqlpp = "
                SELECT 
                    q.hospcode,
                    q.hosname,
                    COUNT(DISTINCT q.sq) AS total_visit,
                    SUM(CAST(q.claim AS DECIMAL(20,2))) AS claim,                    
                    SUM(CAST(q.alltotal AS DECIMAL(20,2))) AS alltotal
                FROM (
                    SELECT 
                        opdata.hospcode,
                        chospital2.hosname,
                        opdata.dateserv,
                        opdata.hn,
                        opdata.seq AS sq,
                        opdata.cid,
                        opdata.`name`,
                        opdata.icd10,
                        opdata.alltotal,
                        CASE 
                            WHEN CAST(opdata.alltotal AS DECIMAL(20,2)) >= 700 THEN 700
                            WHEN CAST(opdata.alltotal AS DECIMAL(20,2)) < 700 THEN opdata.alltotal
                            ELSE NULL 
                        END AS claim,
                        opdata.FundHolderID AS fundHos
                    FROM opdata
                    inner JOIN chospital2 ON opdata.hospcode = chospital2.hospcode
                    WHERE opdata.FundHolderID = '$hoscode'
                    AND opdata.hospcode != '$hoscode'
                    AND opdata.FundHolderID <> 'ccccc'
                    AND opdata.dateserv BETWEEN ':startDate' and ':endDate'
                    AND opdata.seq IN (
                      SELECT DISTINCT opdata.seq
                      FROM opdata
                      WHERE opdata.dateserv BETWEEN ':startDate' and ':endDate'
                      AND opdata.icd10 REGEXP '(^|,|\\{|\\})('
                          'Z34|Z13|Z35|Z30|Z31|Z32|Z33|Z39|Z364|Z37|Z21|Z232|Z240|Z244|Z245|Z271|Z246|Z251|Z274|Z273|Z11|Z10|Z298|Z001|Z115|U119'
                      ')($|,|\\}|\\{)'
                    )
                ) AS q
                GROUP BY q.hospcode, q.hosname;
            ";
            
            $sqlip = "
            SELECT
            q.hospcode,
            q.hosname,
            count(q.an) as total_visit,
            sum(q.cost) as claim,
            sum(q.costadjrw) as alltotal
            FROM(
            SELECT
            ipdata.hospcode,
            chospital2.hosname,
            ipdata.admitdate,
            ipdata.dischartdate,
            ipdata.`name`,
            ipdata.fund,
            ipdata.cid,
            ipdata.hn,
            ipdata.an,
            ipdata.icd10,
            ipdata.icd9,
            cast(ipdata.los as int) los,
            ipdata.cost cost,
            ipdata.adjrw,
            TRUNCATE(ipdata.adjrw*8600,2) as costadjrw,
            ipdata.FundHolderID
            FROM
            ipdata
            left join chospital2 on ipdata.hospcode=chospital2.hospcode
            WHERE
            ipdata.FundHolderID = '$hoscode'
            and ipdata.hospcode != '$hoscode'
            and ipdata.FundHolderID<>'ccccc'
            and ipdata.dischartdate between ':startDate' and ':endDate'
            ) as q
            group by q.hospcode            
            ";

            // แทนค่าวันที่ใน SQL
            $sqlop   = $this->bindDates($sqlop, $dq1, $dq2);
            $sqlpp   = $this->bindDates($sqlpp, $dq1, $dq2);
            $sqlip   = $this->bindDates($sqlip, $dq1, $dq2);
            $queryop = Yii::$app->db->createCommand($sqlop)->queryAll();
            $querypp = Yii::$app->db->createCommand($sqlpp)->queryAll();
            $queryip = Yii::$app->db->createCommand($sqlip)->queryAll();
        }

        $dataProvider = new ArrayDataProvider([
            'allModels'  => $queryop,
            'pagination' => ['pageSize' => 10],
            'sort'       => [
                'attributes' => ['hospcode', 'hosname', 'total_visit', 'alltotal', 'claim'],
            ],
        ]);
        $dataProvider2 = new ArrayDataProvider([
            'allModels'  => $querypp,
            'pagination' => ['pageSize' => 10],
            'sort'       => [
                'attributes' => ['hospcode', 'hosname', 'total_visit', 'claim', 'alltotal'],
            ],
        ]);
        $dataProvider3 = new ArrayDataProvider([
            'allModels'  => $queryip,
            'pagination' => ['pageSize' => 10],
            'sort'       => [
                'attributes' => ['hospcode', 'hosname', 'total_visit', 'claim', 'alltotal'],
            ],
        ]);

        return $this->render('index', [
            'date1'        => $date1,
            'date2'        => $date2,
            'dq1'          => $dq1,
            'dq2'          => $dq2,
            'back'         => $back,
            'forward'      => $forward,
            'peengob'      => $peengob,
            'hoscode'      => $hoscode,
            'queryop'      => $queryop,
            'dataProvider' => $dataProvider,
            'dataProvider2' => $dataProvider2,
            'dataProvider3' => $dataProvider3,

        ]);
    }

    private function bindDates(string $sql, string $dq1, string $dq2): string
    {
        return str_replace([':startDate', ':endDate'], [$dq1, $dq2], $sql);
    }

}
