<?php

namespace frontend\controllers;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii;

class OpaController extends BaseController
{
    public function actionIndex()
    {
        $session = Yii::$app->session;
        $hoscode=$session['hoscode'];

        $data = Yii::$app->request->get();
        $date1 = $data['date1'] ?? '';
        $date2 = $data['date2'] ?? '';
  
  
        $sql = "

        SELECT
        DISTINCT
        opdata.*
        FROM
        opdata
        WHERE
        opdata.hospcode = '$hoscode'
        and opdata.FundHolderID != 'ccccc'
        and opdata.dateserv between ':startDate' and ':endDate'
        and op_pp_type = 'op'
        ";
  
        if (!empty($date1) && !empty($date2)) {
            $y1=substr($date1, 0, 4);
            $yt1=$y1-543;
            $m1=substr($date1, 5, 2);
            $d1=substr($date1, 8, 2);
            $dq1=$yt1.'-'.$m1.'-'.$d1;
            
        //$dq1=วันที่เริ่มต้น
            $y2=substr($date2, 0, 4);
            $yt2=$y2-543;
            $m2=substr($date2, 5, 2);
            $d2=substr($date2, 8, 2);
            $dq2=$yt2.'-'.$m2.'-'.$d2;
            
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
              $date2=date('Y-m-d');              

              $dq1=$yg.'-10-01';
              $date1=$yg.'-10-01';
        	  //$dq1=วันที่เริ่มต้น

              $dq2=$yg1.'-09-30';
              $date2=$yg1.'-09-30';
              //$dq2=วันที่สิ้นสุด   
            }
//แทนค่า ? ด้วยวันที่
        while ($ps=strpos($sql,":startDate")) {
        $ps=strpos($sql,":startDate");
        if($ps!==FALSE){  $qr = substr_replace($sql, $dq1, $ps ,10);   }
        $ps2=strpos($qr,":endDate");
        if($ps2!==FALSE){   $qr2 = substr_replace($qr, $dq2, $ps2 ,8); }
        $sql=$qr2;
        }
//แทนค่า ? ด้วยวันที่

    $query = Yii::$app->db->createCommand($sql)->queryAll();
    $dataProvider = new ArrayDataProvider([
            'allModels' => $query,
          //  'pagination' => FALSE,
            'pagination' => true,
            'pagination' => ['pagesize' => 10],
    ]);
  
  
                return $this->render('index', [
                    'dataProvider' => $dataProvider,
                    'query' => $query,
                    'sql' => $sql,
                    'data' => $data,
                    'date1' => $date1,
                    'date2' => $date2,
  
        ]);
    }

}
