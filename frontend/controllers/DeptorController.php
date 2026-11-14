<?php

namespace frontend\controllers;
use Yii;
use frontend\models\User;
use yii\data\ArrayDataProvider;
use yii\helpers\Url;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DeptorController extends BaseController
{
    public function actionIndex()
    {
        $session = Yii::$app->session;
        $hoscode = $session['hoscode'];
        $facilityCodesSql = $this->facilityCodesSql();

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
        if (!$this->isProvincialAdmin()) {

            // ── op aggregate queries ──────────────────────────────
            $sqlop = "
            SELECT
                opdata.hospcode,
                chospital2.hosname,
                COUNT(DISTINCT CONCAT_WS('-', opdata.hospcode, opdata.seq)) AS total_visit,
                ROUND(sum(case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
                when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end),2) as claim,
                ROUND(SUM(opdata.alltotal), 2) AS alltotal
            FROM
                opdata
                inner join chospital2
                    on opdata.hospcode = chospital2.hospcode
                    and chospital2.provcode = '82'
            WHERE
                opdata.op_pp_type = 'op'
                -- and opdata.FundHolderID = '11354'
                and opdata.FundHolderID = '$hoscode'
                and opdata.FundHolderID != 'ccccc'
                -- and opdata.dateserv BETWEEN '2025-10-01' AND '2026-09-30'
                and opdata.dateserv BETWEEN ':startDate' and ':endDate'
            GROUP BY
                opdata.hospcode,
                chospital2.hosname
            ORDER BY
                opdata.hospcode DESC
            ";

            $sqlpp = "
            SELECT
                opdata.hospcode,
                chospital2.hosname,
                COUNT(DISTINCT CONCAT_WS('-', opdata.hospcode, opdata.seq)) AS total_visit,
                ROUND(sum(case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
                when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end),2) as claim,
                ROUND(SUM(opdata.alltotal), 2) AS alltotal
            FROM
                opdata
                inner join chospital2
                    on opdata.hospcode = chospital2.hospcode
                    and chospital2.provcode = '82'
            WHERE
                opdata.op_pp_type = 'pp'
                -- and opdata.FundHolderID = '11354'
                and opdata.FundHolderID = '$hoscode'
                and opdata.FundHolderID != 'ccccc'
                -- and opdata.dateserv BETWEEN '2025-10-01' AND '2026-09-30'
                and opdata.dateserv BETWEEN ':startDate' and ':endDate'
            GROUP BY
                opdata.hospcode,
                chospital2.hosname
            ORDER BY
                opdata.hospcode DESC
            ";
            
            $sqlip = "
            SELECT
                q1.hospcode,
                chospital2.hosname,
                COUNT(DISTINCT CONCAT_WS('-', q1.hospcode, q1.an)) AS total_visit,
                ROUND(SUM(q1.costadjrw), 2) AS claim,
                ROUND(SUM(q1.cost), 2) AS alltotal
            FROM ipdata AS q1
            INNER JOIN chospital2
                ON q1.hospcode = chospital2.hospcode
                AND chospital2.provcode = '82'
            WHERE
            --   q1.FundHolderID = '11354'
            q1.FundHolderID = '$hoscode'
            AND q1.FundHolderID != 'ccccc'
            --   AND q1.dischartdate BETWEEN '2025-10-01' AND '2026-09-30'
            AND q1.dischartdate BETWEEN ':startDate' and ':endDate'
            GROUP BY
                q1.hospcode,
                chospital2.hosname
            ORDER BY
                q1.hospcode ASC            
            ";

            // แทนค่าวันที่ใน SQL
            $sqlop   = $this->bindDates($sqlop, $dq1, $dq2);
            $sqlpp   = $this->bindDates($sqlpp, $dq1, $dq2);
            $sqlip   = $this->bindDates($sqlip, $dq1, $dq2);
            $queryop = Yii::$app->db->createCommand($sqlop)->queryAll();
            $querypp = Yii::$app->db->createCommand($sqlpp)->queryAll();
            $queryip = Yii::$app->db->createCommand($sqlip)->queryAll();
        }

        $providers = $this->createReportProviders($data, ['op' => $queryop, 'pp' => $querypp, 'ip' => $queryip]);

        if (isset($data['export']) || isset($data['format'])) {
            $type = $data['export'] ?? null;
            $format = $data['format'] ?? null;
            if (!is_string($type) || !isset($providers[$type]) || !in_array($format, ['csv', 'xlsx', 'txt'], true)) {
                throw new BadRequestHttpException('รูปแบบการส่งออกไม่ถูกต้อง');
            }
            return $this->exportTable($providers[$type], $type, $format);
        }

        $dataProvider = $providers['op'];
        $dataProvider2 = $providers['pp'];
        $dataProvider3 = $providers['ip'];

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

    private function createReportProviders(array $params, array $datasets): array
    {
        $providers = [];
        foreach ($datasets as $type => $models) {
            $showAll = ($params[$type . '-all'] ?? $params['all'] ?? null) === '1';
            $providers[$type] = new ArrayDataProvider([
                'allModels' => $models,
                'pagination' => $showAll ? false : [
                    'pageSize' => 10,
                    'pageParam' => $type . '-page',
                    'pageSizeParam' => $type . '-per-page',
                    'params' => array_merge($params, ['#' => 'deptor-' . $type]),
                ],
                'sort' => [
                    'sortParam' => $type . '-sort',
                    'params' => array_merge($params, ['#' => 'deptor-' . $type]),
                    'attributes' => ['hospcode', 'hosname', 'total_visit', 'claim', 'alltotal'],
                ],
            ]);
        }
        return $providers;
    }

    private function exportTable(ArrayDataProvider $provider, string $type, string $format)
    {
        $headers = ['ลำดับ', 'รหัสสถานพยาบาล', 'ชื่อสถานพยาบาล', 'จำนวนครั้ง', 'ยอดเบิก (บาท)', 'ยอดรวม (บาท)'];
        $rows = [];
        $models = array_values($provider->getModels());
        $pagination = $provider->getPagination();
        $offset = $pagination === false ? 0 : $pagination->getOffset();
        foreach ($models as $index => $model) {
            $rows[] = [
                $offset + $index + 1,
                (string)($model['hospcode'] ?? ''),
                (string)($model['hosname'] ?? ''),
                $model['total_visit'],
                $model['claim'],
                $model['alltotal'],
            ];
        }

        $stream = fopen('php://temp', 'w+b');
        if ($stream === false) {
            throw new \RuntimeException('ไม่สามารถสร้างไฟล์ส่งออกได้');
        }
        try {
            if ($format === 'xlsx') {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                foreach (array_merge([$headers], $rows) as $rowIndex => $row) {
                    foreach ($row as $columnIndex => $value) {
                        $sheet->setCellValueExplicit(
                            [$columnIndex + 1, $rowIndex + 1],
                            $rowIndex === 0 || $columnIndex === 1 || $columnIndex === 2 || $value === null
                                ? (string)$value : (float)$value,
                            $rowIndex === 0 || $columnIndex === 1 || $columnIndex === 2 || $value === null
                                ? DataType::TYPE_STRING : DataType::TYPE_NUMERIC
                        );
                    }
                }
                $sheet->getStyle('A1:F1')->getFont()->setBold(true);
                $sheet->getStyle('E2:F' . max(2, count($rows) + 1))->getNumberFormat()->setFormatCode('#,##0.00');
                (new Xlsx($spreadsheet))->save($stream);
                $spreadsheet->disconnectWorksheets();
                $mime = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            } else {
                fwrite($stream, "\xEF\xBB\xBF");
                foreach (array_merge([$headers], $rows) as $row) {
                    foreach ($row as $columnIndex => &$value) {
                        if (is_string($value)
                            && ($columnIndex === 1 || $columnIndex === 2 || !is_numeric($value))
                            && preg_match('/^(?:[\x09\x0a\x0d]|[\s\x00-\x1f]*[=+\-@])/u', $value)) {
                            $value = "'" . $value;
                        }
                    }
                    unset($value);
                    if (fputcsv($stream, $row, $format === 'csv' ? ',' : "\t", '"', '') === false) {
                        throw new \RuntimeException('ไม่สามารถเขียนไฟล์ส่งออกได้');
                    }
                }
                $mime = $format === 'csv' ? 'text/csv' : 'text/plain';
            }
            rewind($stream);
            $content = stream_get_contents($stream);
            if ($content === false) {
                throw new \RuntimeException('ไม่สามารถอ่านไฟล์ส่งออกได้');
            }
        } finally {
            fclose($stream);
        }

        return Yii::$app->response->sendContentAsFile(
            $content,
            'deptor_' . $type . '_' . date('Y-m-d') . '.' . $format,
            ['mimeType' => $mime]
        );
    }

    private function bindDates(string $sql, string $dq1, string $dq2): string
    {
        return str_replace([':startDate', ':endDate'], [$dq1, $dq2], $sql);
    }

}
