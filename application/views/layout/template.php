<!DOCTYPE HTML>
<html>
    <head>
        <meta content="width=device-width, initial-scale=1" name="viewport">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0, width=device-width" name="viewport"/>
        <link rel="shortcut icon" href="<?php echo HTTP_PATH; ?>img/front/favicon/favicon.ico" type="image/x-icon">

        <title> <?php echo SITE_TITLE . " - " . TAG_LINE . " - " . $title; ?> </title>

        <!-- Owl Carousel Assets -->
        <link href="<?php echo HTTP_PATH; ?>css/front/style.css" rel="stylesheet">
        <link href="<?php echo HTTP_PATH; ?>css/front/media.css" rel="stylesheet">
        <link href="<?php echo HTTP_PATH; ?>css/front/jquery.jqzoom.css" rel="stylesheet">
        <!--<link href="css/media.css" rel="stylesheet">-->
        <script type="text/javascript" src="<?php echo HTTP_PATH; ?>js/front/jquery-1.9.1.min.js"></script>
        <link type="text/css" rel="stylesheet" href="<?php echo HTTP_PATH; ?>css/front/font-awesome.min.css" >
        <script src="<?php echo HTTP_PATH; ?>js/cssua.min.js"  type="text/javascript" ></script>
        <script src="<?php echo HTTP_PATH; ?>js/html5.js"  type="text/javascript" ></script>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
        <script src="<?php echo HTTP_PATH; ?>js/jquery.html5-placeholder-shim.js" type="text/javascript" ></script>
        <!--<script type="text/javascript" src="<?php echo HTTP_PATH; ?>js/front/jquery-1.6.js"></script>-->
        <!--<script type="text/javascript" src="<?php echo HTTP_PATH; ?>js/front/jquery.jqzoom-core.js"></script>--> 

        

       <script src="<?php echo HTTP_PATH; ?>assets/frontend/js/bootstrap-toggle.min.js" type="text/javascript" ></script>
        


        <script type="text/javascript">
            $(document).ready(function() {
                //                $('.jqzoom').jqzoom({
                //                    zoomType: 'innerzoom',
                //                    preloadImages: false,
                //                    alwaysOn: false
                //                });

                $('.logi').click(function() {

                    $('.onroi').slideToggle();
                });
                
                $(".close-sm").click(function() {
                    $(".alert-success").fadeOut();
                    $(".alert-block").fadeOut();
                });
            });
        </script>


        <!--<link rel="apple-touch-icon" sizes="57x57" href="<?php echo HTTP_PATH; ?>img/front/favicon/apple-touch-icon-57x57.png">
        <link rel="apple-touch-icon" sizes="60x60" href="<?php echo HTTP_PATH; ?>img/front/favicon/apple-touch-icon-60x60.png">
        <link rel="apple-touch-icon" sizes="72x72" href="<?php echo HTTP_PATH; ?>img/front/favicon/apple-touch-icon-72x72.png">
        <link rel="apple-touch-icon" sizes="76x76" href="<?php echo HTTP_PATH; ?>img/front/favicon/apple-touch-icon-76x76.png">
        <link rel="apple-touch-icon" sizes="114x114" href="<?php echo HTTP_PATH; ?>img/front/favicon/apple-touch-icon-114x114.png">
        <link rel="apple-touch-icon" sizes="120x120" href="<?php echo HTTP_PATH; ?>img/front/favicon/apple-touch-icon-120x120.png">
        <link rel="apple-touch-icon" sizes="144x144" href="<?php echo HTTP_PATH; ?>img/front/favicon/apple-touch-icon-144x144.png">
        <link rel="apple-touch-icon" sizes="152x152" href="<?php echo HTTP_PATH; ?>img/front/favicon/apple-touch-icon-152x152.png">
        <link rel="apple-touch-icon" sizes="180x180" href="<?php echo HTTP_PATH; ?>img/front/favicon/apple-touch-icon-180x180.png">
        <link rel="icon" type="image/png" href="<?php echo HTTP_PATH; ?>img/front/favicon/favicon-32x32.png" sizes="32x32">
        <link rel="icon" type="image/png" href="<?php echo HTTP_PATH; ?>img/front/favicon/android-chrome-192x192.png" sizes="192x192">
        <link rel="icon" type="image/png" href="<?php echo HTTP_PATH; ?>img/front/favicon/favicon-96x96.png" sizes="96x96">
        <link rel="icon" type="image/png" href="<?php echo HTTP_PATH; ?>img/front/favicon/favicon-16x16.png" sizes="16x16">
        --><link rel="manifest" href="<?php echo HTTP_PATH; ?>img/front/favicon/manifest.json">
        <!--<link rel="shortcut icon" href="<?php echo HTTP_PATH; ?>img/front/favicon/favicon.ico">
        --><meta name="msapplication-TileColor" content="#d26999">
        <meta name="msapplication-TileImage" content="<?php echo HTTP_PATH; ?>img/front/favicon/mstile-144x144.png">
        <meta name="msapplication-config" content="<?php echo HTTP_PATH; ?>img/front/favicon/browserconfig.xml">
        <meta name="theme-color" content="#ffffff">


    </head>
    <body>
        <div class="all_bg" style="display: none;">
            <div class="all_bg_ldr"><?php echo img("img/front/loader.gif"); ?></div>
        </div>
        <?php echo $this->load->view('layout/header'); ?>
        <?php echo $contents; ?>
        <?php echo $this->load->view('layout/footer'); ?>
    </body>
</html>