<!DOCTYPE html>
<html>
    <head>
        <meta content="width=device-width, initial-scale=1" name="viewport">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="shortcut icon" type="<?php echo HTTP_PATH; ?>img/front/x-icon" href="img/favicon.ico">
        <title>Welcome to WoodyStuff</title>
        <link href="<?php echo HTTP_PATH; ?>css/front/style.css" rel="stylesheet" type="text/css">
        <link href="<?php echo HTTP_PATH; ?>css/front/media.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
        <script src="<?php echo HTTP_PATH; ?>js/front/cssua.min.js"  type="text/javascript" ></script>
        <script src="<?php echo HTTP_PATH; ?>js/front/html5.js"  type="text/javascript" ></script>
        <script src="<?php echo HTTP_PATH; ?>js/front/jquery.html5-placeholder-shim.js" type="text/javascript" ></script>
        <script src="<?php echo HTTP_PATH; ?>js/front/jquery.styleSelect.js" type="text/javascript" ></script>

        <script>

            $(document).ready(function() {
                $('.show_d').click(function() {
                    $('.hosiw_sv').slideToggle()
                });
            });

        </script>
    </head>
    <body>
        <div class="mid_prt">
            <div class="trri no_bg">

                <?php echo $this->load->view('layout/header'); ?>
                <?php echo $contents; ?>
            </div>
            <?php echo $this->load->view('layout/footer'); ?>
        </div>

        <!-- /**************************************************************/--> 
        <script type="text/javascript">

            $(document).ready(function() {

                $(".tab_content").hide();
                $(".tab_content:first").show();

                $("ul.tabs li").click(function() {
                    $("ul.tabs li").removeClass("active");
                    $(this).addClass("active");
                    $(".tab_content").hide();
                    var activeTab = $(this).attr("rel");
                    $("#" + activeTab).fadeIn();
                });
            });

        </script>
    </body>
</html>
