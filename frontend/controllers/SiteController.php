<?php
namespace frontend\controllers;

use Yii;
use yii\helpers\Url;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use yii\data\ArrayDataProvider;
use frontend\models\User;
use frontend\models\Fwfdatabase;
use frontend\models\LoginHistory;
use frontend\components\RegistrationWindow;
use yii\web\NotFoundHttpException;

/**
 * Site controller
 */
class SiteController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                    'registration-status' => ['get'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Public actions that do not require authentication.
     */
    protected function getPublicActionIds(): array
    {
        return ['login', 'signup', 'registration-status', 'facility-name', 'error', 'captcha', 'request-password-reset', 'reset-password'];
    }

    public function actionRegistrationStatus()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->headers->set('Cache-Control', 'no-store, private');
        $window = RegistrationWindow::current();
        $now = time();
        if (!RegistrationWindow::isOpen($window)) {
            return ['open' => false];
        }
        return [
            'open' => true,
            'url' => Url::to(['/site/signup', 'token' => $window['token']]),
            'remaining' => max(0, (int)$window['expires_at'] - $now),
        ];
    }

    public function actionFacilityName()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $hoscode = trim((string)Yii::$app->request->get('hoscode', ''));

        if (!preg_match('/^\d{5}$/D', $hoscode)) {
            return ['name' => null];
        }

        return [
            'name' => Yii::$app->facilityAccess->facilityName($hoscode),
            'role' => Yii::$app->facilityAccess->expectedStaffRole($hoscode),
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {

        $session = Yii::$app->session;
        $hoscode=$session['hoscode'];
        $facilityCodesSql = $this->facilityCodesSql();
        $hospitalFacilityCodesSql = $this->hospitalFacilityCodesSql();

        
        $datenow=date('Y-m-d');
        // รายการรหัส ICD10 กลุ่ม PP (ใช้ร่วมกันทุก query)
        $icd10Codes = ['Z34','Z13','Z35','Z30','Z31','Z32','Z33','Z39',
                       'Z364','Z37','Z21','Z232','Z240','Z244','Z245',
                       'Z271','Z246','Z251','Z274','Z273','Z11','Z10',
                       'Z298','Z001','Z115','U119'];
        $icd10CondOp = implode(' or ', array_map(fn($c) => "opdata.icd10 LIKE '%{$c}%'", $icd10Codes));
        $icd10CondQ  = implode(' or ', array_map(fn($c) => "q.icd10 LIKE '%{$c}%'", $icd10Codes));

        $data = Yii::$app->request->get();
        $date1 = $data['date1'] ?? '';
        $date2 = $data['date2'] ?? '';	
        $back = $data['back'] ?? '';
        $forward = $data['forward'] ?? '';
        $peengob = $data['peengob'] ?? '';

        if(!empty($forward)){
          $back='';
        }
        if(!empty($back)){
          $forward='';
        }
################################### client op pp ip ##########################################        
    if(!$this->isProvincialAdmin()){
        $sqlip = "
        SELECT 
        q.FundHolderID hoscode, 
        q.hosname,
        count(q.an) an,
        sum(q.los) los,
        sum(q.cost) cost,
        TRUNCATE(sum(q.adjrw),4) adjrw,
        sum(q.costadjrw) costadjrw
        from(
        SELECT
        ipdata.ipid,
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
        left join chospital2 on ipdata.FundHolderID=chospital2.hospcode
        WHERE
        ipdata.hospcode IN ($facilityCodesSql)
				and ipdata.FundHolderID <> 'ccccc'
        and ipdata.dischartdate between ':startDate' and ':endDate'
        )as q 
        GROUP BY q.FundHolderID
        ";

        $sqlipall = "
        SELECT hospcode as hos 
        ,COUNT(distinct an) as an
        ,sum(los) as los 
        ,sum(cost) as cost 
        ,TRUNCATE(sum(adjrw),4) as adjrw 
        ,TRUNCATE(sum(costadjrw),2) as costadjrw        
        from
        ipdata 
        where dischartdate between ':startDate' and ':endDate'
        and hospcode IN ($facilityCodesSql)
        ";

        $sqlip_a = "
        SELECT q.hospcode hospcode
        ,COUNT(q.an) an
        ,sum(q.los) los
        ,sum(q.cost) cost
        ,TRUNCATE(sum(adjrw),4) adjrw
        ,TRUNCATE(sum(q.costadjrw),2) costadjrw
        from (        
				SELECT * FROM ipdata
        WHERE hospcode IN ($facilityCodesSql)
				and dischartdate between ':startDate' and ':endDate'
				and FundHolderID <> 'ccccc'
				) as q
        ";

        $sqlip_c = "
        SELECT q.hospcode hospcode
        ,COUNT(q.an) an
        ,sum(q.los) los
        ,sum(q.cost) cost
        ,TRUNCATE(sum(adjrw),4) adjrw
        ,TRUNCATE(sum(q.costadjrw),2) costadjrw
        from (        
				SELECT * FROM ipdata
        WHERE hospcode IN ($facilityCodesSql)
				and dischartdate between ':startDate' and ':endDate'
				and FundHolderID = 'ccccc'
				) as q
        ";

        $sqlip_tkp = "
        SELECT 
        count(q.an) an,
        sum(q.los) los,
        sum(q.cost) cost,
        TRUNCATE(sum(q.adjrw),4) adjrw,
        sum(q.costadjrw) costadjrw
        from(
        SELECT
        ipdata.ipid,
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
        left join chospital2 on ipdata.FundHolderID=chospital2.hospcode
        WHERE
        ipdata.hospcode IN ($facilityCodesSql)
				-- and ipdata.FundHolderID='10740'
				and ipdata.FundHolderID='10740'
				-- and ipdata.FundHolderID not in('10739','10740')
				and ipdata.FundHolderID<>'ccccc'
        and ipdata.dischartdate between ':startDate' and ':endDate'
				)as q 

        ";

        $sqlip_png = "
        SELECT 
        count(q.an) an,
        sum(q.los) los,
        sum(q.cost) cost,
        TRUNCATE(sum(q.adjrw),4) adjrw,
        sum(q.costadjrw) costadjrw
        from(
        SELECT
        ipdata.ipid,
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
        left join chospital2 on ipdata.FundHolderID=chospital2.hospcode
        WHERE
        ipdata.hospcode IN ($facilityCodesSql)
		and ipdata.FundHolderID='10739'
		and ipdata.FundHolderID<>'ccccc'
        and ipdata.dischartdate between ':startDate' and ':endDate'
				)as q 
        ";

        $sqlip_ssj = "
        SELECT 
        count(q.an) an,
        sum(q.los) los,
        sum(q.cost) cost,
        TRUNCATE(sum(q.adjrw),4) adjrw,
        sum(q.costadjrw) costadjrw
        from(
        SELECT
        ipdata.ipid,
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
        left join chospital2 on ipdata.FundHolderID=chospital2.hospcode
        WHERE
        ipdata.hospcode IN ($facilityCodesSql)
		and ipdata.FundHolderID not in('10739','10740')
		and ipdata.FundHolderID<>'ccccc'
        and ipdata.dischartdate between ':startDate' and ':endDate'
				)as q 
        ";
################## pp ##########################
        $sqlpp_all = "
        SELECT 
        count(q.sq) as sq 
        ,sum(q.alltotal) as alltotal
        ,sum(q.claim) as claim
        
        FROM(

        SELECT opdata.hospcode as hospcode
        ,substring(opdata.dateserv,6,2) as month1
        ,opdata.hn as hn
        ,opdata.seq as sq
        ,opdata.cid as cid
        ,opdata.`name` as pt
        ,opdata.icd10 as icd10
        ,opdata.alltotal as alltotal
        ,case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
        when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
        ,opdata.FundHolderID as fundHos

        FROM opdata 

        WHERE  hospcode IN ($hospitalFacilityCodesSql)
--        and ($icd10CondOp)
        and op_pp_type = 'pp'
		and opdata.dateserv between ':startDate' and ':endDate'        
				
        ) as q
        ";

        $sqlpp_a = "
        SELECT 
        count(q.sq) as sq 
        ,sum(q.alltotal) as alltotal
        ,sum(q.claim) as claim
        
        FROM
        (
            SELECT opdata.hospcode as hospcode
        ,substring(opdata.dateserv,1,10) dateserv
        ,opdata.hn as hn
        ,opdata.seq as sq
        ,opdata.cid as cid
        ,opdata.`name` as pt
        ,opdata.icd10 as icd10
        ,opdata.alltotal as alltotal
        ,case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
        when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
        ,opdata.FundHolderID as fundHos

        FROM opdata 

        WHERE  hospcode IN ($hospitalFacilityCodesSql)
--        and ($icd10CondOp)
        and op_pp_type = 'pp'
				and opdata.FundHolderID <> 'ccccc'
				and opdata.dateserv between ':startDate' and ':endDate'
        ) as q
        ";

        $sqlpp_c = "
        SELECT 
        count(q.sq) as sq 
        ,sum(q.alltotal) as alltotal
        ,sum(q.claim) as claim
        
        FROM
        (
            SELECT
            DISTINCT
            opdata.hospcode as hospcode
            ,substring(opdata.dateserv,1,10) as dateserv
            ,opdata.hn as hn
            ,opdata.seq as sq
            ,opdata.cid as cid
            ,opdata.`name` as pt
            ,opdata.icd10 as icd10
            ,opdata.alltotal as alltotal
            ,case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
            when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
    
            FROM opdata 
    
            WHERE  hospcode IN ($hospitalFacilityCodesSql)
--            and ($icd10CondOp)
            and op_pp_type = 'pp'
                    and opdata.dateserv between ':startDate' and ':endDate'
                    and opdata.FundHolderID='ccccc'
        ) as q
        ";
   
        $sqlpp = "
        SELECT 
        q.fundHos hoscode, 
        q.hosname hosname,
        count(q.sq) sq,
        sum(q.alltotal) alltotal,
        sum(q.claim) claim
        from(
        SELECT
        DISTINCT
        opdata.hospcode as hospcode
        ,substring(opdata.dateserv,6,2) as month1
        ,opdata.hn as hn
        ,opdata.seq as sq
        ,opdata.cid as cid
        ,opdata.`name` as pt
        ,opdata.icd10 as icd10
        ,opdata.alltotal as alltotal
        ,case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
        when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
        ,opdata.FundHolderID as fundHos
        ,chospital2.hosname hosname
        FROM opdata				   
        left join chospital2 on opdata.FundHolderID=chospital2.hospcode
        WHERE  opdata.hospcode IN ($hospitalFacilityCodesSql)
--        and ($icd10CondOp)
        and op_pp_type = 'pp'
		and opdata.dateserv between ':startDate' and ':endDate'
        and opdata.FundHolderID <> 'ccccc'
        ) as q
        group by q.fundHos
        ";

        $sqlpp_tkp = "
        SELECT 
        count(q.sq) sq,
        sum(q.alltotal) alltotal,
        sum(q.claim) claim
        from(
        SELECT
        DISTINCT
        opdata.hospcode as hospcode
        ,substring(opdata.dateserv,6,2) as month1
        ,opdata.hn as hn
        ,opdata.seq as sq
        ,opdata.cid as cid
        ,opdata.`name` as pt
        ,opdata.icd10 as icd10
        ,opdata.alltotal as alltotal
        ,case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
        when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
        ,opdata.FundHolderID as fundHos
        ,chospital2.hosname hosname
        FROM opdata				   
        left join chospital2 on opdata.FundHolderID=chospital2.hospcode
        WHERE  opdata.hospcode IN ($hospitalFacilityCodesSql)
--        and ($icd10CondOp)
        and op_pp_type = 'pp'
		and opdata.dateserv between ':startDate' and ':endDate'
		and opdata.FundHolderID='10740'
        and opdata.FundHolderID <> 'ccccc'
        )as q
        
        ";

        $sqlpp_png = "
        SELECT 
        count(q.sq) sq,
        sum(q.alltotal) alltotal,
        sum(q.claim) claim
        from(
        SELECT
        DISTINCT
        opdata.hospcode as hospcode
        ,substring(opdata.dateserv,6,2) as month1
        ,opdata.hn as hn
        ,opdata.seq as sq
        ,opdata.cid as cid
        ,opdata.`name` as pt
        ,opdata.icd10 as icd10
        ,opdata.alltotal as alltotal
        ,case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
        when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
        ,opdata.FundHolderID as fundHos
        ,chospital2.hosname hosname
        FROM opdata				   
        left join chospital2 on opdata.FundHolderID=chospital2.hospcode
        WHERE  opdata.hospcode IN ($hospitalFacilityCodesSql)
--        and ($icd10CondOp)
        and op_pp_type = 'pp'
		and opdata.dateserv between ':startDate' and ':endDate'
		and opdata.FundHolderID='10739'
        and opdata.FundHolderID <> 'ccccc'
        )as q
        
        ";

        $sqlpp_ssj = "
        SELECT 

        count(q.sq) sq,
        sum(q.alltotal) alltotal,
        sum(q.claim) claim
        from(
        SELECT
        DISTINCT
        opdata.hospcode as hospcode
        ,substring(opdata.dateserv,6,2) as month1
        ,opdata.hn as hn
        ,opdata.seq as sq
        ,opdata.cid as cid
        ,opdata.`name` as pt
        ,opdata.icd10 as icd10
        ,opdata.alltotal as alltotal
        ,case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
        when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
        ,opdata.FundHolderID as fundHos
        ,chospital2.hosname hosname
        FROM opdata				   
        left join chospital2 on opdata.FundHolderID=chospital2.hospcode
        WHERE  opdata.hospcode IN ($hospitalFacilityCodesSql)
--        and ($icd10CondOp)
        and op_pp_type = 'pp'
		and opdata.dateserv between ':startDate' and ':endDate'
		and opdata.FundHolderID not in ('10739','10740')
        and opdata.FundHolderID <> 'ccccc'
        )as q
        
        ";

        ############################### OP #################################

        $sqlop_all = "
        SELECT
        count(q.sq) as sq 
        ,sum(q.alltotal) as alltotal
        ,sum(q.claim) as claim
        FROM
        (
        SELECT
        DISTINCT
        opdata.hospcode,
        opdata.dateserv,
        opdata.hn,
        opdata.seq sq,
        opdata.cid,
        opdata.`name`,
        opdata.icd10,
        opdata.alltotal,
        case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
        when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
        FROM
        opdata
        WHERE
        opdata.hospcode IN ($hospitalFacilityCodesSql)
        and opdata.dateserv between ':startDate' and ':endDate'
        and op_pp_type = 'op'
        )as q
        ";

        $sqlop_a = "
        SELECT
        count(q.sq) as sq 
        ,sum(q.alltotal) as alltotal
        ,sum(q.claim) as claim
        FROM
        (
        SELECT
        DISTINCT
        opdata.hospcode,
        opdata.dateserv,
        opdata.hn,
        opdata.seq sq,
        opdata.cid,
        opdata.`name`,
        opdata.icd10,
        opdata.alltotal,
        case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
        when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
        FROM
        opdata
        WHERE
        opdata.hospcode IN ($hospitalFacilityCodesSql)
        and opdata.FundHolderID <> 'ccccc'
        and opdata.dateserv between ':startDate' and ':endDate'
        and op_pp_type = 'op'
        )as q
        ";

        $sqlop_c = "
        SELECT
        count(q.sq) as sq 
        ,sum(q.alltotal) as alltotal
        ,sum(q.claim) as claim
        FROM
        (
        SELECT
        DISTINCT
        opdata.hospcode,
        opdata.dateserv,
        opdata.hn,
        opdata.seq sq,
        opdata.cid,
        opdata.`name`,
        opdata.icd10,
        opdata.alltotal,
        case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
        when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
        FROM
        opdata
        WHERE
        opdata.hospcode IN ($hospitalFacilityCodesSql)
        and opdata.FundHolderID = 'ccccc'
        and opdata.dateserv between ':startDate' and ':endDate'
        and op_pp_type = 'op'
        )as q
        ";
   
        $sqlop = "
        SELECT 
        q.fundHos hoscode, 
        q.hosname hosname,
        count(q.sq) sq,
        sum(q.alltotal) alltotal,
        sum(q.claim) claim
        from(
					SELECT
					DISTINCT
					opdata.hospcode,
					opdata.dateserv,
					opdata.hn,
					opdata.seq sq,
					opdata.cid,
					opdata.`name`,
					opdata.icd10,
					opdata.alltotal,
					case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
					when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim,
					opdata.FundHolderID as fundHos,
					chospital2.hosname hosname
					FROM
					opdata
					left join chospital2 on opdata.FundHolderID=chospital2.hospcode
					WHERE
					opdata.hospcode IN ($hospitalFacilityCodesSql)
					and opdata.FundHolderID<>'ccccc'
					and opdata.dateserv between ':startDate' and ':endDate'
                    and op_pp_type = 'op'
					) as q
        group by q.fundHos

        ";

        $sqlop_tkp = "
        SELECT 
        count(q.sq) sq,
        sum(q.alltotal) alltotal,
        sum(q.claim) claim
        from(
					SELECT
					DISTINCT
					opdata.hospcode,
					opdata.dateserv,
					opdata.hn,
					opdata.seq sq,
					opdata.cid,
					opdata.`name`,
					opdata.icd10,
					opdata.alltotal,
					case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
					when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim,
					opdata.FundHolderID as fundHos,
					chospital2.hosname hosname
					FROM
					opdata
					left join chospital2 on opdata.FundHolderID=chospital2.hospcode
					WHERE
					opdata.hospcode IN ($hospitalFacilityCodesSql)
					and opdata.FundHolderID='10740'
					and opdata.FundHolderID<>'ccccc'
					and opdata.dateserv between ':startDate' and ':endDate'
                    and op_pp_type = 'op'
					) as q

        
        ";

        $sqlop_png = "
        SELECT 
        count(q.sq) sq,
        sum(q.alltotal) alltotal,
        sum(q.claim) claim
        from(
					SELECT
					DISTINCT
					opdata.hospcode,
					opdata.dateserv,
					opdata.hn,
					opdata.seq sq,
					opdata.cid,
					opdata.`name`,
					opdata.icd10,
					opdata.alltotal,
					case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
					when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim,
					opdata.FundHolderID as fundHos,
					chospital2.hosname hosname
					FROM
					opdata
					left join chospital2 on opdata.FundHolderID=chospital2.hospcode
					WHERE
					opdata.hospcode IN ($hospitalFacilityCodesSql)
					and opdata.FundHolderID='10739'
					and opdata.FundHolderID<>'ccccc'
					and opdata.dateserv between ':startDate' and ':endDate'
                    and op_pp_type = 'op'
					) as q

        
        ";

        $sqlop_ssj = "
        SELECT 
        count(q.sq) sq,
        sum(q.alltotal) alltotal,
        sum(q.claim) claim
        from(
					SELECT
					DISTINCT
					opdata.hospcode,
					opdata.dateserv,
					opdata.hn,
					opdata.seq sq,
					opdata.cid,
					opdata.`name`,
					opdata.icd10,
					opdata.alltotal,
					case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
					when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim,
					opdata.FundHolderID as fundHos,
					chospital2.hosname hosname
					FROM
					opdata
					left join chospital2 on opdata.FundHolderID=chospital2.hospcode
					WHERE
					opdata.hospcode IN ($hospitalFacilityCodesSql)
					and opdata.FundHolderID not in('10740','10739')
					and opdata.FundHolderID<>'ccccc'
					and opdata.dateserv between ':startDate' and ':endDate'
                    and op_pp_type = 'op'
					) as q        
        ";


        $sqlrpst_claim = "
            SELECT DISTINCT
            q.hospcode
            ,q.hosname 
            ,COUNT(q.seq) as seq
            ,CAST(sum(q.alltotal) as DECIMAL(20,2)) as alltotal
            ,CAST(sum(q.claim) as DECIMAL(20,2)) as claim
            from
            (
            SELECT
                 opdata.* 
                 ,chospital2.hosname
                 ,case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
                    when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
                    FROM opdata       
                    left join chospital2 on opdata.hospcode=chospital2.hospcode
            
                    WHERE  
                 opdata.dateserv between  ':startDate' and ':endDate'
            
                 and opdata.hospcode not in(
                 SELECT hospcode FROM chospital2 WHERE hospcode in(
                 SELECT DISTINCT cupcode FROM chospital2 WHERE provcode='82'
                 )
                 )
                    and opdata.FundHolderID <> 'ccccc'
                and opdata.FundHolderID IN ($facilityCodesSql)
                    ) as q
                    GROUP BY q.hosname     
            ";  
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

        // แทนค่าวันที่ใน SQL ทุกตัว (IP)
        $sqlip        = $this->bindDates($sqlip, $dq1, $dq2);
        $sqlipall     = $this->bindDates($sqlipall, $dq1, $dq2);
        $sqlip_a      = $this->bindDates($sqlip_a, $dq1, $dq2);
        $sqlip_c      = $this->bindDates($sqlip_c, $dq1, $dq2);
        $sqlip_tkp    = $this->bindDates($sqlip_tkp, $dq1, $dq2);
        $sqlip_png    = $this->bindDates($sqlip_png, $dq1, $dq2);
        $sqlip_ssj    = $this->bindDates($sqlip_ssj, $dq1, $dq2);
        // PP
        $sqlpp_all    = $this->bindDates($sqlpp_all, $dq1, $dq2);
        $sqlpp_a      = $this->bindDates($sqlpp_a, $dq1, $dq2);
        $sqlpp_c      = $this->bindDates($sqlpp_c, $dq1, $dq2);
        $sqlpp        = $this->bindDates($sqlpp, $dq1, $dq2);
        $sqlpp_tkp    = $this->bindDates($sqlpp_tkp, $dq1, $dq2);
        $sqlpp_png    = $this->bindDates($sqlpp_png, $dq1, $dq2);
        $sqlpp_ssj    = $this->bindDates($sqlpp_ssj, $dq1, $dq2);
        // OP
        $sqlop_all    = $this->bindDates($sqlop_all, $dq1, $dq2);
        $sqlop_a      = $this->bindDates($sqlop_a, $dq1, $dq2);
        $sqlop_c      = $this->bindDates($sqlop_c, $dq1, $dq2);
        $sqlop        = $this->bindDates($sqlop, $dq1, $dq2);
        $sqlop_tkp    = $this->bindDates($sqlop_tkp, $dq1, $dq2);
        $sqlop_png    = $this->bindDates($sqlop_png, $dq1, $dq2);
        $sqlop_ssj    = $this->bindDates($sqlop_ssj, $dq1, $dq2);
        // รพสต
        $sqlrpst_claim = $this->bindDates($sqlrpst_claim, $dq1, $dq2);

        #########################

        $queryip = Yii::$app->db->createCommand($sqlip)->queryAll();
        $queryipall = Yii::$app->db->createCommand($sqlipall)->queryAll();
        $queryip_a = Yii::$app->db->createCommand($sqlip_a)->queryAll();
        $queryip_c = Yii::$app->db->createCommand($sqlip_c)->queryAll();

        $queryip_tkp = Yii::$app->db->createCommand($sqlip_tkp)->queryAll();
        $queryip_png = Yii::$app->db->createCommand($sqlip_png)->queryAll();
        $queryip_ssj = Yii::$app->db->createCommand($sqlip_ssj)->queryAll();
        ##################### PP ######################
        $querypp_all = Yii::$app->db->createCommand($sqlpp_all)->queryAll();
        $querypp_a = Yii::$app->db->createCommand($sqlpp_a)->queryAll();
        $querypp_c = Yii::$app->db->createCommand($sqlpp_c)->queryAll();
        $querypp = Yii::$app->db->createCommand($sqlpp)->queryAll();
        
        $querypp_tkp = Yii::$app->db->createCommand($sqlpp_tkp)->queryAll();
        $querypp_png = Yii::$app->db->createCommand($sqlpp_png)->queryAll();
        $querypp_ssj = Yii::$app->db->createCommand($sqlpp_ssj)->queryAll();
            ##################### OP ######################
        $queryop_all = Yii::$app->db->createCommand($sqlop_all)->queryAll();
        $queryop_a = Yii::$app->db->createCommand($sqlop_a)->queryAll();
        $queryop_c = Yii::$app->db->createCommand($sqlop_c)->queryAll();
        $queryop = Yii::$app->db->createCommand($sqlop)->queryAll();
        
        $queryop_tkp = Yii::$app->db->createCommand($sqlop_tkp)->queryAll();
        $queryop_png = Yii::$app->db->createCommand($sqlop_png)->queryAll();
        $queryop_ssj = Yii::$app->db->createCommand($sqlop_ssj)->queryAll();

        $querysqlrpst_claim = Yii::$app->db->createCommand($sqlrpst_claim)->queryAll(); 

               # return $this->render('index');
               return $this->render('index', [

                'queryip' => $queryip,
                'queryipall' => $queryipall,
                'queryip_a' => $queryip_a,
                'queryip_c' => $queryip_c,
                'queryip_tkp' => $queryip_tkp,
                'queryip_png' => $queryip_png,
                'queryip_ssj' => $queryip_ssj,
                'querypp_all' => $querypp_all,
                'querypp_a' => $querypp_a,
                'querypp_c' => $querypp_c,
                'querypp' => $querypp,
                'querypp_tkp' => $querypp_tkp,
                'querypp_png' => $querypp_png,
                'querypp_ssj' => $querypp_ssj,

                'queryop_all' => $queryop_all,
                'queryop_a' => $queryop_a,
                'queryop_c' => $queryop_c,
                'queryop' => $queryop,
                'queryop_tkp' => $queryop_tkp,
                'queryop_png' => $queryop_png,
                'queryop_ssj' => $queryop_ssj,

                'querysqlrpst_claim' => $querysqlrpst_claim,

                'date1' => $date1,
                'date2' => $date2,
                'back' => $back,
                'forward' => $forward,
                'peengob' => $peengob,
    ]);
               } //  if($hoscode!='00064'){ 
################################### SSJ ##########################################
                if($this->isProvincialAdmin()){

            #### 10740 ####                   
            $sqlssjop_10739 = "
            SELECT 
            q.hospcode
            ,q.hosname
            ,count(q.seq) as seq 
                    ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10739' THEN q.claim ELSE 0 END),2) '10739'
                    ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10740' THEN q.claim ELSE 0 END),2) '10740'
                    ,TRUNCATE(sum(CASE WHEN q.FundHolderID not in('10739','10740') THEN q.claim ELSE 0 END),2) 'SSJ'
            ,TRUNCATE(sum(q.claim),2) as claim
            from(
                SELECT
                opdata.* 
                ,chospital2.hosname
                ,case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
                            when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
                FROM
                opdata
                left join chospital2 on opdata.FundHolderID=chospital2.hospcode
                WHERE
                opdata.hospcode = '10739'
                -- and opdata.FundHolderID='10740'
                -- and opdata.FundHolderID='10739'
                -- and opdata.FundHolderID not in('10740','10739')
                and opdata.FundHolderID<>'ccccc'
                and opdata.dateserv between ':startDate' and ':endDate'
                and op_pp_type = 'op'
                ) as q

            ";

            $sqlssjpp_10739 = "
                SELECT 
                q.hospcode
                ,q.hosname
                ,count(q.seq) as seq 
                ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10739' THEN q.claim ELSE 0 END),2) '10739'
                ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10740' THEN q.claim ELSE 0 END),2) '10740'
                ,TRUNCATE(sum(CASE WHEN q.FundHolderID not in('10739','10740') THEN q.claim ELSE 0 END),2) 'SSJ'
                ,TRUNCATE(sum(q.claim),2) as claim
                from(
                SELECT
                opdata.* 
                ,chospital2.hosname
                ,case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
                when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
                FROM opdata				   
                left join chospital2 on opdata.FundHolderID=chospital2.hospcode
                WHERE  opdata.hospcode = '10739'
                and op_pp_type = 'pp'
                and opdata.dateserv between ':startDate' and ':endDate'	
                -- and opdata.FundHolderID='10740'
                -- and opdata.FundHolderID='10739'
                -- and opdata.FundHolderID not in ('10739','10740')
                and opdata.FundHolderID <> 'ccccc'
                )as q
            
    
            ";                    

            $sqlssjip_10739 = "
            SELECT
            q.hospcode,
            q.hosname,
            TRUNCATE(sum(CASE WHEN q.FundHolderID='10739' THEN q.costadjrw ELSE 0 END),2) '10739',
            TRUNCATE(sum(CASE WHEN q.FundHolderID='10740' THEN q.costadjrw ELSE 0 END),2) '10740',
            TRUNCATE(sum(CASE WHEN q.FundHolderID not in('10739','10740') THEN q.costadjrw ELSE 0 END),2) 'SSJ',
            TRUNCATE(sum(q.costadjrw),2) costadjrw
            from
            (
            SELECT
                ipdata.* ,chospital2.hosname
            FROM
                ipdata 
                left join chospital2 on chospital2.hospcode=ipdata.hospcode
            WHERE ipdata.hospcode='10739'
            and ipdata.FundHolderID<>'ccccc'	
            
                    and ipdata.dischartdate between ':startDate' and ':endDate'	
            )as q
            
            ";                      
    #### 10739 ####

            #### 10740 ####                   
            $sqlssjop_10740 = "
            SELECT 
            q.hospcode
            ,q.hosname
            ,count(q.seq) as seq 
                    ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10739' THEN q.claim ELSE 0 END),2) '10739'
                    ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10740' THEN q.claim ELSE 0 END),2) '10740'
                    ,TRUNCATE(sum(CASE WHEN q.FundHolderID not in('10739','10740') THEN q.claim ELSE 0 END),2) 'SSJ'
            ,TRUNCATE(sum(q.claim),2) as claim
            from(
                SELECT
                opdata.* 
                ,chospital2.hosname
                ,case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
                            when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
                FROM
                opdata
                left join chospital2 on opdata.FundHolderID=chospital2.hospcode
                WHERE
                opdata.hospcode = '10740'
                -- and opdata.FundHolderID='10740'
                -- and opdata.FundHolderID='10739'
                -- and opdata.FundHolderID not in('10740','10739')
                and opdata.FundHolderID<>'ccccc'
                and opdata.dateserv between ':startDate' and ':endDate'
                and op_pp_type = 'op'
                ) as q
            ";

            $sqlssjpp_10740 = "
                SELECT 
                q.hospcode
                ,q.hosname
                ,count(q.seq) as seq 
                ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10739' THEN q.claim ELSE 0 END),2) '10739'
                ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10740' THEN q.claim ELSE 0 END),2) '10740'
                ,TRUNCATE(sum(CASE WHEN q.FundHolderID not in('10739','10740') THEN q.claim ELSE 0 END),2) 'SSJ'
                ,TRUNCATE(sum(q.claim),2) as claim
                from(
                SELECT
                opdata.* 
                ,chospital2.hosname
                ,case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
                when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
                FROM opdata				   
                left join chospital2 on opdata.FundHolderID=chospital2.hospcode
                WHERE  opdata.hospcode = '10740'
                and op_pp_type = 'pp'
                and opdata.dateserv between ':startDate' and ':endDate'	
                -- and opdata.FundHolderID='10740'
                -- and opdata.FundHolderID='10739'
                -- and opdata.FundHolderID not in ('10739','10740')
                and opdata.FundHolderID <> 'ccccc'
                )as q                
            ";                    

            $sqlssjip_10740 = "
            SELECT
            q.hospcode,
            q.hosname,
            TRUNCATE(sum(CASE WHEN q.FundHolderID='10739' THEN q.costadjrw ELSE 0 END),2) '10739',
            TRUNCATE(sum(CASE WHEN q.FundHolderID='10740' THEN q.costadjrw ELSE 0 END),2) '10740',
            TRUNCATE(sum(CASE WHEN q.FundHolderID not in('10739','10740') THEN q.costadjrw ELSE 0 END),2) 'SSJ',
            TRUNCATE(sum(q.costadjrw),2) costadjrw
            from
            (
            SELECT
                ipdata.* ,chospital2.hosname
            FROM
                ipdata 
                left join chospital2 on chospital2.hospcode=ipdata.hospcode
            WHERE ipdata.hospcode='10740'
            and ipdata.FundHolderID<>'ccccc'	            
                    and ipdata.dischartdate between ':startDate' and ':endDate'	
            )as q
            
            ";                      
    #### 10740 ####

            #### 11347 ####                   
            $sqlssjop_11347 = "
            SELECT 
            q.hospcode
            ,q.hosname
            ,count(q.seq) as seq 
                    ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10739' THEN q.claim ELSE 0 END),2) '10739'
                    ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10740' THEN q.claim ELSE 0 END),2) '10740'
                    ,TRUNCATE(sum(CASE WHEN q.FundHolderID not in('10739','10740') THEN q.claim ELSE 0 END),2) 'SSJ'
            ,TRUNCATE(sum(q.claim),2) as claim
            from(
                SELECT
                opdata.* 
                ,chospital2.hosname
                ,case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
                            when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
                FROM
                opdata
                left join chospital2 on opdata.FundHolderID=chospital2.hospcode
                WHERE
                opdata.hospcode = '11347'
                -- and opdata.FundHolderID='10740'
                -- and opdata.FundHolderID='10739'
                -- and opdata.FundHolderID not in('10740','10739')
                and opdata.FundHolderID<>'ccccc'
                and opdata.dateserv between ':startDate' and ':endDate'
                and op_pp_type = 'op'
                ) as q

            ";

            $sqlssjpp_11347 = "
                SELECT 
                q.hospcode
                ,q.hosname
                ,count(q.seq) as seq 
                ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10739' THEN q.claim ELSE 0 END),2) '10739'
                ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10740' THEN q.claim ELSE 0 END),2) '10740'
                ,TRUNCATE(sum(CASE WHEN q.FundHolderID not in('10739','10740') THEN q.claim ELSE 0 END),2) 'SSJ'
                ,TRUNCATE(sum(q.claim),2) as claim
                from(
                SELECT
                opdata.* 
                ,chospital2.hosname
                ,case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
                when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
                FROM opdata				   
                left join chospital2 on opdata.FundHolderID=chospital2.hospcode
                WHERE  opdata.hospcode = '11347'
                and op_pp_type = 'pp'
                and opdata.dateserv between ':startDate' and ':endDate'	
                -- and opdata.FundHolderID='10740'
                -- and opdata.FundHolderID='10739'
                -- and opdata.FundHolderID not in ('10739','10740')
                and opdata.FundHolderID <> 'ccccc'
                )as q
            
    
            ";                    

            $sqlssjip_11347 = "
            SELECT
            q.hospcode,
            q.hosname,
            TRUNCATE(sum(CASE WHEN q.FundHolderID='10739' THEN q.costadjrw ELSE 0 END),2) '10739',
            TRUNCATE(sum(CASE WHEN q.FundHolderID='10740' THEN q.costadjrw ELSE 0 END),2) '10740',
            TRUNCATE(sum(CASE WHEN q.FundHolderID not in('10739','10740') THEN q.costadjrw ELSE 0 END),2) 'SSJ',
            TRUNCATE(sum(q.costadjrw),2) costadjrw
            from
            (
            SELECT
                ipdata.* ,chospital2.hosname
            FROM
                ipdata 
                left join chospital2 on chospital2.hospcode=ipdata.hospcode
            WHERE ipdata.hospcode='11347'
            and ipdata.FundHolderID<>'ccccc'	
            
                    and ipdata.dischartdate between ':startDate' and ':endDate'	
            )as q
            
            ";                      
    #### 11347 ####

            #### 11348 ####                   
            $sqlssjop_11348 = "
            SELECT 
            q.hospcode
            ,q.hosname
            ,count(q.seq) as seq 
                    ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10739' THEN q.claim ELSE 0 END),2) '10739'
                    ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10740' THEN q.claim ELSE 0 END),2) '10740'
                    ,TRUNCATE(sum(CASE WHEN q.FundHolderID not in('10739','10740') THEN q.claim ELSE 0 END),2) 'SSJ'
            ,TRUNCATE(sum(q.claim),2) as claim
            from(
                SELECT
                opdata.* 
                ,chospital2.hosname
                ,case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
                            when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
                FROM
                opdata
                left join chospital2 on opdata.FundHolderID=chospital2.hospcode
                WHERE
                opdata.hospcode = '11348'
                -- and opdata.FundHolderID='10740'
                -- and opdata.FundHolderID='10739'
                -- and opdata.FundHolderID not in('10740','10739')
                and opdata.FundHolderID<>'ccccc'
                and opdata.dateserv between ':startDate' and ':endDate'
                and op_pp_type = 'op'
                ) as q
            ";

            $sqlssjpp_11348 = "
                SELECT 
                q.hospcode
                ,q.hosname
                ,count(q.seq) as seq 
                ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10739' THEN q.claim ELSE 0 END),2) '10739'
                ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10740' THEN q.claim ELSE 0 END),2) '10740'
                ,TRUNCATE(sum(CASE WHEN q.FundHolderID not in('10739','10740') THEN q.claim ELSE 0 END),2) 'SSJ'
                ,TRUNCATE(sum(q.claim),2) as claim
                from(
                SELECT
                opdata.* 
                ,chospital2.hosname
                ,case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
                when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
                FROM opdata				   
                left join chospital2 on opdata.FundHolderID=chospital2.hospcode
                WHERE  opdata.hospcode = '11348'
                and op_pp_type = 'pp'
                and opdata.dateserv between ':startDate' and ':endDate'	
                -- and opdata.FundHolderID='10740'
                -- and opdata.FundHolderID='10739'
                -- and opdata.FundHolderID not in ('10739','10740')
                and opdata.FundHolderID <> 'ccccc'
                )as q                
            ";                    

            $sqlssjip_11348 = "
            SELECT
            q.hospcode,
            q.hosname,
            TRUNCATE(sum(CASE WHEN q.FundHolderID='10739' THEN q.costadjrw ELSE 0 END),2) '10739',
            TRUNCATE(sum(CASE WHEN q.FundHolderID='10740' THEN q.costadjrw ELSE 0 END),2) '10740',
            TRUNCATE(sum(CASE WHEN q.FundHolderID not in('10739','10740') THEN q.costadjrw ELSE 0 END),2) 'SSJ',
            TRUNCATE(sum(q.costadjrw),2) costadjrw
            from
            (
            SELECT
                ipdata.* ,chospital2.hosname
            FROM
                ipdata 
                left join chospital2 on chospital2.hospcode=ipdata.hospcode
            WHERE ipdata.hospcode='11348'
            and ipdata.FundHolderID<>'ccccc'	            
                    and ipdata.dischartdate between ':startDate' and ':endDate'	
            )as q
            
            ";                      
    #### 11348 ####

            #### 11349 ####                   
            $sqlssjop_11349 = "
            SELECT 
            q.hospcode
            ,q.hosname
            ,count(q.seq) as seq 
                    ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10739' THEN q.claim ELSE 0 END),2) '10739'
                    ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10740' THEN q.claim ELSE 0 END),2) '10740'
                    ,TRUNCATE(sum(CASE WHEN q.FundHolderID not in('10739','10740') THEN q.claim ELSE 0 END),2) 'SSJ'
            ,TRUNCATE(sum(q.claim),2) as claim
            from(
                SELECT
                opdata.* 
                ,chospital2.hosname
                ,case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
                            when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
                FROM
                opdata
                left join chospital2 on opdata.FundHolderID=chospital2.hospcode
                WHERE
                opdata.hospcode = '11349'
                -- and opdata.FundHolderID='10740'
                -- and opdata.FundHolderID='10739'
                -- and opdata.FundHolderID not in('10740','10739')
                and opdata.FundHolderID<>'ccccc'
                and opdata.dateserv between ':startDate' and ':endDate'
                and op_pp_type = 'op'
                ) as q

            ";

            $sqlssjpp_11349 = "
                SELECT 
                q.hospcode
                ,q.hosname
                ,count(q.seq) as seq 
                ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10739' THEN q.claim ELSE 0 END),2) '10739'
                ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10740' THEN q.claim ELSE 0 END),2) '10740'
                ,TRUNCATE(sum(CASE WHEN q.FundHolderID not in('10739','10740') THEN q.claim ELSE 0 END),2) 'SSJ'
                ,TRUNCATE(sum(q.claim),2) as claim
                from(
                SELECT
                opdata.* 
                ,chospital2.hosname
                ,case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
                when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
                FROM opdata				   
                left join chospital2 on opdata.FundHolderID=chospital2.hospcode
                WHERE  opdata.hospcode = '11349'
                and op_pp_type = 'pp'
                and opdata.dateserv between ':startDate' and ':endDate'	
                -- and opdata.FundHolderID='10740'
                -- and opdata.FundHolderID='10739'
                -- and opdata.FundHolderID not in ('10739','10740')
                and opdata.FundHolderID <> 'ccccc'
                )as q
            
    
            ";                    

            $sqlssjip_11349 = "
            SELECT
            q.hospcode,
            q.hosname,
            TRUNCATE(sum(CASE WHEN q.FundHolderID='10739' THEN q.costadjrw ELSE 0 END),2) '10739',
            TRUNCATE(sum(CASE WHEN q.FundHolderID='10740' THEN q.costadjrw ELSE 0 END),2) '10740',
            TRUNCATE(sum(CASE WHEN q.FundHolderID not in('10739','10740') THEN q.costadjrw ELSE 0 END),2) 'SSJ',
            TRUNCATE(sum(q.costadjrw),2) costadjrw
            from
            (
            SELECT
                ipdata.* ,chospital2.hosname
            FROM
                ipdata 
                left join chospital2 on chospital2.hospcode=ipdata.hospcode
            WHERE ipdata.hospcode='11349'
            and ipdata.FundHolderID<>'ccccc'	
            
                    and ipdata.dischartdate between ':startDate' and ':endDate'	
            )as q
            
            ";                      
    #### 11349 ####

            #### 11350 ####                   
            $sqlssjop_11350 = "
            SELECT 
            q.hospcode
            ,q.hosname
            ,count(q.seq) as seq 
                    ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10739' THEN q.claim ELSE 0 END),2) '10739'
                    ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10740' THEN q.claim ELSE 0 END),2) '10740'
                    ,TRUNCATE(sum(CASE WHEN q.FundHolderID not in('10739','10740') THEN q.claim ELSE 0 END),2) 'SSJ'
            ,TRUNCATE(sum(q.claim),2) as claim
            from(
                SELECT
                opdata.* 
                ,chospital2.hosname
                ,case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
                            when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
                FROM
                opdata
                left join chospital2 on opdata.FundHolderID=chospital2.hospcode
                WHERE
                opdata.hospcode = '11350'
                -- and opdata.FundHolderID='10740'
                -- and opdata.FundHolderID='10739'
                -- and opdata.FundHolderID not in('10740','10739')
                and opdata.FundHolderID<>'ccccc'
                and opdata.dateserv between ':startDate' and ':endDate'
                and op_pp_type = 'op'
                ) as q

            ";

            $sqlssjpp_11350 = "
                SELECT 
                q.hospcode
                ,q.hosname
                ,count(q.seq) as seq 
                ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10739' THEN q.claim ELSE 0 END),2) '10739'
                ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10740' THEN q.claim ELSE 0 END),2) '10740'
                ,TRUNCATE(sum(CASE WHEN q.FundHolderID not in('10739','10740') THEN q.claim ELSE 0 END),2) 'SSJ'
                ,TRUNCATE(sum(q.claim),2) as claim
                from(
                SELECT
                opdata.* 
                ,chospital2.hosname
                ,case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
                when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
                FROM opdata				   
                left join chospital2 on opdata.FundHolderID=chospital2.hospcode
                WHERE  opdata.hospcode = '11350'
                and op_pp_type = 'pp'
                and opdata.dateserv between ':startDate' and ':endDate'	
                -- and opdata.FundHolderID='10740'
                -- and opdata.FundHolderID='10739'
                -- and opdata.FundHolderID not in ('10739','10740')
                and opdata.FundHolderID <> 'ccccc'
                )as q
            
    
            ";                    

            $sqlssjip_11350 = "
            SELECT
            q.hospcode,
            q.hosname,
            TRUNCATE(sum(CASE WHEN q.FundHolderID='10739' THEN q.costadjrw ELSE 0 END),2) '10739',
            TRUNCATE(sum(CASE WHEN q.FundHolderID='10740' THEN q.costadjrw ELSE 0 END),2) '10740',
            TRUNCATE(sum(CASE WHEN q.FundHolderID not in('10739','10740') THEN q.costadjrw ELSE 0 END),2) 'SSJ',
            TRUNCATE(sum(q.costadjrw),2) costadjrw
            from
            (
            SELECT
                ipdata.* ,chospital2.hosname
            FROM
                ipdata 
                left join chospital2 on chospital2.hospcode=ipdata.hospcode
            WHERE ipdata.hospcode='11350'
            and ipdata.FundHolderID<>'ccccc'	
            
                    and ipdata.dischartdate between ':startDate' and ':endDate'	
            )as q
            
            ";                      
    #### 11350 ####                    

            #### 11352 ####                   
            $sqlssjop_11352 = "
            SELECT 
            q.hospcode
            ,q.hosname
            ,count(q.seq) as seq 
                    ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10739' THEN q.claim ELSE 0 END),2) '10739'
                    ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10740' THEN q.claim ELSE 0 END),2) '10740'
                    ,TRUNCATE(sum(CASE WHEN q.FundHolderID not in('10739','10740') THEN q.claim ELSE 0 END),2) 'SSJ'
            ,TRUNCATE(sum(q.claim),2) as claim
            from(
                SELECT
                opdata.* 
                ,chospital2.hosname
                ,case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
                            when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
                FROM
                opdata
                left join chospital2 on opdata.FundHolderID=chospital2.hospcode
                WHERE
                opdata.hospcode = '11352'
                -- and opdata.FundHolderID='10740'
                -- and opdata.FundHolderID='10739'
                -- and opdata.FundHolderID not in('10740','10739')
                and opdata.FundHolderID<>'ccccc'
                and opdata.dateserv between ':startDate' and ':endDate'
                and op_pp_type = 'op'
                ) as q
            ";

            $sqlssjpp_11352 = "
                SELECT 
                q.hospcode
                ,q.hosname
                ,count(q.seq) as seq 
                ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10739' THEN q.claim ELSE 0 END),2) '10739'
                ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10740' THEN q.claim ELSE 0 END),2) '10740'
                ,TRUNCATE(sum(CASE WHEN q.FundHolderID not in('10739','10740') THEN q.claim ELSE 0 END),2) 'SSJ'
                ,TRUNCATE(sum(q.claim),2) as claim
                from(
                SELECT
                opdata.* 
                ,chospital2.hosname
                ,case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
                when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
                FROM opdata				   
                left join chospital2 on opdata.FundHolderID=chospital2.hospcode
                WHERE  opdata.hospcode = '11352'
                and op_pp_type = 'pp'                
                and opdata.dateserv between ':startDate' and ':endDate'	
                -- and opdata.FundHolderID='10740'
                -- and opdata.FundHolderID='10739'
                -- and opdata.FundHolderID not in ('10739','10740')
                and opdata.FundHolderID <> 'ccccc'
                )as q                
            ";                    

            $sqlssjip_11352 = "
            SELECT
            q.hospcode,
            q.hosname,
            TRUNCATE(sum(CASE WHEN q.FundHolderID='10739' THEN q.costadjrw ELSE 0 END),2) '10739',
            TRUNCATE(sum(CASE WHEN q.FundHolderID='10740' THEN q.costadjrw ELSE 0 END),2) '10740',
            TRUNCATE(sum(CASE WHEN q.FundHolderID not in('10739','10740') THEN q.costadjrw ELSE 0 END),2) 'SSJ',
            TRUNCATE(sum(q.costadjrw),2) costadjrw
            from
            (
            SELECT
                ipdata.* ,chospital2.hosname
            FROM
                ipdata 
                left join chospital2 on chospital2.hospcode=ipdata.hospcode
            WHERE ipdata.hospcode='11352'
            and ipdata.FundHolderID<>'ccccc'	
            
                    and ipdata.dischartdate between ':startDate' and ':endDate'	
            )as q
            
            ";                      
    #### 11352 ####                    
            
            #### 11353 ####                   
                    $sqlssjop_11353 = "
                    SELECT 
                    q.hospcode
                    ,q.hosname
                    ,count(q.seq) as seq 
                            ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10739' THEN q.claim ELSE 0 END),2) '10739'
                            ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10740' THEN q.claim ELSE 0 END),2) '10740'
                            ,TRUNCATE(sum(CASE WHEN q.FundHolderID not in('10739','10740') THEN q.claim ELSE 0 END),2) 'SSJ'
                    ,TRUNCATE(sum(q.claim),2) as claim
                    from(
                        SELECT
                        opdata.* 
                        ,chospital2.hosname
                        ,case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
                                    when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
                        FROM
                        opdata
                        left join chospital2 on opdata.FundHolderID=chospital2.hospcode
                        WHERE
                        opdata.hospcode = '11353'
                        -- and opdata.FundHolderID='10740'
                        -- and opdata.FundHolderID='10739'
                        -- and opdata.FundHolderID not in('10740','10739')
                        and opdata.FundHolderID<>'ccccc'
                        and opdata.dateserv between ':startDate' and ':endDate'
                        and op_pp_type = 'op'
                        ) as q
    
                    ";

                    $sqlssjpp_11353 = "
                        SELECT 
                        q.hospcode
                        ,q.hosname
                        ,count(q.seq) as seq 
                        ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10739' THEN q.claim ELSE 0 END),2) '10739'
                        ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10740' THEN q.claim ELSE 0 END),2) '10740'
                        ,TRUNCATE(sum(CASE WHEN q.FundHolderID not in('10739','10740') THEN q.claim ELSE 0 END),2) 'SSJ'
                        ,TRUNCATE(sum(q.claim),2) as claim
                        from(
                        SELECT
                        opdata.* 
                        ,chospital2.hosname
                        ,case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
                        when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
                        FROM opdata				   
                        left join chospital2 on opdata.FundHolderID=chospital2.hospcode
                        WHERE  opdata.hospcode = '11353'
                        and op_pp_type = 'pp'
                        and opdata.dateserv between ':startDate' and ':endDate'	
                        -- and opdata.FundHolderID='10740'
                        -- and opdata.FundHolderID='10739'
                        -- and opdata.FundHolderID not in ('10739','10740')
                        and opdata.FundHolderID <> 'ccccc'
                        )as q
                    
            
                    ";                    

                    $sqlssjip_11353 = "
                    SELECT
                    q.hospcode,
                    q.hosname,
                    TRUNCATE(sum(CASE WHEN q.FundHolderID='10739' THEN q.costadjrw ELSE 0 END),2) '10739',
                    TRUNCATE(sum(CASE WHEN q.FundHolderID='10740' THEN q.costadjrw ELSE 0 END),2) '10740',
                    TRUNCATE(sum(CASE WHEN q.FundHolderID not in('10739','10740') THEN q.costadjrw ELSE 0 END),2) 'SSJ',
                    TRUNCATE(sum(q.costadjrw),2) costadjrw
                    from
                    (
                    SELECT
                        ipdata.* ,chospital2.hosname
                    FROM
                        ipdata 
                        left join chospital2 on chospital2.hospcode=ipdata.hospcode
                    WHERE ipdata.hospcode='11353'
                    and ipdata.FundHolderID<>'ccccc'	
                    
                            and ipdata.dischartdate between ':startDate' and ':endDate'	
                    )as q
                    
                    ";                      
            #### 11353 ####

            #### 11354 ####                   
            $sqlssjop_11354 = "
            SELECT 
            q.hospcode
            ,q.hosname
            ,count(q.seq) as seq 
                    ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10739' THEN q.claim ELSE 0 END),2) '10739'
                    ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10740' THEN q.claim ELSE 0 END),2) '10740'
                    ,TRUNCATE(sum(CASE WHEN q.FundHolderID not in('10739','10740') THEN q.claim ELSE 0 END),2) 'SSJ'
            ,TRUNCATE(sum(q.claim),2) as claim
            from(
                SELECT
                opdata.* 
                ,chospital2.hosname
                ,case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
                            when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
                FROM
                opdata
                left join chospital2 on opdata.FundHolderID=chospital2.hospcode
                WHERE
                opdata.hospcode = '11354'
                -- and opdata.FundHolderID='10740'
                -- and opdata.FundHolderID='10739'
                -- and opdata.FundHolderID not in('10740','10739')
                and opdata.FundHolderID<>'ccccc'
                and opdata.dateserv between ':startDate' and ':endDate'
                and op_pp_type = 'op'
                ) as q

            ";

            $sqlssjpp_11354 = "
                SELECT 
                q.hospcode
                ,q.hosname
                ,count(q.seq) as seq 
                ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10739' THEN q.claim ELSE 0 END),2) '10739'
                ,TRUNCATE(sum(CASE WHEN q.FundHolderID='10740' THEN q.claim ELSE 0 END),2) '10740'
                ,TRUNCATE(sum(CASE WHEN q.FundHolderID not in('10739','10740') THEN q.claim ELSE 0 END),2) 'SSJ'
                ,TRUNCATE(sum(q.claim),2) as claim
                from(
                SELECT
                opdata.* 
                ,chospital2.hosname
                ,case when(CAST(opdata.alltotal as DECIMAL(20,2)) = 700 or CAST(opdata.alltotal as DECIMAL(20,2)) > 700) then 700
                when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
                FROM opdata				   
                left join chospital2 on opdata.FundHolderID=chospital2.hospcode
                WHERE  opdata.hospcode = '11354'
                and op_pp_type = 'pp'
                and opdata.dateserv between ':startDate' and ':endDate'	
                -- and opdata.FundHolderID='10740'
                -- and opdata.FundHolderID='10739'
                -- and opdata.FundHolderID not in ('10739','10740')
                and opdata.FundHolderID <> 'ccccc'
                )as q
            
    
            ";                    

            $sqlssjip_11354 = "
            SELECT
            q.hospcode,
            q.hosname,
            TRUNCATE(sum(CASE WHEN q.FundHolderID='10739' THEN q.costadjrw ELSE 0 END),2) '10739',
            TRUNCATE(sum(CASE WHEN q.FundHolderID='10740' THEN q.costadjrw ELSE 0 END),2) '10740',
            TRUNCATE(sum(CASE WHEN q.FundHolderID not in('10739','10740') THEN q.costadjrw ELSE 0 END),2) 'SSJ',
            TRUNCATE(sum(q.costadjrw),2) costadjrw
            from
            (
            SELECT
                ipdata.* ,chospital2.hosname
            FROM
                ipdata 
                left join chospital2 on chospital2.hospcode=ipdata.hospcode
            WHERE ipdata.hospcode='11354'
            and ipdata.FundHolderID<>'ccccc'	
            
                    and ipdata.dischartdate between ':startDate' and ':endDate'	
            )as q
            
            ";                      
    #### 11354 ####

#### รพสต ####
    $sqlssjsubhos = "
     SELECT
        q.hospcode,
        q.hosname,
        q.cupcode,
        q.cup,
        q.seq,
        TRUNCATE(SUM(CASE WHEN q.FundHolderID = '10739'
            AND q.op_pp_type = 'op'
        THEN q.claim ELSE 0 END), 2) AS op10739,
        TRUNCATE(SUM(CASE WHEN q.FundHolderID = '10739'
            AND q.op_pp_type = 'pp'
        THEN q.claim ELSE 0 END), 2) AS pp10739,
        TRUNCATE(SUM(CASE WHEN q.FundHolderID = '10740'
            AND q.op_pp_type = 'op'
        THEN q.claim ELSE 0 END), 2) AS op10740,
        TRUNCATE(SUM(CASE WHEN q.FundHolderID = '10740'
            AND q.op_pp_type = 'pp'
        THEN q.claim ELSE 0 END), 2) AS pp10740,
        TRUNCATE(SUM(CASE WHEN q.FundHolderID NOT IN ('10739', '10740')
            AND q.op_pp_type = 'op'
        THEN q.claim ELSE 0 END), 2) AS opSSJ,
        TRUNCATE(SUM(CASE WHEN q.FundHolderID NOT IN ('10739', '10740')
            AND q.op_pp_type = 'pp'
        THEN q.claim ELSE 0 END), 2) AS ppSSJ,
        TRUNCATE(SUM(q.claim), 2) AS claim
    FROM (
    SELECT
        opdata.*,
        chospital2.hosname,
        cup.cupcode AS cupcode,
        cup.hosname AS cup,
        CASE 
            WHEN (CAST(opdata.alltotal AS DECIMAL(20,2)) = 700 OR CAST(opdata.alltotal AS DECIMAL(20,2)) > 700) THEN 700
            WHEN (CAST(opdata.alltotal AS DECIMAL(20,2)) < 700) THEN opdata.alltotal 
            ELSE NULL 
        END AS claim
    FROM opdata
    inner JOIN chospital2 ON opdata.hospcode = chospital2.hospcode
    inner JOIN chospital2 cup ON cup.hospcode = chospital2.cupcode
    WHERE 
    -- opdata.dateserv BETWEEN '2025-10-01' AND '2026-09-30'
    opdata.dateserv between  ':startDate' and ':endDate'
      AND opdata.hospcode NOT IN (
          SELECT hospcode 
          FROM chospital2 
          WHERE hospcode IN (
              SELECT DISTINCT cupcode 
              FROM chospital2 
              WHERE provcode = '82'
          )
      )
    --   and opdata.hospcode = '09059'
    --   and opdata.op_pp_type = 'op'
      AND opdata.FundHolderID <> 'ccccc'
      ) AS q
GROUP BY q.hospcode, q.cupcode
ORDER BY q.cupcode, q.hospcode				            
    ";                      
#### รพสต ####

            $sqlhosmain="
            SELECT 
            DISTINCT
            q.cupcode hospcode
            ,q.cup hosname
    from(
    SELECT
                cup.cupcode cupcode
                ,cup.hosname cup

    FROM opdata				   
    inner join chospital2 on opdata.hospcode=chospital2.hospcode
            inner join chospital2 cup on cup.hospcode=chospital2.cupcode
    WHERE  
                opdata.dateserv between ':startDate' and ':endDate'

                and opdata.hospcode not in(
                SELECT hospcode FROM chospital2 WHERE hospcode in(
                SELECT DISTINCT cupcode FROM chospital2 WHERE provcode='82'
                )
                )

    and opdata.FundHolderID <> 'ccccc'
    )as q

            GROUP BY q.cupcode

            ORDER BY q.cupcode
     
            ";


          

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
                          //$date2=date('Y-m-d');              
            
                          $dq1=$yg.'-10-01';
                          $date1=543+$yg.'-10-01';
                          //$dq1=วันที่เริ่มต้น
            
                          $dq2=$yg1.'-09-30';
                          $date2=543+$yg1.'-09-30';
                          //$dq2=วันที่สิ้นสุด   
                        }

                        $sqlssjop_11354 = $this->bindDates($sqlssjop_11354, $dq1, $dq2);

                        $sqlssjpp_11354 = $this->bindDates($sqlssjpp_11354, $dq1, $dq2);
                        
                        $sqlssjip_11354 = $this->bindDates($sqlssjip_11354, $dq1, $dq2);

                        $sqlssjop_10739 = $this->bindDates($sqlssjop_10739, $dq1, $dq2);

                        $sqlssjpp_10739 = $this->bindDates($sqlssjpp_10739, $dq1, $dq2);
                        
                        $sqlssjip_10739 = $this->bindDates($sqlssjip_10739, $dq1, $dq2);

                        $sqlssjop_10740 = $this->bindDates($sqlssjop_10740, $dq1, $dq2);

                        $sqlssjpp_10740 = $this->bindDates($sqlssjpp_10740, $dq1, $dq2);
                        
                        $sqlssjip_10740 = $this->bindDates($sqlssjip_10740, $dq1, $dq2);

                        $sqlssjop_11347 = $this->bindDates($sqlssjop_11347, $dq1, $dq2);

                        $sqlssjpp_11347 = $this->bindDates($sqlssjpp_11347, $dq1, $dq2);
                        
                        $sqlssjip_11347 = $this->bindDates($sqlssjip_11347, $dq1, $dq2);

                        $sqlssjop_11348 = $this->bindDates($sqlssjop_11348, $dq1, $dq2);

                        $sqlssjpp_11348 = $this->bindDates($sqlssjpp_11348, $dq1, $dq2);
                        
                        $sqlssjip_11348 = $this->bindDates($sqlssjip_11348, $dq1, $dq2);

                        $sqlssjop_11349 = $this->bindDates($sqlssjop_11349, $dq1, $dq2);

                        $sqlssjpp_11349 = $this->bindDates($sqlssjpp_11349, $dq1, $dq2);
                        
                        $sqlssjip_11349 = $this->bindDates($sqlssjip_11349, $dq1, $dq2);

                        $sqlssjop_11350 = $this->bindDates($sqlssjop_11350, $dq1, $dq2);

                        $sqlssjpp_11350 = $this->bindDates($sqlssjpp_11350, $dq1, $dq2);
                        
                        $sqlssjip_11350 = $this->bindDates($sqlssjip_11350, $dq1, $dq2);

                        $sqlssjop_11352 = $this->bindDates($sqlssjop_11352, $dq1, $dq2);

                        $sqlssjpp_11352 = $this->bindDates($sqlssjpp_11352, $dq1, $dq2);
                        
                        $sqlssjip_11352 = $this->bindDates($sqlssjip_11352, $dq1, $dq2);
                        
                        $sqlssjop_11353 = $this->bindDates($sqlssjop_11353, $dq1, $dq2);

                        $sqlssjpp_11353 = $this->bindDates($sqlssjpp_11353, $dq1, $dq2);
                        
                        $sqlssjip_11353 = $this->bindDates($sqlssjip_11353, $dq1, $dq2);
                        
                        $sqlssjsubhos = $this->bindDates($sqlssjsubhos, $dq1, $dq2);

                        $sqlhosmain = $this->bindDates($sqlhosmain, $dq1, $dq2);



                        $queryssjop_11354 = Yii::$app->db->createCommand($sqlssjop_11354)->queryAll();
                        $queryssjpp_11354 = Yii::$app->db->createCommand($sqlssjpp_11354)->queryAll();
                        $queryssjip_11354 = Yii::$app->db->createCommand($sqlssjip_11354)->queryAll();

                        $queryssjop_10739 = Yii::$app->db->createCommand($sqlssjop_10739)->queryAll();
                        $queryssjpp_10739 = Yii::$app->db->createCommand($sqlssjpp_10739)->queryAll();
                        $queryssjip_10739 = Yii::$app->db->createCommand($sqlssjip_10739)->queryAll();

                        $queryssjop_10740 = Yii::$app->db->createCommand($sqlssjop_10740)->queryAll();
                        $queryssjpp_10740 = Yii::$app->db->createCommand($sqlssjpp_10740)->queryAll();
                        $queryssjip_10740 = Yii::$app->db->createCommand($sqlssjip_10740)->queryAll();

                        $queryssjop_11347 = Yii::$app->db->createCommand($sqlssjop_11347)->queryAll();
                        $queryssjpp_11347 = Yii::$app->db->createCommand($sqlssjpp_11347)->queryAll();
                        $queryssjip_11347 = Yii::$app->db->createCommand($sqlssjip_11347)->queryAll();

                        $queryssjop_11348 = Yii::$app->db->createCommand($sqlssjop_11348)->queryAll();
                        $queryssjpp_11348 = Yii::$app->db->createCommand($sqlssjpp_11348)->queryAll();
                        $queryssjip_11348 = Yii::$app->db->createCommand($sqlssjip_11348)->queryAll();

                        $queryssjop_11349 = Yii::$app->db->createCommand($sqlssjop_11349)->queryAll();
                        $queryssjpp_11349 = Yii::$app->db->createCommand($sqlssjpp_11349)->queryAll();
                        $queryssjip_11349 = Yii::$app->db->createCommand($sqlssjip_11349)->queryAll();

                        $queryssjop_11350 = Yii::$app->db->createCommand($sqlssjop_11350)->queryAll();
                        $queryssjpp_11350 = Yii::$app->db->createCommand($sqlssjpp_11350)->queryAll();
                        $queryssjip_11350 = Yii::$app->db->createCommand($sqlssjip_11350)->queryAll();

                        $queryssjop_11352 = Yii::$app->db->createCommand($sqlssjop_11352)->queryAll();
                        $queryssjpp_11352 = Yii::$app->db->createCommand($sqlssjpp_11352)->queryAll();
                        $queryssjip_11352 = Yii::$app->db->createCommand($sqlssjip_11352)->queryAll();

                        $queryssjop_11353 = Yii::$app->db->createCommand($sqlssjop_11353)->queryAll();
                        $queryssjpp_11353 = Yii::$app->db->createCommand($sqlssjpp_11353)->queryAll();
                        $queryssjip_11353 = Yii::$app->db->createCommand($sqlssjip_11353)->queryAll();

                        $queryssjsubhos = Yii::$app->db->createCommand($sqlssjsubhos)->queryAll();
                        $queryhosmain = Yii::$app->db->createCommand($sqlhosmain)->queryAll();



                    return $this->render('index', [   
                        'queryssjop_10739' => $queryssjop_10739,
                        'queryssjpp_10739' => $queryssjpp_10739,
                        'queryssjip_10739' => $queryssjip_10739,    

                        'queryssjop_10740' => $queryssjop_10740,
                        'queryssjpp_10740' => $queryssjpp_10740,
                        'queryssjip_10740' => $queryssjip_10740, 

                        'queryssjop_11347' => $queryssjop_11347,
                        'queryssjpp_11347' => $queryssjpp_11347,
                        'queryssjip_11347' => $queryssjip_11347, 

                        'queryssjop_11348' => $queryssjop_11348,
                        'queryssjpp_11348' => $queryssjpp_11348,
                        'queryssjip_11348' => $queryssjip_11348,    

                        'queryssjop_11349' => $queryssjop_11349,
                        'queryssjpp_11349' => $queryssjpp_11349,
                        'queryssjip_11349' => $queryssjip_11349, 

                        'queryssjop_11350' => $queryssjop_11350,
                        'queryssjpp_11350' => $queryssjpp_11350,
                        'queryssjip_11350' => $queryssjip_11350, 

                        'queryssjop_11352' => $queryssjop_11352,
                        'queryssjpp_11352' => $queryssjpp_11352,
                        'queryssjip_11352' => $queryssjip_11352,    

                        'queryssjop_11353' => $queryssjop_11353,
                        'queryssjpp_11353' => $queryssjpp_11353,
                        'queryssjip_11353' => $queryssjip_11353, 

                        'queryssjop_11354' => $queryssjop_11354,
                        'queryssjpp_11354' => $queryssjpp_11354,
                        'queryssjip_11354' => $queryssjip_11354, 

                        'queryssjsubhos' => $queryssjsubhos,
                        'queryhosmain' => $queryhosmain,
                        'sqlssjsubhos' => $sqlssjsubhos,

                        'date1' => $date1,
                        'date2' => $date2,

                        'back' => $back,
                        'forward' => $forward,
                        'peengob' => $peengob,
            ]);

                }// if($hoscode=='00064'){

    }
     
    /**
     * Logs in a user (secure + backward-compatible).
     *
     * @return mixed
     */
    public function actionLogin()
    {
        Yii::$app->response->headers->set('Cache-Control', 'no-store, private');
        $session = Yii::$app->session;

        // Already logged in via custom session
        if (!empty($session['user'])) {
            $check = User::findIdentity($session->get('userid'));
            if ($check && (string)$check->user === (string)$session['user']) {
                return $this->goHome();
            }
            // stale session — destroy it
            $session->destroy();
        }

        $model = new LoginForm();
        $post = Yii::$app->request->post('LoginForm');

        $username = trim($post['username'] ?? '');
        $password = $post['password'] ?? '';

        if ($username !== '' && $password !== '') {
            // Brute-force: 5 failed attempts in 5 minutes = lockout
            $cacheKey = 'login_fail:' . Yii::$app->request->userIP;
            $fails    = Yii::$app->cache->get($cacheKey) ?: 0;
            if ($fails >= 5) {
                Yii::$app->session->setFlash('error', 'พยายามเข้าสู่ระบบผิดเกินกำหนด กรุณารอ 5 นาทีแล้วลองใหม่');
                return $this->render('login', ['model' => $model]);
            }

            /** @var User|null $loginuser */
            $loginuser = User::findByUsername($username);

            if ($loginuser && $loginuser->validatePassword($password)) {
                // Reset fail counter
                Yii::$app->cache->delete($cacheKey);

                if ($loginuser->approval_status !== User::APPROVAL_APPROVED) {
                    LoginHistory::record(
                        $loginuser->user,
                        $loginuser->hoscode,
                        'failed',
                        $loginuser->approval_status === User::APPROVAL_PENDING ? 'approval_pending' : 'account_not_approved'
                    );
                    Yii::$app->session->setFlash(
                        'error',
                        $loginuser->approval_status === User::APPROVAL_PENDING
                            ? 'บัญชีของท่านอยู่ระหว่างรออนุมัติจาก สสจ.'
                            : 'บัญชีนี้ยังไม่ได้รับอนุมัติ กรุณาติดต่อผู้ดูแลระบบ'
                    );
                    return $this->render('login', ['model' => $model]);
                }

                // Open and regenerate session to prevent session fixation
                $session->open();
                $session->regenerateID(true);
                if (!Yii::$app->user->login($loginuser, 0)) {
                    Yii::error('ไม่สามารถสร้างสถานะการเข้าสู่ระบบสำหรับ userid=' . $loginuser->userid, __METHOD__);
                    throw new \yii\web\ForbiddenHttpException('ไม่สามารถเข้าสู่ระบบได้ กรุณาลองอีกครั้ง');
                }
                try {
                    Yii::$app->facilityAccess->requireAnyRole(['provincialAdmin', 'userManager', 'hospitalStaff', 'pcuStaff']);
                    Yii::$app->facilityAccess->requirePermission('accessApplication');
                    Yii::$app->facilityAccess->requireFacilityRoleMatches();
                } catch (\yii\web\ForbiddenHttpException $exception) {
                    Yii::$app->user->logout(false);
                    Yii::$app->session->setFlash('error', 'บัญชีนี้ยังไม่ได้รับการกำหนดสิทธิ์ กรุณาติดต่อผู้ดูแลระบบ');
                    return $this->render('login', ['model' => $model]);
                }
                $session['user']       = $loginuser->user;
                $session['fname']      = $loginuser->fname;
                $session['lname']      = $loginuser->lname;
                // ปรับ orgname ดึงข้อมูลจากตาราง chospital2 
                $hosname = (new \yii\db\Query())
                    ->select('hosname')
                    ->from('chospital2')
                    ->where(['hospcode' => $loginuser->hoscode])
                    ->scalar();                    
                $session['orgname']    = $hosname;
                $session['fullname']   = trim(($loginuser->fname ?? '') . ' ' . ($loginuser->lname ?? '')) ?: (string)$hosname;
                $session['hoscode']    = $loginuser->hoscode;
                $session['userid']     = $loginuser->userid;
                $session['login_time'] = time();   // for 4-hour expiry check

                // Record successful login in history
                LoginHistory::record($loginuser->user, $loginuser->hoscode, 'success');

                // Force password change if flagged
                if ($loginuser->must_change_password) {
                    return $this->redirect(['/site/change-password']);
                }

                return $this->redirect(['/site/index']);
            }

            // Increment fail counter (5 min TTL) & record failure
            Yii::$app->cache->set($cacheKey, $fails + 1, 300);
            LoginHistory::record($username, null, 'failed', 'invalid_credentials');
            Yii::$app->session->setFlash('error', 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง');
        }

        return $this->render('login', ['model' => $model]);
    }

    /**
     * Forced / voluntary password change.
     */
    public function actionChangePassword()
    {
        $session = Yii::$app->session;
        $username = $session->get('user');

        // Must be logged in
        if (empty($username)) {
            return $this->redirect(['/site/login']);
        }

        $user = \frontend\models\User::findByUsername($username);
        if (!$user) {
            return $this->redirect(['/site/login']);
        }

        $error = null;

        if (Yii::$app->request->isPost) {
            $newPassword    = Yii::$app->request->post('new_password', '');
            $confirmPassword = Yii::$app->request->post('confirm_password', '');

            // Validation
            if (strlen($newPassword) < 8) {
                $error = 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร';
            } elseif (!preg_match('/[A-Z]/', $newPassword)) {
                $error = 'รหัสผ่านต้องมีตัวพิมพ์ใหญ่อย่างน้อย 1 ตัว';
            } elseif (!preg_match('/[a-z]/', $newPassword)) {
                $error = 'รหัสผ่านต้องมีตัวพิมพ์เล็กอย่างน้อย 1 ตัว';
            } elseif (!preg_match('/[0-9]/', $newPassword)) {
                $error = 'รหัสผ่านต้องมีตัวเลขอย่างน้อย 1 ตัว';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน';
            } else {
                // Save: hash directly (no prefix) so the user logs in with exactly this password
                $user->pass               = Yii::$app->security->generatePasswordHash($newPassword);
                $user->must_change_password = 0;
                $user->save(false, ['pass', 'must_change_password']);

                // Sync session flag
                $session->set('must_change_password', 0);

                Yii::$app->session->setFlash('success', 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว');
                return $this->redirect(['/site/index']);
            }
        }

        return $this->render('change-password', ['error' => $error]);
    }

    /**
     * Logs out the current user securely.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        $session = Yii::$app->session;
        $currentUser = $session->get('user');
        if (!empty($currentUser)) {
            LoginHistory::recordLogout($currentUser);
        }
        Yii::$app->user->logout(false);
        $session->destroy();
        return $this->redirect(['/site/login']);
    }

    /**
     * Shows the current user's recent login history (last 50 entries).
     */
    public function actionLoginHistory()
    {
        $session = Yii::$app->session;
        $currentUser = $session->get('user');

        if (empty($currentUser)) {
            return $this->redirect(['/site/login']);
        }

        $records = LoginHistory::find()
            ->where(['user' => $currentUser])
            ->orderBy(['login_at' => SORT_DESC])
            ->limit(50)
            ->all();

        return $this->render('login-history', [
            'records' => $records,
        ]);
    }

    /**
     * แสดง / บันทึกข้อมูลส่วนตัวของผู้ใช้ที่ล็อกอินอยู่
     */
    public function actionProfile()
    {
        $session = Yii::$app->session;
        $currentUser = $session->get('user');

        $user = \frontend\models\User::findOne(['user' => $currentUser]);
        if (!$user) {
            return $this->redirect(['/site/login']);
        }

        $facilityName = Yii::$app->facilityAccess->facilityName((string)$user->hoscode);
        $user->orgname = $facilityName ?? '';
        $error   = null;
        $success = null;

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();

            $user->fname       = trim($post['fname']       ?? '');
            $user->lname       = trim($post['lname']       ?? '');
            $user->orgname     = $facilityName ?? '';
            $user->phonenumber = trim($post['phonenumber'] ?? '');
            $user->email       = trim($post['email']       ?? '');

            // validate only these fields
            if ($facilityName === null) {
                $error = 'ไม่พบชื่อหน่วยบริการจากรหัสสถานพยาบาล กรุณาติดต่อผู้ดูแลระบบ';
            } elseif ($user->validate(['fname', 'lname', 'phonenumber', 'email'])) {
                $user->save(false, ['fname', 'lname', 'orgname', 'phonenumber', 'email']);

                // sync session
                $session['fname']    = $user->fname;
                $session['lname']    = $user->lname;
                $session['orgname']  = $user->orgname;
                $session['fullname'] = trim($user->fname . ' ' . $user->lname) ?: $user->orgname;

                $success = 'บันทึกข้อมูลเรียบร้อยแล้ว';
            } else {
                $error = implode('<br>', \yii\helpers\ArrayHelper::merge(...array_values($user->errors)));
            }
        }

        return $this->render('profile', [
            'user'    => $user,
            'error'   => $error,
            'success' => $success,
            'facilityName' => $facilityName,
        ]);
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup($token = null)
    {
        Yii::$app->response->headers->set('Cache-Control', 'no-store, private');
        Yii::$app->response->headers->set('Referrer-Policy', 'no-referrer');
        if (!is_string($token) || !RegistrationWindow::isOpen(RegistrationWindow::current(), $token)) {
            throw new NotFoundHttpException('ลิงก์สมัครไม่ถูกต้องหรือหมดเวลารับสมัครแล้ว');
        }
        $model = new SignupForm();
        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $window = RegistrationWindow::current(true);
                if (!RegistrationWindow::isOpen($window, $token)) {
                    throw new NotFoundHttpException('ลิงก์สมัครไม่ถูกต้องหรือหมดเวลารับสมัครแล้ว');
                }
                $user = $model->signup();
                if ($user && !RegistrationWindow::isOpen($window, $token)) {
                    throw new NotFoundHttpException('หมดเวลารับสมัครแล้ว กรุณาติดต่อผู้ดูแลระบบ');
                }
                $transaction->commit();
                if ($user) {
                    Yii::$app->session->setFlash('success', 'สมัครบัญชีสำเร็จ บัญชีของท่านจะเข้าใช้งานได้หลัง สสจ. อนุมัติ');
                    return $this->redirect(['/site/login']);
                }
            } catch (\Throwable $exception) {
                if ($transaction->isActive) {
                    $transaction->rollBack();
                }
                throw $exception;
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }
    // =========================================================
    //  DASHBOARD
    // =========================================================
    public function actionDashboard()
    {
        $session = Yii::$app->session;
        $hoscode = $session['hoscode'];
        $facilityCodesSql = $this->facilityCodesSql();
        $hospitalFacilityCodesSql = $this->hospitalFacilityCodesSql();

        $icd10Codes = ['Z34','Z13','Z35','Z30','Z31','Z32','Z33','Z39',
                       'Z364','Z37','Z21','Z23.2','Z240','Z244','Z245',
                       'Z27.1','Z246','Z251','Z274','Z273','Z11','Z10',
                       'Z298','Z001','Z115','U119'];
        $icd10CondOp = implode(' or ', array_map(fn($c) => "opdata.icd10 LIKE '%{$c}%'", $icd10Codes));

        $data    = Yii::$app->request->get();
        $date1   = $data['date1'] ?? '';
        $date2   = $data['date2'] ?? '';
        $back    = $data['back'] ?? '';
        $forward = $data['forward'] ?? '';
        $peengob = $data['peengob'] ?? '';

        if (!empty($forward)) { $back = ''; }
        if (!empty($back))    { $forward = ''; }

        // ── Date resolution ──────────────────────────────────────
        if (!empty($date1) && !empty($date2)) {
            $yt1  = (int)substr($date1,0,4) - 543;
            $m1   = substr($date1,5,2);
            $d1   = substr($date1,8,2);
            $dq1  = "$yt1-$m1-$d1";
            $date1 = (543+$yt1)."-$m1-$d1";

            $yt2  = (int)substr($date2,0,4) - 543;
            $m2   = substr($date2,5,2);
            $d2   = substr($date2,8,2);
            $dq2  = "$yt2-$m2-$d2";
            $date2 = (543+$yt2)."-$m2-$d2";
        } else {
            $datenow = date('Y-m-d');
            $yt  = (int)substr($datenow,0,4);
            $m1  = (int)substr($datenow,5,2);
            $yg  = $m1 > 9 ? $yt   : $yt - 1;
            $yg1 = $m1 > 9 ? $yt+1 : $yt;
            $dq1   = "$yg-10-01";
            $date1 = (543+$yg)."-10-01";
            $dq2   = "$yg1-09-30";
            $date2 = (543+$yg1)."-09-30";
        }

        // =========================================================
        //  Non-SSJ branch  (individual hospital)
        // =========================================================
        if (!$this->isProvincialAdmin()) {

            // ── IP aggregate queries ──────────────────────────────
            $sqlipall = "
            SELECT hospcode as hos
            ,COUNT(distinct an) as an
            ,sum(los) as los
            ,sum(cost) as cost
            ,TRUNCATE(sum(adjrw),4) as adjrw
            ,TRUNCATE(sum(costadjrw),2) as costadjrw
            from ipdata
            where dischartdate between ':startDate' and ':endDate'
            and hospcode IN ($facilityCodesSql)
            ";

            $sqlip_a = "
            SELECT q.hospcode hospcode
            ,COUNT(q.an) an ,sum(q.los) los ,sum(q.cost) cost
            ,TRUNCATE(sum(adjrw),4) adjrw ,TRUNCATE(sum(q.costadjrw),2) costadjrw
            from (
            SELECT * FROM ipdata
            WHERE hospcode IN ($hospitalFacilityCodesSql)
            and dischartdate between ':startDate' and ':endDate'
            and FundHolderID <> 'ccccc'
            ) as q
            ";

            $sqlip_c = "
            SELECT q.hospcode hospcode
            ,COUNT(q.an) an ,sum(q.los) los ,sum(q.cost) cost
            ,TRUNCATE(sum(adjrw),4) adjrw ,TRUNCATE(sum(q.costadjrw),2) costadjrw
            from (
            SELECT * FROM ipdata
            WHERE hospcode IN ($hospitalFacilityCodesSql)
            and dischartdate between ':startDate' and ':endDate'
            and FundHolderID = 'ccccc'
            ) as q
            ";

            $sqlip_ssj = "
            SELECT count(q.an) an,sum(q.los) los,sum(q.cost) cost
            ,TRUNCATE(sum(q.adjrw),4) adjrw,sum(q.costadjrw) costadjrw
            from( SELECT ipdata.ipid,ipdata.hospcode,ipdata.admitdate,ipdata.dischartdate,
            ipdata.fund,ipdata.cid,ipdata.hn,ipdata.an,ipdata.icd10,ipdata.icd9,
            cast(ipdata.los as int) los,ipdata.cost cost,ipdata.adjrw,
            TRUNCATE(ipdata.adjrw*8600,2) as costadjrw,ipdata.FundHolderID
            FROM ipdata
            WHERE ipdata.hospcode IN ($facilityCodesSql)
            and ipdata.FundHolderID not in('10739','10740')
            and ipdata.FundHolderID<>'ccccc'
            and ipdata.dischartdate between ':startDate' and ':endDate' )as q
            ";

            $sqlip_png = "
            SELECT count(q.an) an,sum(q.los) los,sum(q.cost) cost
            ,TRUNCATE(sum(q.adjrw),4) adjrw,sum(q.costadjrw) costadjrw
            from( SELECT ipdata.ipid,ipdata.hospcode,ipdata.admitdate,ipdata.dischartdate,
            ipdata.fund,ipdata.cid,ipdata.hn,ipdata.an,ipdata.icd10,ipdata.icd9,
            cast(ipdata.los as int) los,ipdata.cost cost,ipdata.adjrw,
            TRUNCATE(ipdata.adjrw*8600,2) as costadjrw,ipdata.FundHolderID
            FROM ipdata
            WHERE ipdata.hospcode IN ($facilityCodesSql)
            and ipdata.FundHolderID='10739'
            and ipdata.FundHolderID<>'ccccc'
            and ipdata.dischartdate between ':startDate' and ':endDate' )as q
            ";

            $sqlip_tkp = "
            SELECT count(q.an) an,sum(q.los) los,sum(q.cost) cost
            ,TRUNCATE(sum(q.adjrw),4) adjrw,sum(q.costadjrw) costadjrw
            from( SELECT ipdata.ipid,ipdata.hospcode,ipdata.admitdate,ipdata.dischartdate,
            ipdata.fund,ipdata.cid,ipdata.hn,ipdata.an,ipdata.icd10,ipdata.icd9,
            cast(ipdata.los as int) los,ipdata.cost cost,ipdata.adjrw,
            TRUNCATE(ipdata.adjrw*8600,2) as costadjrw,ipdata.FundHolderID
            FROM ipdata
            WHERE ipdata.hospcode IN ($facilityCodesSql)
            and ipdata.FundHolderID='10740'
            and ipdata.FundHolderID<>'ccccc'
            and ipdata.dischartdate between ':startDate' and ':endDate' )as q
            ";

            // ── PP aggregate queries ──────────────────────────────
            $sqlpp_all = "
            SELECT count(q.sq) as sq,sum(q.alltotal) as alltotal,sum(q.claim) as claim
            FROM(
            SELECT opdata.hospcode,opdata.hn,opdata.seq as sq,opdata.cid,opdata.alltotal,
            case when(CAST(opdata.alltotal as DECIMAL(20,2)) >= 700) then 700
            when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim,
            opdata.FundHolderID as fundHos
            FROM opdata
            WHERE hospcode IN ($hospitalFacilityCodesSql)
            and op_pp_type = 'op'            
            and opdata.dateserv between ':startDate' and ':endDate'
            ) as q
            ";

            $sqlpp_a = "
            SELECT count(q.sq) as sq,sum(q.alltotal) as alltotal,sum(q.claim) as claim
            FROM(
            SELECT opdata.hospcode,opdata.seq as sq,opdata.alltotal,
            case when(CAST(opdata.alltotal as DECIMAL(20,2)) >= 700) then 700
            when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
            FROM opdata
            WHERE hospcode IN ($hospitalFacilityCodesSql)
            and op_pp_type = 'pp'
            and opdata.FundHolderID <> 'ccccc'
            and opdata.dateserv between ':startDate' and ':endDate'
            ) as q
            ";

            $sqlpp_c = "
            SELECT count(q.sq) as sq,sum(q.alltotal) as alltotal,sum(q.claim) as claim
            FROM(
            SELECT DISTINCT opdata.hospcode,opdata.seq as sq,opdata.alltotal,
            case when(CAST(opdata.alltotal as DECIMAL(20,2)) >= 700) then 700
            when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
            FROM opdata
            WHERE hospcode IN ($hospitalFacilityCodesSql)
            and op_pp_type = 'pp'
            and opdata.dateserv between ':startDate' and ':endDate'
            and opdata.FundHolderID='ccccc'
            ) as q
            ";

            $sqlpp_ssj = "
            SELECT count(q.sq) sq,sum(q.alltotal) alltotal,sum(q.claim) claim
            from( SELECT DISTINCT opdata.hospcode,opdata.seq as sq,opdata.alltotal,
            case when(CAST(opdata.alltotal as DECIMAL(20,2)) >= 700) then 700
            when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim,
            opdata.FundHolderID as fundHos
            FROM opdata
            WHERE opdata.hospcode IN ($hospitalFacilityCodesSql)
            and op_pp_type = 'pp'
            and opdata.dateserv between ':startDate' and ':endDate'
            and opdata.FundHolderID not in ('10739','10740')
            and opdata.FundHolderID <> 'ccccc'
            )as q
            ";

            $sqlpp_png = "
            SELECT count(q.sq) sq,sum(q.alltotal) alltotal,sum(q.claim) claim
            from( SELECT DISTINCT opdata.hospcode,opdata.seq as sq,opdata.alltotal,
            case when(CAST(opdata.alltotal as DECIMAL(20,2)) >= 700) then 700
            when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
            FROM opdata
            WHERE opdata.hospcode IN ($hospitalFacilityCodesSql)
            and op_pp_type = 'pp'
            and opdata.dateserv between ':startDate' and ':endDate'
            and opdata.FundHolderID='10739'
            and opdata.FundHolderID <> 'ccccc'
            )as q
            ";

            $sqlpp_tkp = "
            SELECT count(q.sq) sq,sum(q.alltotal) alltotal,sum(q.claim) claim
            from( SELECT DISTINCT opdata.hospcode,opdata.seq as sq,opdata.alltotal,
            case when(CAST(opdata.alltotal as DECIMAL(20,2)) >= 700) then 700
            when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
            FROM opdata
            WHERE opdata.hospcode IN ($hospitalFacilityCodesSql)
            and op_pp_type = 'pp'
            and opdata.dateserv between ':startDate' and ':endDate'
            and opdata.FundHolderID='10740'
            and opdata.FundHolderID <> 'ccccc'
            )as q
            ";

            // ── OP aggregate queries ──────────────────────────────
            $sqlop_all = "
            SELECT count(q.sq) as sq,sum(q.alltotal) as alltotal,sum(q.claim) as claim
            FROM(
            SELECT DISTINCT opdata.hospcode,opdata.dateserv,opdata.hn,opdata.seq sq,
            opdata.cid,opdata.alltotal,
            case when(CAST(opdata.alltotal as DECIMAL(20,2)) >= 700) then 700
            when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
            FROM opdata
            WHERE opdata.hospcode IN ($hospitalFacilityCodesSql)
            and opdata.dateserv between ':startDate' and ':endDate'
            and op_pp_type = 'op'
            )as q
            ";

            $sqlop_a = "
            SELECT count(q.sq) as sq,sum(q.alltotal) as alltotal,sum(q.claim) as claim
            FROM(
            SELECT DISTINCT opdata.hospcode,opdata.dateserv,opdata.seq sq,opdata.alltotal,
            case when(CAST(opdata.alltotal as DECIMAL(20,2)) >= 700) then 700
            when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
            FROM opdata
            WHERE opdata.hospcode IN ($hospitalFacilityCodesSql)
            and opdata.FundHolderID <> 'ccccc'
            and opdata.dateserv between ':startDate' and ':endDate'
            and op_pp_type = 'op'
            )as q
            ";

            $sqlop_c = "
            SELECT count(q.sq) as sq,sum(q.alltotal) as alltotal,sum(q.claim) as claim
            FROM(
            SELECT DISTINCT opdata.hospcode,opdata.dateserv,opdata.seq sq,opdata.alltotal,
            case when(CAST(opdata.alltotal as DECIMAL(20,2)) >= 700) then 700
            when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
            FROM opdata
            WHERE opdata.hospcode IN ($hospitalFacilityCodesSql)
            and opdata.FundHolderID = 'ccccc'
            and opdata.dateserv between ':startDate' and ':endDate'
            and op_pp_type = 'op'
            )as q
            ";

            $sqlop_ssj = "
            SELECT count(q.sq) sq,sum(q.alltotal) alltotal,sum(q.claim) claim
            from(
            SELECT DISTINCT opdata.hospcode,opdata.dateserv,opdata.seq sq,opdata.alltotal,
            case when(CAST(opdata.alltotal as DECIMAL(20,2)) >= 700) then 700
            when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
            FROM opdata
            WHERE opdata.hospcode IN ($hospitalFacilityCodesSql)
            and opdata.FundHolderID not in('10740','10739')
            and opdata.FundHolderID<>'ccccc'
            and opdata.dateserv between ':startDate' and ':endDate'
            and op_pp_type = 'op'
            ) as q
            ";

            $sqlop_png = "
            SELECT count(q.sq) sq,sum(q.alltotal) alltotal,sum(q.claim) claim
            from(
            SELECT DISTINCT opdata.hospcode,opdata.dateserv,opdata.seq sq,opdata.alltotal,
            case when(CAST(opdata.alltotal as DECIMAL(20,2)) >= 700) then 700
            when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
            FROM opdata
            WHERE opdata.hospcode IN ($hospitalFacilityCodesSql)
            and opdata.FundHolderID='10739'
            and opdata.FundHolderID<>'ccccc'
            and opdata.dateserv between ':startDate' and ':endDate'
            and op_pp_type = 'op'
            ) as q
            ";

            $sqlop_tkp = "
            SELECT count(q.sq) sq,sum(q.alltotal) alltotal,sum(q.claim) claim
            from(
            SELECT DISTINCT opdata.hospcode,opdata.dateserv,opdata.seq sq,opdata.alltotal,
            case when(CAST(opdata.alltotal as DECIMAL(20,2)) >= 700) then 700
            when(CAST(opdata.alltotal as DECIMAL(20,2)) < 700) then opdata.alltotal else NULL end as claim
            FROM opdata
            WHERE opdata.hospcode IN ($hospitalFacilityCodesSql)
            and opdata.FundHolderID='10740'
            and opdata.FundHolderID<>'ccccc'
            and opdata.dateserv between ':startDate' and ':endDate'
            and op_pp_type = 'op'
            ) as q
            ";

            // ── Bind dates & run ──────────────────────────────────
            $queries = [
                'queryipall'  => $sqlipall,  'queryip_a'   => $sqlip_a,
                'queryip_c'   => $sqlip_c,   'queryip_ssj' => $sqlip_ssj,
                'queryip_png' => $sqlip_png, 'queryip_tkp' => $sqlip_tkp,
                'querypp_all' => $sqlpp_all, 'querypp_a'   => $sqlpp_a,
                'querypp_c'   => $sqlpp_c,   'querypp_ssj' => $sqlpp_ssj,
                'querypp_png' => $sqlpp_png, 'querypp_tkp' => $sqlpp_tkp,
                'queryop_all' => $sqlop_all, 'queryop_a'   => $sqlop_a,
                'queryop_c'   => $sqlop_c,   'queryop_ssj' => $sqlop_ssj,
                'queryop_png' => $sqlop_png, 'queryop_tkp' => $sqlop_tkp,
            ];
            $result = [];
            foreach ($queries as $key => $sql) {
                $result[$key] = Yii::$app->db->createCommand(
                    $this->bindDates($sql, $dq1, $dq2)
                )->queryAll();
            }

            return $this->render('dashboard', array_merge($result, [
                'date1' => $date1, 'date2' => $date2,
                'back' => $back, 'forward' => $forward, 'peengob' => $peengob,
            ]));
        }

        // =========================================================
        //  SSJ branch  (hoscode == '00064')
        // =========================================================

        // OP per hospital summary
        $sqlssj_op = "
        SELECT q.hospcode, q.hosname, count(q.seq) as seq, TRUNCATE(sum(q.claim),2) as claim
        from(
            SELECT DISTINCT opdata.hospcode, chospital2.hosname, opdata.seq,
            case when CAST(opdata.alltotal AS DECIMAL(20,2)) >= 700 then 700
                 when CAST(opdata.alltotal AS DECIMAL(20,2)) < 700  then opdata.alltotal
                 else NULL end as claim
            FROM opdata
            left join chospital2 on opdata.hospcode=chospital2.hospcode
            WHERE opdata.FundHolderID <> 'ccccc'
            and opdata.hospcode IN ($hospitalFacilityCodesSql)
            and opdata.dateserv between ':startDate' and ':endDate'
            and opdata.seq not in(
                SELECT DISTINCT opdata.seq FROM opdata
                WHERE ($icd10CondOp)
                and opdata.hospcode IN ($hospitalFacilityCodesSql)
                and opdata.dateserv between ':startDate' and ':endDate'
            )
        ) as q
        GROUP BY q.hospcode ORDER BY q.hospcode
        ";

        // PP per hospital summary
        $sqlssj_pp = "
        SELECT q.hospcode, q.hosname, count(q.seq) as seq, TRUNCATE(sum(q.claim),2) as claim
        from(
            SELECT DISTINCT opdata.hospcode, chospital2.hosname, opdata.seq,
            case when CAST(opdata.alltotal AS DECIMAL(20,2)) >= 700 then 700
                 when CAST(opdata.alltotal AS DECIMAL(20,2)) < 700  then opdata.alltotal
                 else NULL end as claim
            FROM opdata
            left join chospital2 on opdata.hospcode=chospital2.hospcode
            WHERE ($icd10CondOp)
            and opdata.FundHolderID <> 'ccccc'
            and opdata.hospcode IN ($hospitalFacilityCodesSql)
            and opdata.dateserv between ':startDate' and ':endDate'
        ) as q
        GROUP BY q.hospcode ORDER BY q.hospcode
        ";

        // IP per hospital summary
        $sqlssj_ip = "
        SELECT ipdata.hospcode, chospital2.hosname,
        COUNT(DISTINCT ipdata.an) as an,
        sum(ipdata.los) as los,
        TRUNCATE(sum(ipdata.adjrw),4) as adjrw,
        TRUNCATE(sum(ipdata.costadjrw),2) as costadjrw
        FROM ipdata
        left join chospital2 on ipdata.hospcode=chospital2.hospcode
        WHERE ipdata.dischartdate between ':startDate' and ':endDate'
        AND ipdata.FundHolderID <> 'ccccc'
        GROUP BY ipdata.hospcode ORDER BY ipdata.hospcode
        ";

        $queryssj_op = Yii::$app->db->createCommand($this->bindDates($sqlssj_op, $dq1, $dq2))->queryAll();
        $queryssj_pp = Yii::$app->db->createCommand($this->bindDates($sqlssj_pp, $dq1, $dq2))->queryAll();
        $queryssj_ip = Yii::$app->db->createCommand($this->bindDates($sqlssj_ip, $dq1, $dq2))->queryAll();

        return $this->render('dashboard', [
            'queryssj_op' => $queryssj_op,
            'queryssj_pp' => $queryssj_pp,
            'queryssj_ip' => $queryssj_ip,
            'date1' => $date1, 'date2' => $date2,
            'back' => $back, 'forward' => $forward, 'peengob' => $peengob,
        ]);
    }

    /** Replaces :startDate and :endDate placeholders in a SQL string. */
    private function bindDates(string $sql, string $dq1, string $dq2): string
    {
        return str_replace([':startDate', ':endDate'], [$dq1, $dq2], $sql);
    }
}