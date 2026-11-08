<aside class="main-sidebar">

    <section class="sidebar">

        <!-- Sidebar user panel -->
        <!--
        <div class="user-panel">
            <div class="pull-left image">
                <img src="<?php // echo $directoryAsset ?>/img/user2-160x160.jpg" class="img-circle" alt="User Image"/>
            </div>
            <div class="pull-left info">
                <p>Alexander Pierce</p>

                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>
        -->
        <!-- search form -->
        <!--
        <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search..."/>
              <span class="input-group-btn">
                <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
            </div>
        </form>
        -->
        <!-- /.search form -->

        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu tree', 'data-widget'=> 'tree'],
                'items' => [
                    ["label" => "Server Status", "url" => ["/site/index"], "icon" => "server"],
                    ["label" => "Dashboard", "url" => ["/dashboard/index"], "icon" => "dashboard"],                                    
                    ["label" => "รายงาน Report39", "url" => ["/rsqltemplate/index"], "icon" => "th"],
                    ["label" => "รายงาน อื่นๆ", "url" => ["/reporthosos/index"], "icon" => "th"],
                    ["label" => "ตรวจสอบข้อมูล 43 แฟ้ม", "url" => ["/site/43file"], "icon" => "th"],                                
                    ["label" => "ข้อมูล visit รายเดือน", "url" => ["/totalvisit/index"], "icon" => "th-list"],
                    ["label" => "Check App Code", "url" => ["/approvecode/index"], "icon" => "th"],
                    ["label" => "NCD", "url" => ["/ncd/index"], "icon" => "th"],
                    ["label" => "QOF", "url" => ["/qof/index"], "icon" => "th"],
                    ["label" => "PPA", "url" => ["/ppa/index"], "icon" => "th"],
                    ["label" => "KPI", "url" => ["/kpi/index"], "icon" => "th"], 

                ],
            ]
        ) ?>

    </section>

</aside>
