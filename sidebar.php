    <!-- dashboard menu-->
    <?php 
    ?>
    <li class="treeview profile-active">
        <?php if($_SESSION['user_type']==1){ ?>
            <!-- for super admin  -->
            <a href="?page=add-user&t=sa&id=<?=$_SESSION['user_id']?>&user=profile"><i class="fa fa-user"></i><span><?=$_SESSION['user_name']?></span></a>
        <?php }else if($_SESSION['user_type']==2){ ?>
             <!-- for admin  -->
            <a href="?page=add-user&t=a&id=<?=$_SESSION['user_id']?>&user=profile"><i class="fa fa-user"></i><span><?=$_SESSION['user_name']?></span></a>
        <?php }else { ?>
            <!-- for manager  -->
            <a href="?page=add-user&t=c&id=<?=$_SESSION['user_id']?>&user=profile"><i class="fa fa-user"></i><span><?=$_SESSION['user_name']?></span></a>
        <?php } ?>
    </li>
    <li><a href="index.php"><i class="fa fa-dashboard"></i><span>DASHBOARD</span></a></li>
    <!-- Configuration menu-->
    <?php if(count(array_intersect($configuration,$user_permission))>0) { ?>
        <li class="treeview <?=make_sidebar_active($_GET['page'],$configuration )?>">
            <a href="#"><i class="fa fa-solid fa-gear"></i> <span>CONFIGURATION</span> <i class="fa fa-angle-left pull-right"></i> </a>
            <ul class="treeview-menu">
                <li class="treeview <?=make_sidebar_active($_GET['page'],array('add-department','manage-department'))?>">
                    <a href="#" class="nav-link "> <i class="fa fa-th-large"></i> <span>DEPARTMENTS</span> <i class="fa fa-angle-left pull-right"></i> </a> 
                    <ul class="treeview-menu timeline-area child">
                        <li class="treeview cusul-line <?=make_sidebar_active($_GET['page'],'add-department')?>"><a href="?page=add-department" class="nav-link"> <i class=""></i> <span>Add Department</span></a> </li>
                        <li class="treeview cusul-line <?=make_sidebar_active($_GET['page'],'manage-department')?>"><a href="?page=manage-department" class="nav-link"> <i class=""></i> <span>View Departments</span></a> </li>
                    </ul>
                </li>

                <li class="treeview <?=make_sidebar_active($_GET['page'],array('add-location','manage-locations'))?>">
                    <a href="#" class="nav-link"> <i class="fa fa-map-marker-alt"></i> <span>LOCATIONS</span> <i class="fa fa-angle-left pull-right"></i></a> 
                    <ul class="treeview-menu timeline-area child ">
                        <li class="treeview cusul-line  <?=make_sidebar_active($_GET['page'],'add-location')?>"><a href="?page=add-location" class="nav-link"> <i class=""></i> <span>Add Location</span></a> </li>
                        <li class="treeview cusul-line <?=make_sidebar_active($_GET['page'],'manage-locations')?>"><a href="?page=manage-locations" class="nav-link"> <i class=""></i> <span>View Locations</span></a> </li>
                    </ul>
                </li>

                <li class="treeview <?=make_sidebar_active($_GET['page'],array('add-group','manage-groups'))?>">
                    <a href="#" class="nav-link"> <i class="fa fa-layer-group"></i> <span>GROUPS</span> <i class="fa fa-angle-left pull-right"></i></a> 
                    <ul class="treeview-menu timeline-area child">
                        <li class="treeview cusul-line <?=make_sidebar_active($_GET['page'],'add-group')?>"><a href="?page=add-group" class="nav-link"> <i class=""></i> <span>Add Group</span></a> </li>
                        <li class="treeview cusul-line <?=make_sidebar_active($_GET['page'],'manage-groups')?>"><a href="?page=manage-groups" class="nav-link"> <i class=""></i> <span>View Groups</span></a> </li>
                    </ul>
                </li>

                <li class="treeview <?=make_sidebar_active($_GET['page'],array('view-survey','add-survey'))?>">
                    <a href="#" class="nav-link"> <i class="fa fa-list-alt"></i> <span>SURVEYS</span> <i class="fa fa-angle-left pull-right"></i></a> 
                    <ul class="treeview-menu timeline-area child <?=make_sidebar_active($_GET['page'],array('view-survey','add-survey'))?>">
                        <?php if($_SESSION['user_type']==1) {?>
                        <li class="treeview cusul-line <?=make_sidebar_active($_GET['page'],'add-survey')?> "><a href="?page=add-survey" class="nav-link"> <i class=""></i> <span>Add Survey</span></a> </li>
                        <?php } ?>    
                        <li class="treeview cusul-line <?=make_sidebar_active($_GET['page'],'view-survey')?> "><a href="?page=view-survey" class="nav-link"> <i class=""></i> <span>View Surveys</span></a> </li>
                        
                    </ul>
                </li>
            </ul>
        </li>
    <?php } ?>
     <!-- Surveys menu-->
    <?php if(count(array_intersect($surveysMenu,$user_permission))>0) { ?> 
        <li class="treeview <?=make_sidebar_active($_GET['page'],array('monthly-report','view-report','view-statistics','report-statistics','view-analytics','survey-outcomes','survey-statistics','view-leagues','view-contacted-list'))?>">
            <a href="#"><i class="fa fa-poll"></i><span>  SURVEYS</span> <i class="fa fa-angle-left pull-right"></i> </a>
            <ul class="treeview-menu ">
                <li class="treeview <?=make_sidebar_active($_GET['page'],array('monthly-report','view-report','survey-outcomes','view-leagues','view-contacted-list'))?>">
                    <a href="#" class="nav-link "> <i class="fa fa-th-large"></i> <span>RESPONSES</span> <i class="fa fa-angle-left pull-right"></i> </a> 
                    <ul class="treeview-menu timeline-area child">
                        <li class="treeview cusul-line <?=make_sidebar_active($_GET['page'],'view-report')?>"><a href="?page=view-report" class="nav-link"> <i class=""></i> <span>Individual</span></a> </li>
                        <li class="treeview cusul-line <?=make_sidebar_active($_GET['page'],'view-contacted-list')?>"><a href="?page=view-contacted-list" class="nav-link"> <i class=""></i> <span>Contacts</span></a> </li>
                        <li class="treeview cusul-line  <?=make_sidebar_active($_GET['page'],'survey-outcomes')?>"><a href="?page=survey-outcomes" class="nav-link"> <i class=""></i> <span>Outcomes</span></a> </li>
                    </ul>
                </li>
                <li class="treeview cusul-line <?=make_sidebar_active($_GET['page'],'monthly-report')?>"><a href="?page=monthly-report" class="nav-link"> <i class="fa fa-poll"></i> <span>RESULTS</span></a> </li>
                <?php if($_SESSION['user_type']<2) {?>
                    <li class="treeview cusul-line <?=make_sidebar_active($_GET['page'],'survey-statistics')?>">
                        <a href="?page=survey-statistics" class="nav-link "> <i class="fa fa-pie-chart"></i> <span>STATISTICS</span> </a> 
                    </li>
                <?php } ?>
                <li class="treeview  cusul-line <?=make_sidebar_active($_GET['page'],'view-analytics')?>">
                    <a href="?page=view-analytics" class="nav-link "> <i class="fa fa-bar-chart"></i> <span>ANALYTICS</span> </a> 
                </li>
                <li class="treeview  cusul-line <?=make_sidebar_active($_GET['page'],'view-leagues')?>">
                    <a href="?page=view-leagues" class="nav-link "> <i class="fa fa-bar-chart"></i> <span>LEAGUE TABLES</span> </a> 
                </li>
            </ul>
        </li>
    <?php } ?>
     <!-- pulses -->
     <?php if(count(array_intersect($pulsesMenu,$user_permission))>0) {?> 
        <li class="treeview ">
            <a href="#"><i class="fa fa-list-alt"></i> <span>PULSES</span> <i class="fa fa-angle-left pull-right"></i> </a>
            <ul class="treeview-menu timeline-area">
                <?php if($_SESSION['user_type'] <3) {?>
                <li class="treeview cusul-line">
                    <a href="?page=#" class="nav-link"> <i class=""></i> <span>ABOUT</span> </a> 
                </li>

                <li class="treeview cusul-line">
                    <a href="?page=#" class="nav-link"> <i class=""></i> <span>CONTACT</span> </a> 
                </li>
                <?php }?>    
                <li class="treeview cusul-line">
                    <a href="?page=#" class="nav-link"> <i class=""></i> <span>RESULTS</span> </a> 
                </li>
                <?php if($_SESSION['user_type']<2) {?>
                <li class="treeview cusul-line">
                    <a href="?page=#" class="nav-link"> <i class=""></i> <span>STATISTICS</span> </a> 
                </li>
                <?php }?>
                <li class="treeview cusul-line">
                    <a href="?page=#" class="nav-link"> <i class=""></i> <span>ANALYTICS</span> </a> 
                </li>
            </ul>
        </li>
    <?php } ?>
     <!-- Reports menu-->
    <?php if(count(array_intersect($reportMenu,$user_permission))>0) {?> 
        <li class="treeview  <?=make_sidebar_active($_GET['page'],$reportMenu)?>">
            <a href="#"><i class="fa fa-list-alt"></i> <span>REPORTS</span> <i class="fa fa-angle-left pull-right"></i> </a>
            <ul class="treeview-menu timeline-area <?=make_sidebar_active($_GET['page'],$reportMenu)?>">
                <li class="treeview cusul-line <?=make_sidebar_active($_GET['type'],'report')?>">
                    <a href="?page=create-report&type=report" class="nav-link"> <i class=""></i> <span>CREATE REPORT</span> </a> 
                </li>
                <li class="treeview cusul-line <?=($_GET['page']=='create-report' && $_GET['type']=='template') ? 'active':''?>">
                    <a href="?page=create-report&type=template" class="nav-link"> <i class=""></i> <span>CREATE TEMPLATE</span> </a> 
                </li>
                <li class="treeview cusul-line <?=($_GET['page']=='manage-report-template' && $_GET['type']=='template') ? 'active':''?>">
                    <a href="?page=manage-report-template&type=template" class="nav-link"> <i class=""></i> <span>VIEW TEMPLATES</span> </a> 
                </li>

                <li class="treeview cusul-line <?=make_sidebar_active($_GET['type'],'schedule')?>">
                    <a href="?page=manage-report-template&type=schedule" class="nav-link"> <i class=""></i> <span>VIEW SCHEDULE</span> </a> 
                </li>

                <!-- <li class="treeview cusul-line">
                    <a href="?page=#" class="nav-link"> <i class=""></i> <span>VIEW SCHEDULE</span> </a> 
                </li> -->
            </ul>
        </li>
    <?php } ?>
     <!-- Users menu-->
    <?php if(count(array_intersect($userMenu,$user_permission))>0) {?>     
        <li class="treeview <?=(in_array($_GET['page'],array('add-user','import-users','view-user')) && !isset($_GET['user'])) ? 'active':''?>">
            <a href="#"><i class="fa fa-list-alt"></i> <span>USERS</span> <i class="fa fa-angle-left pull-right"></i> </a>
            <ul class="treeview-menu timeline-area">
                <li class="treeview cusul-line <?=($_GET['page']=='add-user' and !($_GET['user'])) ? 'active':'' ?>">
                    <a href="?page=add-user" class="nav-link"> <i class=""></i> <span>ADD USER</span> </a> 
                </li>

                <li class="treeview cusul-line <?=make_sidebar_active($_GET['page'],'import-users')?>">
                    <a href="?page=import-users" class="nav-link"> <i class=""></i> <span>IMPORT USERS</span> </a> 
                </li>

                <li class="treeview cusul-line <?=make_sidebar_active($_GET['page'],'view-user')?>">
                    <a href="?page=view-user" class="nav-link"> <i class=""></i> <span>VIEW USERS</span> </a> 
                </li>
            </ul>
        </li>
    <?php } ?>
    <li><a href="?page=logout"> <i class="fa fa-sign-out"></i> <span>LOGOUT</span></a></li>