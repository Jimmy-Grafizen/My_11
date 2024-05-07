<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <title>EL GUERO 2 </title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">

        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-129149279-1"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());

          gtag('config', 'UA-129149279-1');
        </script>
        <!-- CSS -->
        <link href="/../css/front/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="/../css/front/flexslider.css" rel="stylesheet" type="text/css" />
        <link href="/../css/front/prettyPhoto.css" rel="stylesheet" type="text/css" />
        <link href="/../css/front/animate.css" rel="stylesheet" type="text/css" media="all" />
        <link href="/../css/front/owl.carousel.css" rel="stylesheet">
        <link href="/../css/front/font-awesome.min.css" rel="stylesheet">
        
        <?php if($this->router->class!='home' && $this->router->method!='index') { ?>
        <link href="/../css/front/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css">
        <?php } ?>
       
        <link href="/../css/front/typeahead.css" rel="stylesheet" />
        <link rel="stylesheet" href="/../css/front/build.css"/>
        <link href="/../css/front/style.css?<?php echo time(); ?>" rel="stylesheet" type="text/css" />
        <link href="/../css/front/webslidemenu.css" rel="stylesheet" type="text/css" />
        <link href="/../css/front/responsive.css" rel="stylesheet" type="text/css" />
        <link href="/../css/jquery-ui.css" rel="stylesheet" type="text/css" />

        <!-- SCRIPTS -->
        <!--[if IE]><script src="https://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
        <!--[if IE]>
          <html class="ie" lang="en">
             <![endif]-->
        <script type="text/javascript" src="/../js/jquery-1.9.1.min.js" defer="defer"></script>
        <script src="/../js/front/jquery.min.js" type="text/javascript" defer="defer"></script>
        <script src="/../js/front/bootstrap.min.js" type="text/javascript" defer="defer"></script>
        <script src="/../js/front/jquery.prettyPhoto.js" type="text/javascript" defer="defer"></script>
        <script src="/../js/front/superfish.min.js" type="text/javascript"></script>
        <script src="/../js/front/jquery.flexslider-min.js" type="text/javascript"></script>
        <script src="/../js/front/owl.carousel.js" type="text/javascript"></script>
        <script src="/../js/front/animate.js" type="text/javascript" defer="defer"></script>
        <script src="/../js/front/jquery.BlackAndWhite.js" type="text/javascript" defer="defer"></script>
        <script src="/../js/front/myscript.js" type="text/javascript" defer="defer"></script>
        <?php if($this->router->class!='home' && $this->router->method!='index') { ?>
            <script src="/../js/front/bootstrap-datetimepicker.js" type="text/javascript"></script>
            <script src="/..js/front/bootstrap-datetimepicker.fr.js" type="text/javascript"></script>
            <script src="/../assets/frontend/js/bootstrap-toggle.min.js" type="text/javascript" ></script>
            <script type="text/javascript" src="/../js/front/typeahead.js"></script>
            <link href="/../css/front/validationEngine.jquery.css" rel="stylesheet" type="text/css" />
            <script src="/../js/front/jquery.validationEngine.js" type="text/javascript"></script>
            <script src="/../js/front/jquery.validationEngine-en.js" type="text/javascript"></script>
        <?php } ?>

        <script>
            //PrettyPhoto
            jQuery(document).ready(function() {
                $("a[rel^='prettyPhoto']").prettyPhoto();
            });

            //BlackAndWhite
            $(window).load(function() {
                $('.client_img').BlackAndWhite({
                    hoverEffect: true, // default true
                    // set the path to BnWWorker.js for a superfast implementation
                    webworkerPath: false,
                    // for the images with a fluid width and height 
                    responsive: true,
                    // to invert the hover effect
                    invertHoverEffect: false,
                    // this option works only on the modern browsers ( on IE lower than 9 it remains always 1)
                    intensity: 1,
                    speed: { //this property could also be just speed: value for both fadeIn and fadeOut
                        fadeIn: 300, // 200ms for fadeIn animations
                        fadeOut: 300 // 800ms for fadeOut animations
                    },
                    onImageReady: function(img) {
                        // this callback gets executed anytime an image is converted
                    }
                });
            });
        </script>
    </head>

    <body>
        <!-- PRELOADER -->
        <img id="preloader" src="<?php echo "/../img/front/" ?>preloader.gif" alt="" />
        <!-- //PRELOADER -->
        <div class="preloader_hide">
            <!-- PAGE -->
            <div id="page">
                <?php $this->load->view('element/header'); ?>
                <?php echo $contents; ?>
                <?php $this->load->view('element/footer'); ?>
            </div>
            <!-- //PAGE -->
        </div>
    </body>
</html>