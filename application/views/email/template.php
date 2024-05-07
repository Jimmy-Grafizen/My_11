<!DOCTYPE html>
<html dir="ltr" lang="en-US">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Email Template</title>
    </head>
    <body>
        <div style="background-color: #E9EDF1; text-align:center;
             width: 100%; padding:50px 0;">
            <table width="710" align="center" style=" padding: 0 50px 10px; text-align:left;  table-layout:fixed; font-family:Arial, Helvetica, sans-serif;background-color: #FFFFFF;
                   border: 1px solid #DDDDDD;  ">
                <tr>
                    <td valign="top">
                        <!-- Begin Header -->
                        <table width="100%" style="  border-bottom: 1px solid #EEEEEE; background-color: #fff; text-align: left; padding-top: 10px; ">
                            <!--#F76F24-->
                            <tr>
                                <td><a style="margin-left:0px" href="<?php echo HTTP_PATH; ?>"><img  src="<?php echo HTTP_PATH; ?>img/logo-mail.png" alt="<?php echo SITE_TITLE; ?>" title="<?php echo SITE_TITLE; ?>" /></a></td>
                            </tr>
                        </table>
                        <!-- End Header -->
                    </td>
                </tr>

                <tr>
                    <td valign="top">
                        <!-- Begin Middle Content -->
                        <table width="100%">
                            <tr>
                                <td valign="top" style="color: #000;font-size: 13px;padding: 10px 0 0;word-wrap: break-word;">
                                    <?php
                                    if (isset($text) && !empty($text)) {
                                        echo $text;
                                    }
                                    ?>
                                </td>
                            </tr>

                            <?php if (isset($title)) { ?>
                                <tr>
                                    <td style="font-size:12px; color:#000; line-height:18px;">
                                        <p style="margin:10px 0 0;"><strong style="width:150px;">Course title:</strong>  <?php echo $title; ?></p>
                                    </td>
                                </tr>
                            <?php }if (isset($message_invite)) { ?>
                                <tr>
                                    <td style="font-size:12px; color:#000; line-height:18px;">
                                        <p style="margin:10px 0 0;"><?php echo $message_invite; ?></p>
                                    </td>
                                </tr>
                                <?php
                            }
                            if (isset($invite_link)) {
                                ?>
                                <tr>
                                    <td style="font-size:12px; line-height:18px;">
                                        <p style="margin:10px 0 0;">
                                <center>
                                    <div>                    
                                        <?php
                                        if ($image and file_exists('img/uploads/images/' . $image)) {
                                            echo img(array('src' => 'img/uploads/images/' . $image, 'width' => '153', 'style' => 'border: 2px solid #CCCCCC;'));
                                        } else {
                                            echo img(array('src' => 'img/front/imgd.png', 'style' => 'border: 2px solid #CCCCCC;'));
                                        }
                                        ?>    
                                    </div>
                                    <a target="_blank" href="<?php echo $invite_link ?>" style="border-radius: 3px; color: white; text-align: center; text-decoration: none; margin-top: 6px; margin-bottom: 6px; float: none; display: inline-block; background-color: rgb(18, 178, 18); border: 1px solid rgb(18, 178, 18); max-width: 280px; font-size: 16px; width: 125px; padding: 12px 7px; margin-left: 3px;">Accept Invitation</a>
                                </center>
                                </p>
                        </td>
                    </tr>
                    <?php
                }


                if (isset($large_text)) { //send description for post announcement & waning message through admin (mashuta)      
                    ?>
                    <tr>
                        <td style="font-size:12px;color:#000; line-height:15px;">
                            <p style="margin:10px 0 0;"> <?php echo $large_text; ?></p>
                        </td>
                    </tr>
                <?php } if (isset($large_text_message)) { //send description for post announcement & waning message through admin (mashuta)       ?>
                    <tr>
                        <td style="font-size:12px;color:#000; line-height:15px;">
                            <p style="margin:0px;"> <strong style="width:150px;">Message:</strong> <?php echo $large_text_message; ?></p>
                        </td>
                    </tr>
                <?php } if (isset($name)) { ?>
                    <tr>
                        <td style="font-size:12px;color:#000; line-height:15px;">
                            <p style="margin:0px;"><strong style="width:150px;">Name:</strong>  <?php echo $name; ?></p>
                        </td>
                    </tr>
                    <?php
                }
                if (isset($phone)) {
                    ?>   
                    <tr>
                        <td style="font-size:12px; color:#000; line-height:18px;">
                            <p style="margin:0px;"><strong style="width:150px;">Phone:</strong>  <?php echo $phone; ?></p>
                        </td>
                    </tr>
                    <?php
                }
                if (isset($report_email)) {
                    ?>   
                    <tr>
                        <td style="font-size:12px; color:#000; line-height:18px;">
                            <p style="margin:0px;"><strong style="width:150px;">Email:</strong>  <?php echo $report_email; ?></p>
                        </td>
                    </tr>
                    <?php
                }
                if (isset($report_message)) {
                    ?>   
                    <tr>
                        <td style="font-size:12px; color:#000; line-height:18px;">
                            <p style="margin:0px;"><strong style="width:150px;">Message:</strong>  <?php echo $report_message; ?></p>
                        </td>
                    </tr>
                    <?php
                }


                if (isset($bid_name)) {
                    ?>
                    <tr>
                        <td style="font-size:12px;color:#000; line-height:15px;">
                            <p style="margin:0px;"><strong style="width:150px;">Bid Poster Name:</strong>  <?php echo $bid_name; ?></p>
                        </td>
                    </tr>
                    <?php
                }

                if (isset($bid_email)) {
                    ?>
                    <tr>
                        <td style="font-size:12px;color:#000; line-height:15px;">
                            <p style="margin:0px;"><strong style="width:150px;">Bid Poster Email:</strong>  <?php echo $bid_email; ?></p>
                        </td>
                    </tr>
                    <?php
                }

                if (isset($bid_phone)) {
                    ?>
                    <tr>
                        <td style="font-size:12px;color:#000; line-height:15px;">
                            <p style="margin:0px;"><strong style="width:150px;">Bid Poster Phone:</strong>  <?php echo $bid_phone; ?></p>
                        </td>
                    </tr>
                    <?php
                }

                if (isset($bid_amount)) {
                    ?>
                    <tr>
                        <td style="font-size:12px;color:#000; line-height:15px;">
                            <p style="margin:0px;"><strong style="width:150px;">Bid Price:</strong>  <?php echo $bid_amount; ?></p>
                        </td>
                    </tr>
                    <?php
                }



                if (isset($price)) {
                    ?>   
                    <tr>
                        <td style="font-size:12px; color:#000; line-height:18px;">
                            <p style="margin:0px;"><strong style="width:150px;">Price you paid: </strong> <?php echo $price; ?></p>
                        </td>
                    </tr>
                    <?php
                }
                if (isset($pesapal_transaction_tracking_id)) {
                    ?>   
                    <tr>
                        <td style="font-size:12px; color:#000; line-height:18px;">
                            <p style="margin:0px;"><strong style="width:150px;">Pesapal Transaction Tracking Id: </strong>  <?php echo $pesapal_transaction_tracking_id; ?></p>
                        </td>
                    </tr>
                    <?php
                }
                if (isset($pesapal_merchant_reference)) {
                    ?>   
                    <tr>
                        <td style="font-size:12px; color:#000; line-height:18px;">
                            <p style="margin:0px;"><strong style="width:150px;">Pesapal Merchant Reference: </strong>  <?php echo $pesapal_merchant_reference; ?></p>
                        </td>
                    </tr>
                    <?php
                }
                if (isset($username) and $username) {
                    ?>
                    <tr>
                        <td style="font-size:12px;color:#000; line-height:15px;">
                            <p style="margin:0px;"><strong style="width:150px;">Username:</strong>  <?php echo $username; ?></p>
                        </td>
                    </tr>
                <?php } if (isset($email)) {
                    ?>
                    <tr>
                        <td style="font-size:12px;color:#000; line-height:15px;">
                            <p style="margin:0px;"><strong style="width:150px;">Email Address:</strong>  <?php echo $email; ?></p>
                        </td>
                    </tr>
                <?php }if (isset($post_code)) {
                    ?>
                    <tr>
                        <td style="font-size:12px;color:#000; line-height:15px;">
                            <p style="margin:0px;"><strong style="width:150px;">Postcode:</strong>  <?php echo $post_code; ?></p>
                        </td>
                    </tr>
                <?php }if (isset($from_name)) {
                    ?>
                    <tr>
                        <td style="font-size:12px;color:#000; line-height:15px;">
                            <p style="margin:0;"><strong style="width:150px;">Your name:</strong>  <?php echo $from_name; ?></p>
                        </td>
                    </tr>
                    <?php
                } if (isset($to_name)) {
                    ?>
                    <tr>
                        <td style="font-size:12px;color:#000; line-height:15px;">
                            <p style="margin:0;"><strong style="width:150px;">Friend’s name:</strong>  <?php echo $to_name; ?></p>
                        </td>
                    </tr>
                    <?php
                } if (isset($to_email)) {
                    ?>
                    <tr>
                        <td style="font-size:12px;color:#000; line-height:15px;">
                            <p style="margin:0;"><strong style="width:150px;">Friend’s e-mail:</strong>  <?php echo $to_email; ?></p>
                        </td>
                    </tr>
                <?php } if (isset($query)) {
                    ?>
                    <tr>
                        <td style="font-size:12px;color:#000; line-height:15px;">
                            <p style="margin:0px;"><strong style="width:150px;">Enquiry:</strong>  <?php echo $query; ?></p>
                        </td>
                    </tr>
                    <?php
                } if (isset($message)) {
                    ?>
                    <tr>
                        <td style="font-size:12px;color:#000; line-height:15px;">
                            <p style="margin:0;"><strong style="width:150px;">Additional Message:</strong>  <?php echo nl2br($message); ?></p>
                        </td>
                    </tr>
                    <?php
                } if (isset($my_message)) {
                    ?>
                    <tr>
                        <td style="font-size:12px;color:#000; line-height:15px;">
                            <p style="margin:0;"><strong style="width:150px;">Message:</strong>  <?php echo nl2br($my_message); ?></p>
                        </td>
                    </tr>
                    <?php
                }




                if (isset($password) and $password) {
                    ?>
                    <tr>
                        <td style="font-size:12px;color:#000; line-height:15px;">
                            <p style="margin:0px;"><strong style="width:150px;">Password:</strong>  <?php echo $password; ?></p>
                        </td>
                    </tr>
                <?php }if (isset($transaction_id)) { ?>
                    <tr>
                        <td style="font-size:12px;color:#000; line-height:15px;">
                            <p style="margin:0px;"><strong style="width:150px;"> Payment Transaction ID:</strong>  <?php echo $transaction_id ? $transaction_id : "Not Available"; ?></p>
                        </td>
                    </tr>
                <?php }if (isset($amount)) { ?>
                    <tr>
                        <td style="font-size:12px; line-height:18px;">
                            <p style="margin:0px;"><strong style="width:150px;">Payment Amount:</strong>  <?php echo $amount; ?></p>
                        </td>
                    </tr>
                <?php }if (isset($new_password) and $new_password) { ?>
                    <tr>
                        <td style="font-size:12px;color:#000; line-height:15px;">
                            <p style="margin:0px;"><strong style="width:150px;">New Password:</strong>  <?php echo $new_password; ?></p>
                        </td>
                    </tr>
                <?php } if (isset($link) && $link != '') { ?>
                    <tr>
                        <td style="font-size:12px;color:#000; line-height:15px;">
                            <p style="margin:10px 0 0;"><?php echo $link; ?></p>
                        </td>
                    </tr>
                <?php } if (isset($booked_by) && $booked_by != '') { ?>
                    <tr>
                        <td style="font-size:12px;color:#000; line-height:15px;">
                            <p style="margin:10px 0 0;"><strong style="width:150px;">Course Booked By:</strong>  <?php echo $booked_by; ?></p>
                        </td>
                    </tr>
                <?php } if (isset($no_of_seat) && $no_of_seat != '') { ?>
                    <tr>
                        <td style="font-size:12px;color:#000; line-height:15px;">
                            <p style="margin:10px 0 0;"><strong style="width:150px;">No of Seat:</strong>  <?php echo $no_of_seat; ?></p>
                        </td>
                    </tr>
                <?php } if (isset($payment_mode) && $payment_mode != '') { ?>
                    <tr>
                        <td style="font-size:12px;color:#000; line-height:15px;">
                            <p style="margin:10px 0 0;"><strong style="width:150px;">Payment Mode:</strong>  <?php echo $payment_mode; ?></p>
                        </td>
                    </tr>
                    <?php
                }
                if (isset($text_footer) and $text_footer) {
                    ?>
                    <tr>
                        <td valign="top" style="color: #000;font-size: 13px;padding:0;word-wrap: break-word;">
                            <?php
                            if (isset($text_footer) && !empty($text_footer)) {
                                echo $text_footer;
                            }
                            ?>
                        </td>
                    </tr>
                <?php } if (isset($stock) && $stock != '') { ?>
                    <tr>
                        <td style="font-size:12px;color:#000; line-height:15px;">
                            <p style="margin:0px;"><strong style="width:150px;">Diamond Stock Number:</strong>  <?php echo $stock; ?></p>
                        </td>
                    </tr>
                <?php }if (isset($cert) && $cert != '') { ?>
                    <tr>
                        <td style="font-size:12px;color:#000; line-height:15px;">
                            <p style="margin:0px;"><strong style="width:150px;">Diamond Cert No:</strong>  <?php echo $cert; ?></p>
                        </td>
                    </tr>
                <?php } ?>
                <tr>
                    <td style="font-size:12px;color:#000; line-height:15px;">
                        <p style="margin:10px 0 0;">If you need any assistance, please submit an enquiry on the website or email us at <a href="mailto:sales@elguero2.com" style="color:#000; text-decoration: underline;">sales@elguero2.com</a>.</p>
                    </td>
                </tr>
            </table>
            <!-- End Middle Content --> 
        </td>
    </tr>
    <tr>
        <td>
            <!-- Begin Footer Notifications -->
            <table width="100%" style="border-top:1px solid #ddd;">
                <tr>
                    <td style="font-size:11px; line-height:18px;">
                        <p style="margin:10px 0 0;"> From <?php echo SITE_TITLE; ?> Team</p>
                    </td>
                    <td style="text-align: right">
                        <a style="margin-left: 10px; float: right; margin-top: 21px;" href="<?php echo HTTP_PATH; ?>">
                            <img width="50" src="<?php echo HTTP_PATH; ?>img/logo-mail.png" alt="<?php echo SITE_TITLE; ?>" title="<?php echo SITE_TITLE; ?>" />
                        </a>
                    </td>

                </tr>
            </table>
            <!-- End Footer Notifications -->
        </td>
    </tr>
    <tr>
        <td valign="top">
            <!-- Begin Footer -->
            <table width="100%" style="border-top:1px solid #ddd; background-color:#7CC6B7;">
                <!--#F76F24-->

            </table>
            <!-- End Footer -->
        </td>
    </tr>
</table>

<table style="display: inline-block;
       text-align: center;
       width: 710px;">
    <tbody style="color: #929292;
           display: inline-block;
           font-family: arial;
           font-size: 14px;
           margin-top: 5px;">
        <tr>
            <td style="font-size:12px;">
                <p style="color:#000;">
                    <!--<a href="#">Terms and Conditions</a> | <a href="#">Privacy Policy</a> | <a href="#">About Us </a> <br/>-->
                    Copyright &copy; <?php echo date('Y'); ?>  <a style="color:#000;" href="<?php echo HTTP_PATH ?>"> www.elguero2.com</a> All Rights Reserved.</p>
            </td>
        </tr>
    </tbody>
</table>
</div>
</body>
</html>