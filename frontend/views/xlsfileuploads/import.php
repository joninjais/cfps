<?php
use frontend\models\Ipdata;

echo "My Import</br>";
//print_r($data);
echo '</br>';
echo $data[0][0][0];
echo '</br>';
echo $data[0][0][1];
echo '</br>';
echo $highkey;
echo '</br>';
echo $highestColumn;
echo '</br>';
echo $model->typefileupload;


foreach ($data as $key => $val) {

  $ad_y = substr($data[$key][0][1],0,4);
  $ad_m = substr($data[$key][0][1],5,2); 
  $ad_d = substr($data[$key][0][1],8,2);
  $addate = ($ad_y-543).'-'.$ad_m.'-'.$ad_d; 

  $dc_y = substr($data[$key][0][2],0,4);
  $dc_m = substr($data[$key][0][2],5,2); 
  $dc_d = substr($data[$key][0][2],8,2);
  $dcdate = ($dc_y-543).'-'.$dc_m.'-'.$dc_d; 

  echo $hospcode = $data[$key][0][0];
  echo ' | ';
  echo  $admitdate = $addate;
  echo ' | '; 
  echo  $dischartdate = $dcdate;
  echo ' | ';
  echo  $name = $data[$key][0][3];
  echo ' | ';
  echo  $fund = $data[$key][0][4];
  echo ' | ';
  echo $cid = $data[$key][0][5];
  echo ' | ';
  echo $hn = $data[$key][0][6];
  echo ' | ';
  echo $an = $data[$key][0][7];
  echo ' | ';
  echo $icd10 = $data[$key][0][8];
  echo ' | ';
  echo $icd9 = $data[$key][0][9];
  echo ' | ';
  echo $cost = $data[$key][0][10];
  echo ' | ';
  echo $adjrw = $data[$key][0][11]; 
  echo ' | '; 
  echo  $los = $data[$key][0][12]; 
  echo ' | ';
  echo $costadjrw = $data[$key][0][13];  
  echo '</br>';
  /*
                    Yii::$app->db->createCommand()->insert('ipdata', [
                        'ipid' => '',
                        'hospcode' => $hospcode,
                        'admitdate' => $admitdate,
                        'dischartdate' => $dischartdate,
                        'name' => $name,
                        'fund' => $fund,
                        'cid' => $cid,
                        'hn' => $hn,
                        'an' => $an,
                        'icd10' => $icd10,
                        'icd9' => $icd9,
                        'cost' => $cost,
                        'adjrw' => $adjrw,
                        'los' => $los,
                        'costadjrw' => $costadjrw,
                    ])->execute();
*/


}

echo '</br>';
echo $check;
echo '</br>';

?>