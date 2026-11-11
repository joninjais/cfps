<?php

namespace frontend\controllers;

use Yii;
use frontend\models\Locksentday;
use frontend\models\LocksentdaySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * LocksentdayController implements the CRUD actions for Locksentday model.
 */
class LocksentdayController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Locksentday models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new LocksentdaySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Locksentday model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    // public function actionView($id)
    // {
    //     return $this->render('view', [
    //         'model' => $this->findModel($id),
    //     ]);
    // }

    /**
     * Creates a new Locksentday model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        Yii::$app->facilityAccess->requirePermission('manageLockState');
        $model = new Locksentday();

        if(!empty($_POST)){
            $model->load(Yii::$app->request->post());

            $daylock=$model->daylock;
            if(!empty($daylock)){
            $y1=substr($daylock, 0, 4);
            $yt1=$y1-543;
            $m1=substr($daylock, 5, 2);
            $d1=substr($daylock, 8, 2);
            $dq1=$yt1.'-'.$m1.'-'.$d1;
            $model->daylock=$dq1;        
            }
            $checkLocksentday = Locksentday::findOne(['month'=>$model->month,'pee'=>$model->pee]);
            if($checkLocksentday){
                    //ลบข้อมูลเก่า
                    \Yii::$app->db
                    ->createCommand()
                    ->delete('locksentday', ['month'=>$model->month,'pee'=>$model->pee])
                    ->execute();
                if ($model->save()) {
        
                return $this->redirect(['index']);
                                
                }
            }
            else{
                if ($model->save()) {
        
                return $this->redirect(['index']);
            
                }
            }
        }
        
        // if ($model->load(Yii::$app->request->post()) && $model->save()) {
        
        //     return $this->redirect(['view', 'id' => $model->id]);
        
        // }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Locksentday model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */

    // public function actionUpdate($id)
    // {
    //     $model = $this->findModel($id);

    //     if ($model->load(Yii::$app->request->post()) && $model->save()) {
    //         return $this->redirect(['view', 'id' => $model->id]);
    //     }

    //     return $this->render('update', [
    //         'model' => $model,
    //     ]);
    // }

    /**
     * Deletes an existing Locksentday model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        Yii::$app->facilityAccess->requirePermission('manageLockState');
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Locksentday model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Locksentday the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Locksentday::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
