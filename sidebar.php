    <!-- dashboard menu-->
    <?php 
    $userDetails = get_user_datails($_SESSION['user_id']);
    ?>
    <li class="treeview profile-active">
    <div class="app_sidebar_user">
		<div class="user-body">
            <?php if($userDetails['photo']) { ?>
                <img src="upload_image/<?php echo $userDetails['photo']?>" alt="profile-user">
           <?php } else { ?> 
            <img src="https://laravel.spruko.com/sparic/ltr/assets/images/users/avatars/2.png" alt="profile-user">
            <?php }?>
			
		</div>
		<div class="user-info">
			<a href="?page=add-user&id=<?=$_SESSION['user_id']?>&user=profile" class=""><span class=""><?=get_user_datails($_SESSION['user_id'])['name']?></span><br>
			<span class="proDl"><?php if($_SESSION['user_type']==1){ echo 'DGS LEVEL';} else if($_SESSION['user_type']==2){ echo 'SUPER ADMIN';} else if($_SESSION['user_type']==3){ echo 'ADMIN';} else { echo 'MANAGER';}?></span>
			</a>
		</div>
    </div>
    </li>
    <li><a href="index.php"><i class="fa fa-solid fa-house-chimney"></i><span>DASHBOARD</span></a></li>
    <!-- Configuration menu-->
    <?php if(count(array_intersect($configuration,$user_permission))>0) { ?>
        <li class="treeview <?php if(in_array($_GET['page'],$configuration) and (!isset($_GET['type']))){ echo 'active';} ?>">
            <a href="#"><i class="fa fa-solid fa-gear"></i> <span>CONFIGURATION</span> <i class="fa fa-angle-left pull-right"></i> </a>
            <ul class="treeview-menu">
                <li class="treeview <?=make_sidebar_active($_GET['page'],array('add-department','manage-department'))?>">
                    <a href="#" class="nav-link "> <i class="fa fa-th-large"></i> <span>DEPARTMENTS</span> <i class="fa fa-angle-left pull-right"></i> </a> 
                    <ul class="treeview-menu timeline-area child">
                        <li class="treeview cusul-line <?=make_sidebar_active($_GET['page'],'add-department')?>"><a href="?page=add-department" class="nav-link"> <i class=""></i> <span>ADD DEPARTMENT</span></a> </li>
                        <li class="treeview cusul-line <?=make_sidebar_active($_GET['page'],'manage-department')?>"><a href="?page=manage-department" class="nav-link"> <i class=""></i> <span>VIEW DEPARTMENTS</span></a> </li>
                    </ul>
                </li>

                <li class="treeview <?=make_sidebar_active($_GET['page'],array('add-location','manage-locations'))?>">
                    <a href="#" class="nav-link"> <i class="fa fa-map-marker-alt"></i> <span>LOCATIONS</span> <i class="fa fa-angle-left pull-right"></i></a> 
                    <ul class="treeview-menu timeline-area child ">
                        <li class="treeview cusul-line  <?=make_sidebar_active($_GET['page'],'add-location')?>"><a href="?page=add-location" class="nav-link"> <i class=""></i> <span>ADD LOCATION</span></a> </li>
                        <li class="treeview cusul-line <?=make_sidebar_active($_GET['page'],'manage-locations')?>"><a href="?page=manage-locations" class="nav-link"> <i class=""></i> <span>VIEW LOCATIONS</span></a> </li>
                    </ul>
                </li>

                <li class="treeview <?=make_sidebar_active($_GET['page'],array('add-group','manage-groups'))?>">
                    <a href="#" class="nav-link"> <i class="fa fa-layer-group"></i> <span>GROUPS</span> <i class="fa fa-angle-left pull-right"></i></a> 
                    <ul class="treeview-menu timeline-area child">
                        <li class="treeview cusul-line <?=make_sidebar_active($_GET['page'],'add-group')?>"><a href="?page=add-group" class="nav-link"> <i class=""></i> <span>ADD GROUP</span></a> </li>
                        <li class="treeview cusul-line <?=make_sidebar_active($_GET['page'],'manage-groups')?>"><a href="?page=manage-groups" class="nav-link"> <i class=""></i> <span>VIEW GROUPS</span></a> </li>
                    </ul>
                </li>

                <li class="treeview <?php if(in_array($_GET['page'],array('view-survey','add-survey')) and !isset($_GET['type'])) { echo 'active';}?>">
                    <a href="#" class="nav-link"> <i class="fa fa-list-alt"></i> <span>SURVEYS</span> <i class="fa fa-angle-left pull-right"></i></a> 
                    <ul class="treeview-menu timeline-area child <?=make_sidebar_active($_GET['page'],array('view-survey','add-survey'))?>">
                        <?php if($_SESSION['user_type']<=2) {?>
                        <li class="treeview cusul-line <?=make_sidebar_active($_GET['page'],'add-survey')?> "><a href="?page=add-survey" class="nav-link"> <i class=""></i> <span>ADD SURVEY</span></a> </li>
                        <?php } ?>    
                        <li class="treeview cusul-line <?=make_sidebar_active($_GET['page'],'view-survey')?> "><a href="?page=view-survey" class="nav-link"> <i class=""></i> <span>VIEW SURVEYS</span></a> </li>
                        
                    </ul>
                </li>
            </ul>
        </li>
    <?php } ?>
     <!-- Surveys menu-->
    <?php if(count(array_intersect($surveysMenu,$user_permission))>0) { ?> 
        <li class="treeview <?php if(in_array($_GET['page'],array('monthly-report','view-report','view-statistics','report-statistics','view-analytics','survey-outcomes','survey-statistics','view-leagues','view-contacted-list','about','contact-us','view-survey')) and $_GET['type']=='survey') { echo 'active';}?>">
            <a href="#"><i class="fa fa-light fa-comment-dots"></i><span>SURVEYS</span> <i class="fa fa-angle-left pull-right"></i> </a>
            <ul class="treeview-menu ">
                <?php if($_SESSION['user_type'] <3) {?>
                    <li class="treeview <?=($_GET['page']=='about' and $_GET['type']=='survey')?'active':''?>">
                        <a href="?page=about&type=survey" class="nav-link"> <i class="fa fa-light fa-info"></i> <span>ABOUT</span> </a> 
                    </li>

                    <li class="treeview <?=($_GET['page']=='contact-us' and $_GET['type']=='survey')?'active':''?>">
                        <a href="?page=contact-us&type=survey" class="nav-link"> <i class="fa fa-solid fa-at"></i> <span>CONTACT US</span> </a> 
                    </li>
                <?php }?>
                <li class="treeview <?=($_GET['page']=='view-survey' and $_GET['type']=='survey')?'active':''?>">
                   <a href="?page=view-survey&type=survey" class="nav-link"> <i class="fa fa-regular fa-rectangle-list"></i> <span>VIEW SURVEYS</span> </a> 
                </li>
                <li class="treeview <?=make_sidebar_active($_GET['page'],array('view-report','survey-outcomes','view-leagues','view-contacted-list'))?>">
                    <a href="#" class="nav-link "> <i class="fa fa-th-large"></i> <span>RESPONSES</span> <i class="fa fa-angle-left pull-right"></i> </a> 
                    <ul class="treeview-menu timeline-area child">
                        <li class="treeview cusul-line <?=($_GET['page']=='view-report' and $_GET['type']=='survey') ? 'active':''?>"><a href="?page=view-report&type=survey" class="nav-link"> <i class=""></i> <span>INDIVIDUAL</span></a> </li>
                        <li class="treeview cusul-line <?=($_GET['page']=='view-contacted-list' and $_GET['type']=='survey') ? 'active':''?>"><a href="?page=view-contacted-list&type=survey" class="nav-link"> <i class=""></i> <span>CONTACTS</span></a> </li>
                        <li class="treeview cusul-line <?=($_GET['page']=='survey-outcomes' and $_GET['type']=='survey') ? 'active':''?>"><a href="?page=survey-outcomes&type=survey" class="nav-link"> <i class=""></i> <span>OUTCOMES</span></a> </li>
                    </ul>
                </li>
                <li class="treeview cusul-line <?=($_GET['page']=='monthly-report' and $_GET['type']=='survey') ? 'active':''?>"><a href="?page=monthly-report&type=survey" class="nav-link"> <i class="fa fa-poll"></i> <span>RESULTS</span></a> </li>
                <?php if($_SESSION['user_type']<4) {?>
                    <li class="treeview cusul-line <?=($_GET['page']=='survey-statistics' and $_GET['type']=='survey') ? 'active':''?>">
                        <a href="?page=survey-statistics&type=survey" class="nav-link "> <i class="fa fa-pie-chart"></i> <span>STATISTICS</span> </a> 
                    </li>
                <?php } ?>
                <li class="treeview  cusul-line <?=($_GET['page']=='view-analytics' and $_GET['type']=='survey') ? 'active':''?>">
                    <a href="?page=view-analytics&type=survey" class="nav-link "> <i class="fa fa-bar-chart"></i> <span>ANALYTICS</span> </a> 
                </li>
                <li class="treeview  cusul-line <?=make_sidebar_active($_GET['page'],'view-leagues')?>">
                    <a href="?page=view-leagues&type=survey" class="nav-link "> <i class="fa fa-light fa-table-list"></i> <span>LEAGUE TABLES</span> </a> 
                </li>
            </ul>
        </li>
    <?php } ?>
    <!-- pulses -->
    <?php if(count(array_intersect($pulsesMenu,$user_permission))>0) {?> 
        <li class="treeview <?php if(in_array($_GET['page'],array('monthly-report','view-report','view-statistics','report-statistics','view-analytics','survey-outcomes','survey-statistics','view-contacted-list','view-leagues','about','contact-us','view-survey')) and $_GET['type']=='pulse') { echo 'active';}?>">
            <a href="#"><i class="fa fa-light fa-file-circle-question"></i> <span>PULSES</span> <i class="fa fa-angle-left pull-right"></i> </a>
            <ul class="treeview-menu">
                <?php if($_SESSION['user_type'] <3) {?>
                    <li class="treeview <?=($_GET['page']=='about' and $_GET['type']=='pulse')?'active':''?>">
                        <a href="?page=about&type=pulse" class="nav-link"> <i class="fa fa-light fa-info"></i> <span>ABOUT</span> </a> 
                    </li>

                    <li class="treeview  <?=($_GET['page']=='contact-us' and $_GET['type']=='pulse')?'active':''?>">
                        <a href="?page=contact-us&type=pulse" class="nav-link"> <i class="fa fa-solid fa-at"></i> <span>CONTACT US</span> </a> 
                    </li>
                <?php }?>
                <li class="treeview <?=($_GET['page']=='view-survey' and $_GET['type']=='pulse')?'active':''?>">
                    <a href="?page=view-survey&type=pulse" class="nav-link"> <i class="fa fa-regular fa-rectangle-list"></i> <span>VIEW  PULSES</span> </a> 
                </li>
                <li class="treeview <?=make_sidebar_active($_GET['page'],array('view-report','survey-outcomes','view-contacted-list'))?>">
                    <a href="#" class="nav-link "> <i class="fa fa-th-large"></i> <span>RESPONSES</span> <i class="fa fa-angle-left pull-right"></i> </a> 
                    <ul class="treeview-menu timeline-area child">
                        <li class="treeview cusul-line <?=($_GET['page']=='view-report' and $_GET['type']=='pulse') ? 'active':''?>"><a href="?page=view-report&type=pulse" class="nav-link"> <i class=""></i> <span>INDIVIDUAL</span></a> </li>
                        <li class="treeview cusul-line <?=($_GET['page']=='view-contacted-list' and $_GET['type']=='pulse') ? 'active':''?>"><a href="?page=view-contacted-list&type=pulse" class="nav-link"> <i class=""></i> <span>CONTACTS</span></a> </li>

                        <li class="treeview cusul-line <?=($_GET['page']=='survey-outcomes' and $_GET['type']=='pulse') ? 'active':''?>"><a href="?page=survey-outcomes&type=pulse" class="nav-link"> <i class=""></i> <span>OUTCOMES</span></a> </li>
                    </ul>
                </li>  
                
                <li class="treeview cusul-line <?=($_GET['page']=='monthly-report' and $_GET['type']=='pulse') ? 'active':''?>"><a href="?page=monthly-report&type=pulse" class="nav-link"> <i class="fa fa-poll"></i> <span>RESULTS</span></a> </li>
                <?php if($_SESSION['user_type']<4) {?>
                <li class="treeview cusul-line <?=($_GET['page']=='survey-statistics' and $_GET['type']=='pulse') ? 'active':''?>">
                    <a href="?page=survey-statistics&type=pulse" class="nav-link "> <i class="fa fa-pie-chart"></i> <span>STATISTICS</span> </a> 
                </li>
                <?php }?>
                <li class="treeview cusul-line <?=($_GET['page']=='view-analytics' and $_GET['type']=='pulse') ? 'active':''?>">
                    <a href="?page=view-analytics&type=pulse" class="nav-link"> <i class="fa fa-bar-chart"></i> <span>ANALYTICS</span> </a> 
                </li>
                <li class="treeview  cusul-line <?=make_sidebar_active($_GET['page'],'view-leagues')?>">
                    <a href="?page=view-leagues&type=pulse" class="nav-link "> <i class="fa fa-light fa-table-list"></i> <span>LEAGUE TABLES</span> </a> 
                </li>
            </ul>
        </li>
    <?php } ?>
    <!-- Engagement -->
    <li class="treeview <?php if(in_array($_GET['page'],array('monthly-report','view-report','view-statistics','report-statistics','view-analytics','survey-outcomes','survey-statistics','view-contacted-list','view-leagues','about','contact-us','view-survey')) and $_GET['type']=='engagement') { echo 'active';}?>">
        <a href="#"><i class="fa fa-light fa-clipboard-question"></i> <span>ENGAGEMENTS</span> <i class="fa fa-angle-left pull-right"></i> </a>
        <ul class="treeview-menu ">
        <?php if($_SESSION['user_type'] <3) { ?>
            <li class="treeview <?=($_GET['page']=='about' and $_GET['type']=='engagement')?'active':''?>">
                <a href="?page=about&type=engagement" class="nav-link"> <i class="fa fa-light fa-info"></i> <span>ABOUT</span> </a> 
            </li>

            <li class="treeview <?=($_GET['page']=='contact-us' and $_GET['type']=='engagement')?'active':''?>">
                <a href="?page=contact-us&type=engagement" class="nav-link"> <i class="fa fa-solid fa-at"></i> <span>CONTACT US</span> </a> 
            </li>
            <?php }?>
            <li class="treeview <?=($_GET['page']=='view-survey' and $_GET['type']=='engagement')?'active':''?>">
                <a href="?page=view-survey&type=engagement" class="nav-link"> <i class="fa fa-regular fa-rectangle-list"></i> <span>VIEW  ENGAGEMENTS</span> </a> 
            </li>
            <li class="treeview <?=make_sidebar_active($_GET['page'],array('view-report','survey-outcomes','view-contacted-list'))?>">
                <a href="#" class="nav-link "> <i class="fa fa-th-large"></i> <span>RESPONSES</span> <i class="fa fa-angle-left pull-right"></i> </a> 
                <ul class="treeview-menu timeline-area child">
                    
                    <li class="treeview cusul-line <?=($_GET['page']=='view-report' and $_GET['type']=='engagement') ? 'active':''?>"><a href="?page=view-report&type=engagement" class="nav-link"> <i class=""></i> <span>INDIVIDUAL</span></a> </li>
                    <li class="treeview cusul-line  <?=($_GET['page']=='view-contacted-list' and $_GET['type']=='engagement') ? 'active':''?>"><a href="?page=view-contacted-list&type=engagement" class="nav-link"> <i class=""></i> <span>CONTACTS</span></a> </li>

                    <li class="treeview cusul-line <?=($_GET['page']=='survey-outcomes' and $_GET['type']=='engagement') ? 'active':''?>"><a href="?page=survey-outcomes&type=engagement" class="nav-link"> <i class=""></i> <span>OUTCOMES</span></a> </li>
                </ul>
            </li>  
            <li class="treeview cusul-line <?=($_GET['page']=='monthly-report' and $_GET['type']=='engagement') ? 'active':''?>"><a href="?page=monthly-report&type=engagement" class="nav-link"> <i class="fa fa-poll"></i> <span>RESULTS</span></a> </li>

            <?php if($_SESSION['user_type']<4) {?>
                <li class="treeview cusul-line <?=($_GET['page']=='survey-statistics' and $_GET['type']=='engagement') ? 'active':''?>">
                    <a href="?page=survey-statistics&type=engagement" class="nav-link "> <i class="fa fa-pie-chart"></i> <span>STATISTICS</span> </a> 
                </li>
            <?php }?>
            <li class="treeview cusul-line <?=($_GET['page']=='view-analytics' and $_GET['type']=='engagement') ? 'active':''?>">
                <a href="?page=view-analytics&type=engagement" class="nav-link"> <i class="fa fa-bar-chart"></i> <span>ANALYTICS</span> </a> 
            </li>
            <li class="treeview  cusul-line <?=make_sidebar_active($_GET['page'],'view-leagues')?>">
                <a href="?page=view-leagues&type=engagement" class="nav-link "> <i class="fa fa-light fa-table-list"></i> <span>LEAGUE TABLES</span> </a> 
            </li>
        </ul>
    </li>

     <!-- Reports menu-->
    <?php if(count(array_intersect($reportMenu,$user_permission))>0) {?> 
        <li class="treeview tree-menu <?=make_sidebar_active($_GET['page'],$reportMenu)?>">
            <a href="#"><i class="fa fa-light fa-folder"></i> <span>REPORTS</span> <i class="fa fa-angle-left pull-right"></i> </a>
            <?php
            //Task : Reports(62385754), Document Id : 3520827858 
            $new_report_section = false;
            if($new_report_section): 
            ?>
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
            </ul>
            <?php endif ?>
            <ul class="treeview-menu timeline-area <?=make_sidebar_active($_GET['page'],$reportMenu)?>">
                <li class="treeview cusul-line <?=($_GET['page']=='list-report-templates') ? 'active':''?>">
                    <a href="?page=list-report-templates" class="nav-link"> <i class=""></i> <span>VIEW TEMPLATES</span> </a> 
                </li>

                <li class="treeview cusul-line <?=make_sidebar_active($_GET['page'],'list-scheduled-templates')?>">
                    <a href="?page=list-scheduled-templates" class="nav-link"> <i class=""></i> <span>VIEW SCHEDULE</span> </a> 
                </li>
            </ul>
        </li>
    <?php } ?>
     <!-- Users menu-->
    <?php if(count(array_intersect($userMenu,$user_permission))>0) {?>     
        <li class="treeview tree-menu <?=(in_array($_GET['page'],array('add-user','import-users','view-user')) && !isset($_GET['user'])) ? 'active':''?>">
            <a href="#"><i class="fa fa-solid fa-users"></i> <span>USERS</span> <i class="fa fa-angle-left pull-right"></i> </a>
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