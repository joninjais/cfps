<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\bootstrap\Dropdown;
use frontend\models\User;
//ตรวจสอบ user ที่ login เข้ามา
$session = Yii::$app->session;
$checklogin = User::findOne(['user'=>$session['user'],]);

//ดึงชื่อโรงพยาบาลจากฐานข้อมูล

//ดึงชื่อโรงพยาบาลจากฐานข้อมูล
/* @var $this \yii\web\View */
/* @var $content string */
?>

<header class="main-header">

    <nav class="navbar navbar-static-top">
      <div class="container">
        <div class="navbar-header">
          <a href="<?= Url::to(['/site/index']) ?>" class="navbar-brand">
          <b><img src="<?= Url::to('@web/img/logoMOPH.png') ?>" width="30" height="30"/>  CF PS : Claim Foreigner work fund Phang-nga system</b></a>

        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse pull-left" id="navbar-collapse">
          <ul class="nav navbar-nav">
<!--
            <li class="active"><a href="#">Link <span class="sr-only">(current)</span></a></li>
            <li><a href="#">Link</a></li>
-->

        <?php /*
          echo Nav::widget([
            'options' => [
                'class' => 'navbar-nav navbar-left',
            ],
            'items' => [
                ['label' => 'ตัวชี้วัด QOF ระดับพื้นที่','url' => '#',
                  'items' => [
                        ['label' => '1. ร้อยละเด็กแรกเกิดน้ำหนักน้อยกว่า 2,500 กรัม (Low Birth Weight)', 'url' => 'index.php?r=qof62t1/index'],
                        ['label' => '2. ร้อยละของเด็กปฐมวัย (42 เดือน) มีสุขภาพแข็งแรงพัฒนาการสมวัย (Healthy Child)', 'url' => '#',
                        'items' => [
                              ['label' => '2.1 ร้อยละความครอบคลุมการคัดกรองพัฒนาการ ในเด็กปฐมวัย (42 เดือน)', 'url' => 'index.php?r=qof62t1/index'],
                              ['label' => '2.2 ร้อยละการตรวจพบสงสัยพัฒนาการล่าช้า ในเด็กปฐมวัย (42 เดือน)', 'url' => '#'],
                              ['label' => '2.3 ร้อยละความครอบคลุมของเด็กปฐมวัย (42 เดือน)ได้รับการชั่งน้ำหนัก วัดส่วนสูง', 'url' => '#'],                              
                          ],                      
                        ],
                        ['label' => '3. ร้อยละของเด็กวัยเรียน (ป.6) มีสุขภาพแข็งแรงสมวัย (Healthy School age)', 'url' => 'index.php?r=qof62t1/index',
                        'items' => [
                          ['label' => '3.1 ร้อยละความครอบคลุมของเด็กวัยเรียน (ป.6) ได้รับการชั่งน้ำหนัก วัดส่วนสูง', 'url' => '#'],
                          ['label' => '3.2 ร้อยละของเด็กวัยเรียน (ป.6) สูงดีสมส่วน', 'url' => '#'],
                          ['label' => '3.3 ร้อยละเด็กวัยเรียน (ป.6) ได้รับการคัดกรองสุขภาพช่องปาก', 'url' => '#'],                              
                          ['label' => '3.4 ร้อยละเด็กวัยเรียน (ป.6) ฟันดีไม่มีผุ (cavity free)', 'url' => '#'],                              
                          ],                         
                        ],  
                        ['label' => '4. ร้อยละของวัยทำงาน อายุ 30-44 ปี มีค่าดัชนีมวลกายปกติ', 'url' => 'index.php?r=qof62t1/index',
                        'items' => [
                          ['label' => '4.1 ร้อยละความครอบคลุมการคัดกรองดัชนีมวลกายในวัยทำงาน อายุ 30-44 ปี', 'url' => '#'],
                          ['label' => '4.2 ร้อยละของวัยทำงาน อายุ 30-44 ปี มีค่าดัชนีมวลกายปกติ', 'url' => '#'],                              
                          ],                         
                        ],                                         
                  ],
                        
                ],
            ],
          ]);
*/
        ?>



          </ul>

        <!--
          <form class="navbar-form navbar-left" role="search">
            <div class="form-group">
              <input type="text" class="form-control" id="navbar-search-input" placeholder="Search">
            </div>
          </form>
        -->

        </div>
        <!-- /.navbar-collapse -->

        <!-- Navbar Right Menu -->
        <div class="navbar-custom-menu">
          <ul class="nav navbar-nav">
            <!-- Messages: style can be found in dropdown.less-->


            <!-- Notifications Menu -->
            <li class="dropdown notifications-menu">
              <!-- Menu toggle button -->
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <i class="fa fa-bell-o"></i>
                <span class="label label-warning">10</span>
              </a>
              <ul class="dropdown-menu">
                <li class="header">You have 10 notifications</li>
                <li>
                  <!-- Inner Menu: contains the notifications -->
                  <ul class="menu">
                    <li><!-- start notification -->
                      <a href="#">
                        <i class="fa fa-users text-aqua"></i> 5 new members joined today
                      </a>
                    </li>
                    <!-- end notification -->
                  </ul>
                </li>
                <li class="footer"><a href="#">View all</a></li>
              </ul>
            </li>
            <!-- Tasks Menu -->


            <!-- User Account Menu -->
                <!-- User Account: style can be found in dropdown.less -->

                <li class="dropdown user user-menu">
                <!--
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="<?php //echo $directoryAsset ?>/img/user2-160x160.jpg" class="user-image" alt="User Image"/>
                        <span class="hidden-xs">Alexander Pierce</span>
                    </a>
                -->
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <!--
                        <li class="user-header">
                            <img src="<?php // echo $directoryAsset ?>/img/user2-160x160.jpg" class="img-circle"
                                 alt="User Image"/>

                            <p>
                                Alexander Pierce - Web Developer
                                <small>Member since Nov. 2012</small>
                            </p>
                        </li>
                        -->
                        <!-- Menu Body -->
                        <!--
                        <li class="user-body">
                            <div class="col-xs-4 text-center">
                                <a href="#">Followers</a>
                            </div>
                            <div class="col-xs-4 text-center">
                                <a href="#">Sales</a>
                            </div>
                            <div class="col-xs-4 text-center">
                                <a href="#">Friends</a>
                            </div>
                        </li>
                        -->
                        <!-- Menu Footer-->
                        <!--
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="#" class="btn btn-default btn-flat">Profile</a>
                            </div>
                            <div class="pull-right">
                                <?php /* echo Html::a(
                                    'Sign out',
                                    ['/site/logout'],
                                    ['data-method' => 'post', 'class' => 'btn btn-default btn-flat']
                                ) */ ?>
                            </div>
                        </li>
                        -->
                    </ul>
                </li>
                
                <!-- User Account: style can be found in dropdown.less -->
                <li>
                <!--    <a href="#" ><i class="fa fa-gears"></i></a>-->
                
                    <?php if(!$checklogin){ ?>
                        <li>
                        <?= Html::a(
                                    '<i class="glyphicon glyphicon-lock"></i> เข้าสู่ระบบ',
                                    ['/site/login'],
                                    ['class' => '']
                                ) ?>
                        </li>
						<?php }else { ?>
                        <?php if(Yii::$app->user->can('manageUsers')): ?>
                        <li>
                        <?= Html::a(
                                    '<i class="fa fa-users"></i> จัดการผู้ใช้',
                                    ['/user/index'],
                                    ['class' => '']
                                ) ?>
                        </li>
                        <?php endif; ?>
                        <li><a href=""><i class="fa fa-hospital-o" aria-hidden="true"></i><?= ' ' . htmlspecialchars($session['hoscode']) ?></a></li>
                        <li><a href="#"><i class="glyphicon glyphicon-user"></i><?= ' ' . htmlspecialchars($session['fullname']) . ' ' ?></a></li>
                        <li>
                        <?= Html::a(
                                    'ออกจากระบบ <i class="glyphicon glyphicon-log-out"></i>',
                                    ['/logout'],
                                    ['class' => '']
                                ) ?>
                        </li>
						<?php } ?>

                </li>

        <!-- /.navbar-custom-menu -->
      </div>
      <!-- /.container-fluid -->
    </nav>
</header>
