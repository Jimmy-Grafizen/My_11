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
                        <table width="100%" style="  border-bottom: 1px solid #EEEEEE; background-color: #fff; text-align:left; padding-top: 10px;">
                            <!--#F76F24-->
                            <tr>
                                <td><a style="margin-left:0px" href="<?php echo HTTP_PATH; ?>"><img width="350" src="<?php echo HTTP_PATH; ?>img/logo-mail.png" alt="<?php echo SITE_TITLE; ?>" title="<?php echo SITE_TITLE; ?>" /></a></td>
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
                                <td valign="top" style="
                                    color: #000;
                                    font-size: 13px;
                                    padding: 10px 0;
                                    word-wrap: break-word;">

                                    <?php echo $text ?>
                                </td>
                            </tr>

                            <?php if (isset($title)) { ?>
                                <tr>
                                    <td style="font-size:12px; color:#000; line-height:18px;">
                                        <p style="margin:10px 0 0;"><strong style="width:150px;">Course title:</strong>  <?php echo $title; ?></p>
                                    </td>
                                </tr>
                            <?php }if (isset($school)) { ?>
                                <tr>
                                    <td style="font-size:12px;color:#000; line-height:18px;">
                                        <p style="margin:10px 0 0;"><strong style="width:150px;">School Name:</strong>  <?php echo $school; ?></p>
                                    </td>
                                </tr>
                            <?php } if (isset($large_text)) { ?>
                                <tr>
                                    <td style="font-size:12px;color:#000; line-height:18px;">
                                        <p style="margin:10px 0 0;"> <?php echo $large_text; ?></p>
                                    </td>
                                </tr>
                            <?php } if (isset($name)) { ?>
                                <tr>
                                    <td style="font-size:12px;color:#000; line-height:18px;">
                                        <p style="margin:10px 0 0;"><strong style="width:150px;">Name:</strong>  <?php echo $name; ?></p>
                                    </td>
                                </tr>
                            <?php } if (isset($username)) { ?>
                                <tr>
                                    <td style="font-size:12px;color:#000; line-height:18px;">
                                        <p style="margin:10px 0 0;"><strong style="width:150px;">Username:</strong>  <?php echo $username; ?></p>
                                    </td>
                                </tr>
                            <?php } if (isset($email)) {
                                ?>
                                <tr>
                                    <td style="font-size:12px;color:#000; line-height:18px;">
                                        <p style="margin:10px 0 0;"><strong style="width:150px;">Email Address:</strong>  <?php echo $email; ?></p>
                                    </td>
                                </tr>
                            <?php }if (isset($password) and $password) { ?>
                                <tr>
                                    <td style="font-size:12px;color:#000; line-height:18px;">
                                        <p style="margin:10px 0 0;"><strong style="width:150px;">Password:</strong>  <?php echo $password; ?></p>
                                    </td>
                                </tr>
                            <?php } if (isset($link) && $link != '') { ?>
                                <tr>
                                    <td style="font-size:12px;color:#000; line-height:18px;">
                                        <p style="margin:10px 0 0;"><?php echo $link; ?></p>
                                    </td>
                                </tr>
                            <?php } if (isset($booked_by) && $booked_by != '') { ?>
                                <tr>
                                    <td style="font-size:12px;color:#000; line-height:18px;">
                                        <p style="margin:10px 0 0;"><strong style="width:150px;">Course Booked By:</strong>  <?php echo $booked_by; ?></p>
                                    </td>
                                </tr>
                            <?php } if (isset($no_of_seat) && $no_of_seat != '') { ?>
                                <tr>
                                    <td style="font-size:12px;color:#000; line-height:18px;">
                                        <p style="margin:10px 0 0;"><strong style="width:150px;">No of Seat:</strong>  <?php echo $no_of_seat; ?></p>
                                    </td>
                                </tr>
                            <?php } if (isset($payment_mode) && $payment_mode != '') { ?>
                                <tr>
                                    <td style="font-size:12px;color:#000; line-height:18px;">
                                        <p style="margin:10px 0 0;"><strong style="width:150px;">Payment Mode:</strong>  <?php echo $payment_mode; ?></p>
                                    </td>
                                </tr>
                            <?php }if (isset($transaction_id)) { ?>
                                <tr>
                                    <td style="font-size:12px;color:#000; line-height:18px;">
                                        <p style="margin:10px 0 0;"><strong style="width:150px;">Payment Transaction ID:</strong>  <?php echo $transaction_id ? $transaction_id : "Not Available"; ?></p>
                                    </td>
                                </tr>
                            <?php }if (isset($amount)) { ?>
                                <tr>
                                    <td style="font-size:12px; line-height:18px;">
                                        <p style="margin:10px 0 0;"><strong style="width:150px;">Amount:</strong>  AED <?php echo number_format($amount, 2); ?></p>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <td style="font-size:12px;color:#000; line-height:18px;">
                                    <p style="margin:10px 0 0;">If you need any assistance, please submit an enquiry on the website or email us at <a href="mailto:esales@shjewellery.com.au" style="color:#000; text-decoration: underline;">esales@shjewellery.com.au</a>.</p>
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
                                        <img width="170" src="<?php echo HTTP_PATH; ?>img/logo-mail.png" alt="<?php echo SITE_TITLE; ?>" title="<?php echo SITE_TITLE; ?>" />
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
                                Copyright &copy; <?php echo date('Y'); ?>  <a style="color:#000;" href="<?php echo HTTP_PATH ?>"> www.shjewellery.com.au</a> All Rights Reserved.</p>
                        </td>
                    </tr>
                </tbody></table>
        </div>
    </body>
</html>

<?php
// exit; ?>