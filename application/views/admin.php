<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        
		    <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=no;">

        <!--<link rel="shortcut icon" href="<?php echo HTTP_PATH; ?>img/front/favicon.ico">
        --><title><?php echo SITE_TITLE . " :: " . TAG_LINE . "-"; ?>Administration - <?php echo $title; ?></title>

        <!-- Bootstrap core CSS -->
        <link href="<?php echo HTTP_PATH; ?>css/bootstrap.min.css" rel="stylesheet">
        <link href="<?php echo HTTP_PATH; ?>css/bootstrap-reset.css" rel="stylesheet">
        <!--external css-->
        <link href="<?php echo HTTP_PATH; ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
        <link href="<?php echo HTTP_PATH; ?>assets/jquery-easy-pie-chart/jquery.easy-pie-chart.css" rel="stylesheet" type="text/css" media="screen"/>
        <link rel="stylesheet" href="<?php echo HTTP_PATH; ?>css/owl.carousel.css" type="text/css">

        <!--right slidebar-->
        <link href="<?php echo HTTP_PATH; ?>css/slidebars.css" rel="stylesheet">

        <!-- Custom styles for this template -->

        <link href="<?php echo HTTP_PATH; ?>css/style1.css" rel="stylesheet">
        <link href="<?php echo HTTP_PATH; ?>css/style-responsive.css" rel="stylesheet" />
        <link href="<?php echo HTTP_PATH; ?>css/table-responsive.css" rel="stylesheet" />


        <script src="<?php echo HTTP_PATH; ?>js/jquery.js"></script>
        <script src="<?php echo HTTP_PATH; ?>js/bootstrap.min.js"></script>


        <script type="text/javascript" src="<?php echo HTTP_PATH; ?>js/jquery.validate.min.js"></script>

        <script>

            //owl carousel
            $(document).ready(function() {
                $("#myform").validate();
            });
            
        </script>
        
        <?php
        $id = $this->session->userdata('adminId');
        $cond = "id ='" . $this->session->userdata('adminId') . "'";
        $select_fields = "tbl_users.flag";
        $joins = array();
        $table = "tbl_users";
        $admin_detail = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);
        ?>


        <!-- HTML5 shim and Respond.js IE8 support of HTML5 tooltipss and media queries -->
        <!--[if lt IE 9]>
          <script src="<?php echo HTTP_PATH; ?>js/html5shiv.js"></script>
          <script src="<?php echo HTTP_PATH; ?>js/respond.min.js"></script>
        <![endif]-->
    </head>

    <body>
        <div id="loading-image" class="all_bg">
            <div class="all_bg_ldr"><img alt="" src="<?php echo HTTP_PATH; ?>img/loader_backend.gif" width="100" ></div>
        </div>
        <section id="container" <?php if ($admin_detail['flag']) echo "class='sidebar-closed'" ?>>
            <!--header start-->
            <header class="header white-bg">
                
                <!--logo start-->
             
                
                <div class="sidebar-toggle-box">
                    <div class="fa fa-bars toggle-left-menu tooltips" data-placement="right" data-original-title=""></div>
                </div> 
				
				<?php echo anchor("/admin/home/dashboard", img(array('src' => 'img/logo.png',  'alt' => SITE_TITLE, 'class' => "logo_first",  'title' => SITE_TITLE, 'height' => '','style'=>"margin-top: 17px;
    margin-left: 16px;width: 100px")).
                img(array('src' => 'img/logo_last.png',  'alt' => SITE_TITLE, 'title' => SITE_TITLE, 'class' => "logo_last", 'height' => '','style'=>"margin-top: 17px;
    margin-left: 16px;width: 100px")), array('escape' => false, 'class' => 'logo')); ?>
                <!--logo end-->

                <div class="top-nav ">
                    <!--search & user info start-->

                    <ul class="nav pull-right top-menu">
                        <!-- user login dropdown start-->
                        <li class="notifications-menu" title="Add New Matches" id="__new_match_count_li" style="display: none;">
                            <a href="<?php echo HTTP_PATH; ?>admin/matches/add" class="dropdown-toggle" >
                              <i class="fa fa-bell-o"></i>
                              <span class="label label-warning" id="__new_match_count_">0</span>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                <img alt="" src="<?php echo HTTP_PATH; ?>img/usr.png">
                                <span class="username">Admin</span>
                                <b class="caret"></b>
                            </a>
                            <ul class="dropdown-menu extended logout">
                                <div class="log-arrow-up"></div>
                                <li><a href="<?php echo HTTP_PATH . "admin/home/updateprofile" ?>"><i class=" fa fa-user"></i>Update<br/> Profile</a></li>
                                <li><a href="<?php echo HTTP_PATH . "admin/home/changepassword" ?>"><i class="fa fa-lock"></i> Change<br/> Password</a></li>
                                <li><a href="<?php echo HTTP_PATH . "admin/home/changeemail" ?>"><i class="fa fa-envelope"></i> Change<br/>  Email</a></li>
                                <li><a href="<?php echo HTTP_PATH . "admin/home/logout" ?>"><i class="fa fa-key"></i> Log Out</a></li>
                            </ul>
                        </li>
                        <!-- user login dropdown end -->
                    </ul>
                    <!--search & user info end-->
                </div>
            </header>
            <!--header end-->
            <!--sidebar start-->
            <?php $this->load->view('element/admin_left_menu'); ?>
            <!--sidebar end-->
            <!--main content start-->
            <?php echo $contents ?>
            <!--main content end-->


        </section>
            <!--footer start-->
            <footer class="site-footer">
                <div class="text-center">
                    <?php echo date('Y'); ?> &copy; <?php echo SITE_TITLE; ?>.
                    <a href="#" class="go-top">
                        <i class="fa fa-angle-up"></i>
                    </a>
                </div>
            </footer>
            <!--footer end-->
        <!-- js placed at the end of the document so the pages load faster -->
        <script class="include" type="text/javascript" src="<?php echo HTTP_PATH; ?>js/jquery.dcjqaccordion.2.7.js"></script>
        <script src="<?php echo HTTP_PATH; ?>js/jquery.scrollTo.min.js"></script>
        <script src="<?php echo HTTP_PATH; ?>js/jquery.nicescroll.js" type="text/javascript"></script>
        <script src="<?php echo HTTP_PATH; ?>js/jquery.sparkline.js" type="text/javascript"></script>
        <script src="<?php echo HTTP_PATH; ?>js/owl.carousel.js" ></script>
        <script src="<?php echo HTTP_PATH; ?>js/jquery.customSelect.min.js" ></script>
        <script src="<?php echo HTTP_PATH; ?>js/respond.min.js" ></script>

        <!--right slidebar-->
        <script src="<?php echo HTTP_PATH; ?>js/slidebars.min.js"></script>

        <!--common script for all pages-->
        <script src="<?php echo HTTP_PATH; ?>js/common-scripts.js"></script>

        <!--script for this page-->
        <script src="<?php echo HTTP_PATH; ?>js/sparkline-chart.js"></script>



        <script>

            //owl carousel
            $(document).ready(function() {
                $("#owl-demo").owlCarousel({
                    navigation : true,
                    slideSpeed : 300,
                    paginationSpeed : 400,
                    singleItem : true,
                    autoPlay:true

                });
            });
            
            //custom select box
            $(function(){
                $('select.styled').customSelect();
            });

        </script>

        <script>
            $(document).ready(function() {
                $('.toggle-left-menu').click(function () {
                    $.ajax({
                        url: "<?php echo HTTP_PATH . "admin/home/menupgrade" ?>",
                        dataType: 'json',
                        type: 'POST',
                        success: function(data, textStatus, XMLHttpRequest)
                        {
                        }
                    });
                    if (!$("#container").hasClass('sidebar-closed')) {
                        $('#main-content').css({
                            'margin-left': '0px'
                        });
                        $('#sidebar').css({
                            'margin-left': '-210px'
                        });
                        $('#sidebar > ul').hide();
                        $("#container").addClass("sidebar-closed");
						$(".sidebar-overlay").removeClass("active");
						$("body").removeClass("scrollhide");
						
                    } else {
                        $('#main-content').css({
                            'margin-left': '210px'
                        });
                        $('#sidebar > ul').show();
                        $('#sidebar').css({
                            'margin-left': '0'
                        });
                        $("#container").removeClass("sidebar-closed");
						$(".sidebar-overlay").addClass("active");
						$("body").addClass("scrollhide");
                    }
                });
            })    

            
            $(document).ready(function(){
                var path = "<?php echo base_url("admin/".$this->router->fetch_class()."/".$this->router->fetch_method()); ?>";
                $("#nav-accordion").find("[href='"+path +"']").parent('li').addClass('active');
                $("#nav-accordion").find("[href='"+path +"']").parent('li').parent('ul.sub').show('show');
            })
        </script>



    <script type="text/javascript">

       setInterval( function(){ 
             ajax_new_match_get();
       }, ((1000*60)*15) ); 

       ajax_new_match_get();
       setInterval(function(){ location.reload(true); }, ((1000*60)*15)*4 ); 

       function ajax_new_match_get(){
            $.post('<?= base_url(); ?>admin/home/ajax_new_match_get',
            {
                't': 't',
                beforeSend: function() {
                   //$('#new_orders').html('<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>');
            }}, function(data) {
                if(data > 0){
                    $('#__new_match_count_li').show();
                    $('#__new_match_count_').text(data);
                }else{
                    $('#__new_match_count_li').hide();
                }
            });
        }

    </script>

    </body>
</html>

<!-- Localized -->