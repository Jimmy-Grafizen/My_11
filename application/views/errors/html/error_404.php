<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$base_url = "";
if (isset($_SERVER['HTTPS'])) {
    $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
} else {
    $protocol = 'http';
}
if ($_SERVER['HTTP_HOST'] == 'localhost') {
    $base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . '/fantasy_avishkar';
} else {
    $base_url = $protocol . "://" . $_SERVER['HTTP_HOST'];
}
?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>404 Page Not Found</title>
        <link href="<?php echo $base_url;?>/backend_asset/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <a href="<?php echo $base_url;?>"><img class="img-responsive" src="<?php echo $base_url."/backend_asset/images/4_404.jpg"; ?>" /></a>
                </div>
            </div>
        </div>
    </body>
</html>