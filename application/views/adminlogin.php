<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

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
        <title><?php echo SITE_TITLE . " - " . TAG_LINE . " ::  "; ?>Administration - <?php echo $title; ?></title>

        <!-- Bootstrap core CSS -->
        <link href="<?php echo HTTP_PATH; ?>css/bootstrap.min.css" rel="stylesheet">
        <link href="<?php echo HTTP_PATH; ?>css/bootstrap-reset.css" rel="stylesheet">
        <!--external css-->
        <link href="<?php echo HTTP_PATH; ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
        <!-- Custom styles for this template -->
        <link href="<?php echo HTTP_PATH; ?>css/style1.css" rel="stylesheet">
        <link href="<?php echo HTTP_PATH; ?>css/style-responsive.css" rel="stylesheet" />

        <!-- HTML5 shim and Respond.js IE8 support of HTML5 tooltipss and media queries -->
        <!--[if lt IE 9]>
        <script src="<?php echo HTTP_PATH; ?>js/html5shiv.js"></script>
        <script src="<?php echo HTTP_PATH; ?>js/respond.min.js"></script>
        <![endif]-->
       
    </head>

    <body class="login-body">

        <div class="container">
            <div class="website-logo">
                <?php echo SITE_TITLE . " - " . TAG_LINE . " ::  "; ?>
            </div>
            <?php echo form_open('admin/home/login', array('method' => 'post', 'id' => 'login-form', 'name' => 'login-form', 'class' => 'form form-signin')) ?>
            <h2 class="form-signin-heading">Administrator login</h2>
            <div class="login-wrap">
                <div id="login-block"></div>
                <?php
                $data = array(
                    'class' => 'form-control',
                    'name' => 'login',
                    'id' => 'login',
                    'autofocus' => 'autofocus',
                    'placeholder' => 'Login Username'
                );
//                echo $this->session->userdata('username');
                if ($this->session->userdata('username') <> NULL) {
                    $data['value'] = $this->session->userdata('username');
                } else {
                    $data['value'] = set_value('username');
                }
                echo form_input($data);

                // form password field
                $data = array(
                    'class' => 'form-control',
                    'name' => 'pass',
                    'id' => 'pass',
                    'placeholder' => 'Password',
                );
                if ($this->session->userdata('password') <> NULL) {
                    $data['value'] = $this->session->userdata('password');
                } else {
                    $data['value'] = set_value('password');
                }
                echo form_password($data);

                // check captcha code here
                if ($check_login) {
                    $class = "";
                } else {
                    $class = "captcha_show";
                }
                ?>
                <div class="<?php echo $class; ?> captcha-section">
                    <label for="pass"><span class="big">Security code</span></label>
                    <img src="<?php echo HTTP_PATH; ?>img/captcha_code_file.php?rand=<?php echo rand(); ?>" id='captchaimg' ><a href='javascript: refreshCaptcha();'><img src="<?php echo HTTP_PATH; ?>img/captcha_refresh.gif" width="35" height="35" alt=""></a>
                    <?php
                    $data = array(
                        'name' => 'captcha',
                        'class' => 'form-control',
                        'id' => 'captcha',
                        'placeholder' => 'Type security code shown above'
                    );
                    echo form_input($data);
                    ?>
                </div>
                <label class="checkbox">
                    <p><input type="checkbox" name="keep-logged" id="keep-logged" value="1" class="mini-switch"> Remember me</p> 
                    <span class="pull-right">
                        <a data-toggle="modal" href="#myModal"> Forgot Password?</a>
                    </span>
                </label>
                <button class="btn btn-lg btn-login btn-block" type="submit">Login</button>
            </div>
            <?php echo form_close(); ?>
            <!-- Modal -->
            <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade">
                <?php echo form_open('admin/home/forgotPassword', array('method' => 'post', 'id' => 'password-recovery', 'name' => 'password-recovery', 'class' => 'form')) ?>

                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title">Forgot Password ?</h4>
                        </div>
                        <div class="modal-body">
                            <p>Enter your e-mail address below to reset your password.</p>
                            <div id="forgotpass-block"></div>
                            <input type="text" name="recovery-mail" id="recovery-mail"  placeholder="Email" autocomplete="off" class="form-control placeholder-no-fix">
                        </div>
                        <div class="modal-footer">
                            <button data-dismiss="modal" class="btn btn-default" type="button">Cancel</button>
                            <button class="btn btn-success" type="submit">Submit</button>
                        </div>
                    </div>
                </div>

                <?php echo form_close(); ?>
            </div>
            <!-- modal -->

        </div>



        <!-- js placed at the end of the document so the pages load faster -->
        <script src="<?php echo HTTP_PATH; ?>js/jquery.js"></script>
        <script src="<?php echo HTTP_PATH; ?>js/bootstrap.min.js"></script>
        <script type="text/javascript" src="<?php echo HTTP_PATH; ?>js/jquery.validate.min.js"></script>
        <!-- example login script -->
        <script>
	
            $(document).ready(function()
            {
                function error(message) {
                    return '<div class="alert alert-block alert-danger fade in"><button data-dismiss="alert" class="close close-sm" type="button"><i class="fa fa-times"></i></button>'+message+'</div>'
                }
                function success(message) {
                    return '<div class="alert alert-success alert-block fade in"><button data-dismiss="alert" class="close close-sm" type="button"><i class="fa fa-times"></i></button><p>'+message+'</p></div>'
                }
                
                function loading(message) {
                    return '<div class="alert alert-info fade in"><button data-dismiss="alert" class="close close-sm" type="button"><i class="fa fa-times"></i></button> <img src="<?php echo HTTP_PATH . "img/input-spinner.gif" ?>"/> '+message+' </div>'
                }
                
                // We'll catch form submission to do it in AJAX, but this works also with JS disabled
                $('#login-form').submit(function(event)
                {
                    // Stop full page load
                    event.preventDefault();
				
                    // Check fields
                    var login = $('#login').val();
                    var pass = $('#pass').val();
<?php // if ($check_login) {                                                          ?>
                                                                                                                                                                    
            var captcha = $('#captcha').val();
<?php // }                                                          ?>
				
            if (!login || login.length == 0)
            {
                $('#login-block').html(error('Please enter your user name'));
            }
            else if (!pass || pass.length == 0)
            {
                $('#login-block').html(error('Please enter your password'));
            }
<?php if ($check_login) { ?>
                                                                                                                                                                                                                                                                                                                                                                                
                else if (!captcha || captcha.length == 0)
                {
                    $('#login-block').html(error('Please enter your captcha code'));
                } 
                                                                                                                                                                                                                                                                                                                                                                                
<?php } ?>
            else
            {					
                // Target url
                var target = $(this).attr('action');
                if (!target || target == '')
                {
                    // Page url without hash
                    target = document.location.href.match(/^([^#]+)/)[1];
                }		
                // Request
                var data = {
                    a: $('#a').val(),
                    username: login,
<?php // if ($check_login) {                                                         ?>
                    captcha: captcha,
<?php // }                                                         ?>
                    password: pass,
                    'keep-logged': $('#keep-logged').is(':checked') ? 1 : 0
                },
                redirect = $('#redirect'),
                sendTimer = new Date().getTime();
					
                if (redirect.length > 0)
                {
                    data.redirect = redirect.val();
                }
					
                // Send
                $.ajax({
                    url: target,
                    dataType: 'json',
                    type: 'POST',
                    data: data,
                    success: function(data, textStatus, XMLHttpRequest)
                    {
                        if (data.valid)
                        {
                            // Small timer to allow the 'checking login' message to show when server is too fast
                            var receiveTimer = new Date().getTime();
                            if (receiveTimer-sendTimer < 500)
                            {
                                setTimeout(function()
                                {
                                    document.location.href = data.redirect;
										
                                }, 500-(receiveTimer-sendTimer));
                            }
                            else
                            {
                                document.location.href = data.redirect;
                            }
                            
                        }
                        else
                        {
                            if (data.captcha){
                                refreshCaptcha();
                                $(".captcha_show").show();
                                
                                
                            }
                            // Message
                            $('#login-block').html(error(data.error) || error('An unexpected error occured, please try again'));
						
                        }
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown)
                    {
                        // Message
                        $('#login-block').html(error('Error while contacting server, please try again'));
						
                    }
                });
					
                // Message
                $('#login-block').html(loading('Please wait, checking login...'));
            }
        });
        $('#password-recovery').submit(function(event)
        {
            // Stop full page load
            event.preventDefault();
				
            // Check fields
            var login = $('#recovery-mail').val();
            var emailRegex = new RegExp(/^([\w\.\-]+)@([\w\-]+)((\.(\w){2,3})+)$/i);
            var valid = emailRegex.test(login);
            if (!login || login.length == 0)
            {
                $('#forgotpass-block').html(error('Please enter your email address'));
            }
            else if (!valid) {
                $('#forgotpass-block').html(error('Please enter correct email address'));
            } 
            else
            {
					
                // Target url
                var target = $(this).attr('action');
                if (!target || target == '')
                {
                    // Page url without hash
                    target = document.location.href.match(/^([^#]+)/)[1];
                }
					
                // Request
                var data = {
                    a: $('#a').val(),
                    email: login
                },
                redirect = $('#redirect'),
                sendTimer = new Date().getTime();
					
                if (redirect.length > 0)
                {
                    data.redirect = redirect.val();
                }
					
                // Send
                $.ajax({
                    url: target,
                    dataType: 'json',
                    type: 'POST',
                    data: data,
                    success: function(data, textStatus, XMLHttpRequest)
                    {
                        if (data.valid)
                        { $('#myModal').modal('hide');
                            $('#login-block').html(success(data.message) || success('Please check your email account'));
                            $('#forgotpass-block').html("");
                            // Small timer to allow the 'checking login' message to show when server is too fast
                            var receiveTimer = new Date().getTime();
                            if (receiveTimer-sendTimer < 500)
                            {
                                $('#myModal').modal('hide')
                                setTimeout(function()
                                {
                                    
										
                                }, 500-(receiveTimer-sendTimer));
                            }
                            else
                            {
                                $('#forgotpass-block').html(success(data.message) || success('Please check your email account'));
                            }
                        }
                        else
                        {
                            // Message
                            $('#forgotpass-block').html(error(data.message) || success('An unexpected error occured, please try again'));
						
                        }
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown)
                    {
                        // Message
                        $('#forgotpass-block').html(error('Error while contacting server, please try again'));
						
                    }
                });
					
                // Message
                $('#forgotpass-block').html(loading('Please wait, checking email...'));
            }
        });
    });

	    
    function refreshCaptcha()
    {
	
        var img = document.images['captchaimg'];
        img.src = img.src.substring(0,img.src.lastIndexOf("?"))+"?rand="+Math.random()*1000;
        
    }
        </script> 

    </body>
</html>

<!-- Localized -->