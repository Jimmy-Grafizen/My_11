<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

            <title><?php echo SITE_TITLE . " :: " . TAG_LINE . "-"; ?>Administration - <?php echo $title; ?></title>

            <!--<link rel="apple-touch-icon" sizes="57x57" href="<?php echo HTTP_PATH; ?>img/front/favicon/apple-touch-icon-57x57.png"/>
            <link rel="apple-touch-icon" sizes="60x60" href="<?php echo HTTP_PATH; ?>img/front/favicon/apple-touch-icon-60x60.png"/>
            <link rel="apple-touch-icon" sizes="72x72" href="<?php echo HTTP_PATH; ?>img/front/favicon/apple-touch-icon-72x72.png"/>
            <link rel="apple-touch-icon" sizes="76x76" href="<?php echo HTTP_PATH; ?>img/front/favicon/apple-touch-icon-76x76.png"/>
            <link rel="apple-touch-icon" sizes="114x114" href="<?php echo HTTP_PATH; ?>img/front/favicon/apple-touch-icon-114x114.png"/>
            <link rel="apple-touch-icon" sizes="120x120" href="<?php echo HTTP_PATH; ?>img/front/favicon/apple-touch-icon-120x120.png"/>
            <link rel="apple-touch-icon" sizes="144x144" href="<?php echo HTTP_PATH; ?>img/front/favicon/apple-touch-icon-144x144.png"/>
            <link rel="apple-touch-icon" sizes="152x152" href="<?php echo HTTP_PATH; ?>img/front/favicon/apple-touch-icon-152x152.png"/>
            <link rel="apple-touch-icon" sizes="180x180" href="<?php echo HTTP_PATH; ?>img/front/favicon/apple-touch-icon-180x180.png"/>
            <link rel="icon" type="image/png" href="<?php echo HTTP_PATH; ?>img/front/favicon/favicon-32x32.png" sizes="32x32"/>
            <link rel="icon" type="image/png" href="<?php echo HTTP_PATH; ?>img/front/favicon/android-chrome-192x192.png" sizes="192x192"/>
            <link rel="icon" type="image/png" href="<?php echo HTTP_PATH; ?>img/front/favicon/favicon-96x96.png" sizes="96x96"/>
            <link rel="icon" type="image/png" href="<?php echo HTTP_PATH; ?>img/front/favicon/favicon-16x16.png" sizes="16x16"/>
            --><link rel="manifest" href="<?php echo HTTP_PATH; ?>img/front/favicon/manifest.json"/>
            <!--<link rel="shortcut icon" href="<?php echo HTTP_PATH; ?>img/front/favicon/favicon.ico"/>
-->
            <link href="<?php echo HTTP_PATH . "css/" ?>media.css" rel="stylesheet" type="text/css" />
            <link href="<?php echo HTTP_PATH . "css/" ?>bootstrap.css" rel="stylesheet" type="text/css" />
            <link href="<?php echo HTTP_PATH . "css/" ?>bootstrap-theme.css" rel="stylesheet" type="text/css" />
            <link href="<?php echo HTTP_PATH . "css/" ?>login_pg.css" rel="stylesheet" type="text/css" />
            <link href="<?php echo HTTP_PATH . "css/" ?>style1.css" rel="stylesheet" type="text/css" />
            <link href="<?php echo HTTP_PATH . "css/" ?>media_new.css" rel="stylesheet" type="text/css" />
            <link href="<?php echo HTTP_PATH . "css/" ?>aep-custom.css" rel="stylesheet" type="text/css" />
            <link href="<?php echo HTTP_PATH . "css/" ?>listing.css" rel="stylesheet" type="text/css" />
            <!--<link href="<?php echo HTTP_PATH . "css/" ?>common.css" rel="stylesheet" type="text/css" />-->
            <link href="<?php echo HTTP_PATH . "css/" ?>aep-custom.css" rel="stylesheet" type="text/css" />

            <script src="<?php echo HTTP_PATH . "js/" ?>jquery-latest.js"></script>
            <script src="<?php echo HTTP_PATH . "js/" ?>jquery-1.10.2.min.js"></script>
            <script src="<?php echo HTTP_PATH . "js/" ?>common.js"></script>
            <script src="<?php echo HTTP_PATH . "js/" ?>html5.js"></script>
            <script src="<?php echo HTTP_PATH . "js/" ?>bootstrap.js"></script>
            <script src="<?php echo HTTP_PATH . "js/" ?>cssua.min.js"></script>
            <script src="<?php echo HTTP_PATH . "js/" ?>main.js"></script>
            <script src="<?php echo HTTP_PATH . "js/" ?>scrolltopcontrol.js"></script>
            <script src="<?php echo HTTP_PATH . "js/" ?>jquery.browser.js"></script>
            <?php
            $id = $this->session->userdata('adminId');
            $cond = "id ='" . $this->session->userdata('adminId') . "'";
            $select_fields = "tbl_admin.flag";
            $joins = array();
            $table = "tbl_admin";
            $admin_detail = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);
            ?>
            <script>
                $(document).ready(function() {
                    $(".icon-align-justify").click(function() {
                        $(this).toggleClass("collapse-icon");
                        $('#hdasf').toggleClass('small_menu');
                        $('#dsaffasf').toggleClass('smalldf');
                        $.ajax({
                            url: "<?php echo HTTP_PATH . "admin/home/menupgrade" ?>",
                            dataType: 'json',
                            type: 'POST',
                            success: function(data, textStatus, XMLHttpRequest)
                            {
                            }
                        });
                    })
                    
                })
            </script>
    </head>
    <?php $color = "#010A93"; ?>
    <body>
        <div class="all_bg" id="loading-image">
            <div class="all_bg_ldr"><img src="<?php echo HTTP_PATH; ?>img/front/loader.gif" alt=""/></div>
        </div>
        <div class="mid_prt">
            <header class="heaport">
                <div class="header navbar navbar-inverse ">
                    <!-- BEGIN TOP NAVIGATION BAR -->
                    <div class="navbar-inner">

                        <div class="home_dv <?php if (!$admin_detail['flag']) echo "small_menu" ?>" id="hdasf">
                            <?php echo anchor("/admin/home/dashboard", img(array('src' => 'img/front/logo.png', 'alt' => SITE_TITLE, 'title' => SITE_TITLE, 'height' => '')), array('escape' => false, 'class' => 'logo')); ?>

                            <span>
                                <?php echo anchor("/admin/home/dashboard", img(array('src' => 'img/hm.png', 'alt' => SITE_TITLE, 'title' => SITE_TITLE, 'width' => '14')), array('escape' => false, 'class' => 'logo')); ?>
                            </span>
                        </div>
                        <a class="onlci  icon-align-justify <?php if (!$admin_detail['flag']) echo "collapse-icon" ?>"  onclick="javascript:">&nbsp;</a>
                        <div class="page-title">
                            <?php echo $this->breadcrumbs->show(); ?>
                        </div>  
                        <div class="showd">
                            <i class="icon-cog-3" onclick="javascript:$('#frsd').toggle();"></i>
                            <div class="tioud" id="frsd" style="display: none;">
                                <ul>
                                    <li><a href="<?php echo HTTP_PATH . 'admin/home/dashboard' ?>"><i class=" icon-user"></i>My Account</a></li>
                                    <li><a href="<?php echo HTTP_PATH . 'admin/home/logout' ?>"><i class="icon-off"></i>logout</a></li>
                                </ul>

                                <a class="scrollup" href="#" style="display: inline;">Scroll</a>
                            </div>
                        </div>
                    </div>
                    <!-- END TOP NAVIGATION BAR --> 
                </div>
            </header>

            <?php echo $this->load->view('element/admin_left_menu'); ?>
            <div class="oe_dov"> 
                <!-- BEGIN SIDEBAR -->

                <!-- END SIDEBAR -->  <!-- BEGIN DASHBOARD CONTAINER-->
                <div class="page-content icoi"> 
                    <div class="content">
                        <?php echo $contents; ?>
                        <!-- END PAGE --> 
                    </div>
                </div>
                <!-- END CONTAINER --> 
            </div>
        </div>

        <div class="inner_als">
            <footer class="footer">
                <?php // echo $this->element('sql_dump'); ?>
            </footer>
        </div>
    </body>
</html>

</body>
</html>
